<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\DependencyResolver;

use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\BasePackage;
use Composer\Package\PackageInterface;
use Composer\Package\Version\StabilityFilter;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PrePoolCreateEvent;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RootPackageRepository;
use Composer\Semver\CompilingMatcher;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\Constraint\MatchAllConstraint;
use Composer\Semver\Constraint\MultiConstraint;

/**
 * @author Nils Adermann <naderman@naderman.de>
 */
class PoolBuilder
{
    /**
     * @var int[]
     */
    private $acceptableStabilities;
    /**
     * @var int[]
     */
    private $stabilityFlags;
    /**
     * @psalm-var array<string, array<string, array{alias: string, alias_normalized: string}>>
     */
    private $rootAliases;
    /**
     * @psalm-var array<string, string>
     */
    private $rootReferences;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @psalm-var array<string, AliasPackage>
     */
    private $aliasMap = array();
    /**
     * @psalm-var array<string, ConstraintInterface[]|null>
     */
    private $nameConstraints = array();
    private $loadedNames = array();
    /**
     * @psalm-var Package[]
     */
    private $packages = array();
    /**
     * @psalm-var list<Package>
     */
    private $unacceptableFixedPackages = array();
    private $updateAllowList = array();
    private $skippedLoad = array();
    /**
     * @psalm-var array<string, bool>
     */
    private $updateAllowWarned = array();

    /**
     * @param int[] $acceptableStabilities array of stability => BasePackage::STABILITY_* value
     * @psalm-param array<string, BasePackage::STABILITY_*> $acceptableStabilities
     * @param int[] $stabilityFlags an array of package name => BasePackage::STABILITY_* value
     * @psalm-param array<string, BasePackage::STABILITY_*> $stabilityFlags
     * @param array[] $rootAliases
     * @psalm-param array<string, array<string, array{alias: string, alias_normalized: string}>> $rootAliases
     * @param string[] $rootReferences an array of package name => source reference
     * @psalm-param array<string, string> $rootReferences
     */
    public function __construct(array $acceptableStabilities, array $stabilityFlags, array $rootAliases, array $rootReferences, IOInterface $io, EventDispatcher $eventDispatcher = null)
    {
        $this->acceptableStabilities = $acceptableStabilities;
        $this->stabilityFlags = $stabilityFlags;
        $this->rootAliases = $rootAliases;
        $this->rootReferences = $rootReferences;
        $this->eventDispatcher = $eventDispatcher;
        $this->io = $io;
    }

    public function buildPool(array $repositories, Request $request)
    {
        if ($request->getUpdateAllowList()) {
            $this->updateAllowList = $request->getUpdateAllowList();
            $this->warnAboutNonMatchingUpdateAllowList($request);

            foreach ($request->getLockedRepository()->getPackages() as $lockedPackage) {
                if (!$this->isUpdateAllowed($lockedPackage)) {
                    $request->fixPackage($lockedPackage);
                    $lockedName = $lockedPackage->getName();
                    // remember which packages we skipped loading remote content for in this partial update
                    $this->skippedLoad[$lockedName] = $lockedName;
                    foreach ($lockedPackage->getReplaces() as $link) {
                        $this->skippedLoad[$link->getTarget()] = $lockedName;
                    }
                }
            }
        }

        $loadNames = array();
        foreach ($request->getFixedPackages() as $package) {
            $this->nameConstraints[$package->getName()] = null;
            $this->loadedNames[$package->getName()] = true;

            // replace means conflict, so if a fixed package replaces a name, no need to load that one, packages would conflict anyways
            foreach ($package->getReplaces() as $link) {
                $this->nameConstraints[$package->getName()] = null;
                $this->loadedNames[$link->getTarget()] = true;
            }

            // TODO in how far can we do the above for conflicts? It's more tricky cause conflicts can be limited to
            // specific versions while replace is a conflict with all versions of the name

            if (
                $package->getRepository() instanceof RootPackageRepository
                || $package->getRepository() instanceof PlatformRepository
                || StabilityFilter::isPackageAcceptable($this->acceptableStabilities, $this->stabilityFlags, $package->getNames(), $package->getStability())
            ) {
                $loadNames += $this->loadPackage($request, $package, false);
            } else {
                $this->unacceptableFixedPackages[] = $package;
            }
        }

        foreach ($request->getRequires() as $packageName => $constraint) {
            // fixed packages have already been added, so if a root require needs one of them, no need to do anything
            if (isset($this->loadedNames[$packageName])) {
                continue;
            }

            $loadNames[$packageName] = $constraint;
            $this->nameConstraints[$packageName] = $constraint && !($constraint instanceof MatchAllConstraint) ? array($constraint) : null;
        }

        // clean up loadNames for anything we manually marked loaded above
        foreach ($loadNames as $name => $void) {
            if (isset($this->loadedNames[$name])) {
                unset($loadNames[$name]);
            }
        }

        while (!empty($loadNames)) {
            foreach ($loadNames as $name => $void) {
                $this->loadedNames[$name] = true;
            }

            $newLoadNames = array();
            foreach ($repositories as $repository) {
                // these repos have their packages fixed if they need to be loaded so we
                // never need to load anything else from them
                if ($repository instanceof PlatformRepository || $repository === $request->getLockedRepository()) {
                    continue;
                }
                $result = $repository->loadPackages($loadNames, $this->acceptableStabilities, $this->stabilityFlags);

                foreach ($result['namesFound'] as $name) {
                    // avoid loading the same package again from other repositories once it has been found
                    unset($loadNames[$name]);
                }
                foreach ($result['packages'] as $package) {
                    $newLoadNames += $this->loadPackage($request, $package);
                }
            }

            $loadNames = $newLoadNames;
        }

        // filter packages according to all the require statements collected for each package
        $nameConstraints = array();
        foreach ($this->nameConstraints as $name => $constraints) {
            if (\is_array($constraints)) {
                $nameConstraints[$name] = MultiConstraint::create(array_values(array_unique($constraints)), false);
            }
        }
        foreach ($this->packages as $i => $package) {
            // we check all alias related packages at once, so no need to check individual aliases
            // isset also checks non-null value
            if (!$package instanceof AliasPackage && isset($nameConstraints[$package->getName()])) {
                $constraint = $nameConstraints[$package->getName()];

                $aliasedPackages = array($i => $package);
                if (isset($this->aliasMap[spl_object_hash($package)])) {
                    $aliasedPackages += $this->aliasMap[spl_object_hash($package)];
                }

                $found = false;
                foreach ($aliasedPackages as $packageOrAlias) {
                    if (CompilingMatcher::match($constraint, Constraint::OP_EQ, $packageOrAlias->getVersion())) {
                        $found = true;
                    }
                }
                if (!$found) {
                    foreach ($aliasedPackages as $index => $packageOrAlias) {
                        unset($this->packages[$index]);
                    }
                }
            }
        }

        if ($this->eventDispatcher) {
            $prePoolCreateEvent = new PrePoolCreateEvent(
                PluginEvents::PRE_POOL_CREATE,
                $repositories,
                $request,
                $this->acceptableStabilities,
                $this->stabilityFlags,
                $this->rootAliases,
                $this->rootReferences,
                $this->packages,
                $this->unacceptableFixedPackages
            );
            $this->eventDispatcher->dispatch($prePoolCreateEvent->getName(), $prePoolCreateEvent);
            $this->packages = $prePoolCreateEvent->getPackages();
            $this->unacceptableFixedPackages = $prePoolCreateEvent->getUnacceptableFixedPackages();
        }

        $pool = new Pool($this->packages, $this->unacceptableFixedPackages);

        $this->aliasMap = array();
        $this->nameConstraints = array();
        $this->loadedNames = array();
        $this->packages = array();
        $this->unacceptableFixedPackages = array();

        return $pool;
    }

    private function loadPackage(Request $request, PackageInterface $package, $propagateUpdate = true)
    {
        end($this->packages);
        $index = key($this->packages) + 1;
        $this->packages[] = $package;

        if ($package instanceof AliasPackage) {
            $this->aliasMap[spl_object_hash($package->getAliasOf())][$index] = $package;
        }

        $name = $package->getName();

        // we're simply setting the root references on all versions for a name here and rely on the solver to pick the
        // right version. It'd be more work to figure out which versions and which aliases of those versions this may
        // apply to
        if (isset($this->rootReferences[$name])) {
            // do not modify the references on already locked packages
            if (!$request->isFixedPackage($package)) {
                $package->setSourceDistReferences($this->rootReferences[$name]);
            }
        }

        // if propogateUpdate is false we are loading a fixed package, root aliases do not apply as they are manually
        // loaded as separate packages in this case
        if ($propagateUpdate && isset($this->rootAliases[$name][$package->getVersion()])) {
            $alias = $this->rootAliases[$name][$package->getVersion()];
            if ($package instanceof AliasPackage) {
                $basePackage = $package->getAliasOf();
            } else {
                $basePackage = $package;
            }
            $aliasPackage = new AliasPackage($basePackage, $alias['alias_normalized'], $alias['alias']);
            $aliasPackage->setRootPackageAlias(true);

            $this->packages[] = $aliasPackage;
            $this->aliasMap[spl_object_hash($aliasPackage->getAliasOf())][$index+1] = $aliasPackage;
        }

        $loadNames = array();
        foreach ($package->getRequires() as $link) {
            $require = $link->getTarget();
            if (!isset($this->loadedNames[$require])) {
                $loadNames[$require] = null;
            // if this is a partial update with transitive dependencies we need to unfix the package we now know is a
            // dependency of another package which we are trying to update, and then attempt to load it again
            } elseif ($propagateUpdate && $request->getUpdateAllowTransitiveDependencies() && isset($this->skippedLoad[$require])) {
                if ($request->getUpdateAllowTransitiveRootDependencies() || !$this->isRootRequire($request, $this->skippedLoad[$require])) {
                    $this->unfixPackage($request, $require);
                    $loadNames[$require] = null;
                } elseif (!$request->getUpdateAllowTransitiveRootDependencies() && $this->isRootRequire($request, $require) && !isset($this->updateAllowWarned[$require])) {
                    $this->updateAllowWarned[$require] = true;
                    $this->io->writeError('<warning>Dependency "'.$require.'" is also a root requirement. Package has not been listed as an update argument, so keeping locked at old version. Use --with-all-dependencies to include root dependencies.</warning>');
                }
            }

            $linkConstraint = $link->getConstraint();
            if ($linkConstraint && !($linkConstraint instanceof MatchAllConstraint)) {
                if (!\array_key_exists($require, $this->nameConstraints)) {
                    $this->nameConstraints[$require] = array($linkConstraint);
                } elseif (\is_array($this->nameConstraints[$require])) {
                    $this->nameConstraints[$require][] = $linkConstraint;
                }
                // else it is null and should stay null
            } else {
                $this->nameConstraints[$require] = null;
            }
        }

        // if we're doing a partial update with deps we also need to unfix packages which are being replaced in case they
        // are currently locked and thus prevent this updateable package from being installable/updateable
        if ($propagateUpdate && $request->getUpdateAllowTransitiveDependencies()) {
            foreach ($package->getReplaces() as $link) {
                $replace = $link->getTarget();
                if (isset($this->loadedNames[$replace]) && isset($this->skippedLoad[$replace])) {
                    if ($request->getUpdateAllowTransitiveRootDependencies() || !$this->isRootRequire($request, $this->skippedLoad[$replace])) {
                        $this->unfixPackage($request, $replace);
                        $loadNames[$replace] = null;
                        // TODO should we try to merge constraints here?
                        $this->nameConstraints[$replace] = null;
                    } elseif (!$request->getUpdateAllowTransitiveRootDependencies() && $this->isRootRequire($request, $replace) && !isset($this->updateAllowWarned[$replace])) {
                        $this->updateAllowWarned[$replace] = true;
                        $this->io->writeError('<warning>Dependency "'.$replace.'" is also a root requirement. Package has not been listed as an update argument, so keeping locked at old version. Use --with-all-dependencies to include root dependencies.</warning>');
                    }
                }
            }
        }

        return $loadNames;
    }

    /**
     * Checks if a particular name is required directly in the request
     *
     * @return bool
     */
    private function isRootRequire(Request $request, $name)
    {
        $rootRequires = $request->getRequires();
        return isset($rootRequires[$name]);
    }

    /**
     * Checks whether the update allow list allows this package in the lock file to be updated
     * @return bool
     */
    private function isUpdateAllowed(PackageInterface $package)
    {
        foreach ($this->updateAllowList as $pattern => $void) {
            $patternRegexp = BasePackage::packageNameToRegexp($pattern);
            if (preg_match($patternRegexp, $package->getName())) {
                return true;
            }
        }

        return false;
    }

    private function warnAboutNonMatchingUpdateAllowList(Request $request)
    {
        foreach ($this->updateAllowList as $pattern => $void) {
            $patternRegexp = BasePackage::packageNameToRegexp($pattern);
            // update pattern matches a locked package? => all good
            foreach ($request->getLockedRepository()->getPackages() as $package) {
                if (preg_match($patternRegexp, $package->getName())) {
                    continue 2;
                }
            }
            // update pattern matches a root require? => all good, probably a new package
            foreach ($request->getRequires() as $packageName => $constraint) {
                if (preg_match($patternRegexp, $packageName)) {
                    continue 2;
                }
            }
            if (strpos($pattern, '*') !== false) {
                $this->io->writeError('<warning>Pattern "' . $pattern . '" listed for update does not match any locked packages.</warning>');
            } else {
                $this->io->writeError('<warning>Package "' . $pattern . '" listed for update is not locked.</warning>');
            }
        }
    }

    /**
     * Reverts the decision to use a fixed package from lock file if a partial update with transitive dependencies
     * found that this package actually needs to be updated
     */
    private function unfixPackage(Request $request, $name)
    {
        // remove locked package by this name which was already initialized
        foreach ($request->getLockedRepository()->getPackages() as $lockedPackage) {
            if (!($lockedPackage instanceof AliasPackage) && $lockedPackage->getName() === $name) {
                if (false !== $index = array_search($lockedPackage, $this->packages, true)) {
                    $request->unfixPackage($lockedPackage);
                    unset($this->packages[$index]);
                    if (isset($this->aliasMap[spl_object_hash($lockedPackage)])) {
                        foreach ($this->aliasMap[spl_object_hash($lockedPackage)] as $aliasIndex => $aliasPackage) {
                            $request->unfixPackage($aliasPackage);
                            unset($this->packages[$aliasIndex]);
                        }
                        unset($this->aliasMap[spl_object_hash($lockedPackage)]);
                    }
                }
            }
        }

        if (
            // if we unfixed a replaced package name, we also need to unfix the replacer itself
            $this->skippedLoad[$name] !== $name
            // as long as it was not unfixed yet
            && isset($this->skippedLoad[$this->skippedLoad[$name]])
        ) {
            $this->unfixPackage($request, $this->skippedLoad[$name]);
        }

        unset($this->skippedLoad[$name]);
        unset($this->loadedNames[$name]);
    }
}


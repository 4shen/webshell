<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Testing\Application\EnabledRectorsProvider;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @var PhpRectorInterface[]
     */
    private $allPhpRectors = [];

    /**
     * @var EnabledRectorsProvider
     */
    private $enabledRectorsProvider;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @param PhpRectorInterface[] $phpRectors
     */
    public function __construct(
        EnabledRectorsProvider $enabledRectorsProvider,
        Configuration $configuration,
        array $phpRectors = []
    ) {
        $this->allPhpRectors = $phpRectors;

        foreach ($phpRectors as $phpRector) {
            if ($configuration->isCacheEnabled() && ! $configuration->shouldClearCache() && $phpRector instanceof ZeroCacheRectorInterface) {
                continue;
            }

            $this->addVisitor($phpRector);
        }

        $this->enabledRectorsProvider = $enabledRectorsProvider;
        $this->privatesAccessor = new PrivatesAccessor();
    }

    /**
     * @return PhpRectorInterface[]
     */
    public function getAllPhpRectors(): array
    {
        return $this->allPhpRectors;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        if ($this->enabledRectorsProvider->isEnabled()) {
            $this->configureEnabledRectorsOnly();
        }

        return parent::traverse($nodes);
    }

    public function getPhpRectorCount(): int
    {
        return count($this->visitors);
    }

    public function hasZeroCacheRectors(): bool
    {
        return (bool) $this->getZeroCacheRectorCount();
    }

    public function getZeroCacheRectorCount(): int
    {
        $zeroCacheRectors = array_filter($this->allPhpRectors, function (PhpRectorInterface $phpRector) {
            return $phpRector instanceof ZeroCacheRectorInterface;
        });

        return count($zeroCacheRectors);
    }

    /**
     * Mostly used for testing
     */
    private function configureEnabledRectorsOnly(): void
    {
        $this->visitors = [];
        $enabledRectors = $this->enabledRectorsProvider->getEnabledRectors();

        foreach ($enabledRectors as $enabledRector => $configuration) {
            foreach ($this->allPhpRectors as $phpRector) {
                if (! is_a($phpRector, $enabledRector, true)) {
                    continue;
                }

                $this->addRectorConfiguration($configuration, $phpRector);

                $this->addVisitor($phpRector);
                continue 2;
            }
        }
    }

    /**
     * @param mixed[] $configuration
     */
    private function addRectorConfiguration(array $configuration, PhpRectorInterface $phpRector): void
    {
        foreach ($configuration as $property => $value) {
            /** @var string $property */
            $this->privatesAccessor->setPrivateProperty($phpRector, ltrim($property, '$'), $value);
        }
    }
}

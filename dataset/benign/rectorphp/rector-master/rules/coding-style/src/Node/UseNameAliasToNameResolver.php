<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\Imports\ShortNameResolver;
use Rector\CodingStyle\Naming\ClassNaming;

final class UseNameAliasToNameResolver
{
    /**
     * @var ShortNameResolver
     */
    private $shortNameResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(ShortNameResolver $shortNameResolver, ClassNaming $classNaming)
    {
        $this->shortNameResolver = $shortNameResolver;
        $this->classNaming = $classNaming;
    }

    /**
     * @return string[][]
     */
    public function resolve(Use_ $use): array
    {
        $useNamesAliasToName = [];

        $shortNames = $this->shortNameResolver->resolveForNode($use);
        foreach ($shortNames as $alias => $useImport) {
            $shortName = $this->classNaming->getShortName($useImport);
            if ($shortName === $alias) {
                continue;
            }

            $useNamesAliasToName[$shortName][] = $alias;
        }

        return $useNamesAliasToName;
    }
}

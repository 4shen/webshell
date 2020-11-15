<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Restoration\Tests\Rector\Namespace_\CompleteImportForPartialAnnotationRector\CompleteImportForPartialAnnotationRectorTest
 */
final class CompleteImportForPartialAnnotationRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private const DEFAULT_IMPORTS_TO_RESTORE = [
        ['Doctrine\ORM\Mapping', 'ORM'],
        ['Symfony\Component\Validator\Constraints', 'Assert'],
        ['JMS\Serializer\Annotation', 'Serializer'],
    ];

    /**
     * @var mixed[]
     */
    private $useImportsToRestore = [];

    /**
     * @param mixed[] $useImportsToRestore
     */
    public function __construct(array $useImportsToRestore = [])
    {
        $this->useImportsToRestore = array_merge($useImportsToRestore, self::DEFAULT_IMPORTS_TO_RESTORE);
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('In case you have accidentally removed use imports but code still contains partial use statements, this will save you', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}
PHP
                ,
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}
PHP
                ,
                [
                    '$useImportsToRestore' => [['Doctrine\ORM\Mapping', 'ORM']],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf((array) $node->stmts, Class_::class);
        if ($class === null) {
            return null;
        }

        foreach ($this->useImportsToRestore as $useImportToRestore) {
            if (is_array($useImportToRestore)) {
                [$import, $alias] = $useImportToRestore;
                $annotationToSeek = $alias;
            } else {
                if (! is_string($useImportToRestore)) {
                    throw new ShouldNotHappenException();
                }

                $import = $useImportToRestore;
                $alias = '';
                $annotationToSeek = Strings::after($import, '\\', -1);
            }

            $annotationToSeek = '#\*\s+\@' . $annotationToSeek . '#';
            if (! Strings::match($this->print($class), $annotationToSeek)) {
                continue;
            }

            $node = $this->addImportToNamespaceIfMissing($node, $import, $alias);
        }

        return $node;
    }

    private function addImportToNamespaceIfMissing(Namespace_ $namespace, string $import, string $alias): Namespace_
    {
        foreach ($namespace->stmts as $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }

            $useUse = $stmt->uses[0];

            // already there
            if ($this->isName($useUse->name, $import) && (string) $useUse->alias === $alias) {
                return $namespace;
            }
        }

        return $this->addImportToNamespace($namespace, $import, $alias);
    }

    private function addImportToNamespace(Namespace_ $namespace, string $name, string $alias): Namespace_
    {
        $useBuilder = $this->builderFactory->use($name);
        if ($alias !== '') {
            $useBuilder->as($alias);
        }

        /** @var Stmt $use */
        $use = $useBuilder->getNode();

        $namespace->stmts = array_merge([$use], (array) $namespace->stmts);

        return $namespace;
    }
}

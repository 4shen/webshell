<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\Constant;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Renaming\Tests\Rector\Constant\RenameClassConstantRector\RenameClassConstantRectorTest
 */
final class RenameClassConstantRector extends AbstractRector
{
    /**
     * class => [
     *      OLD_CONSTANT => NEW_CONSTANT
     * ]
     *
     * @var string[][]
     */
    private $oldToNewConstantsByClass = [];

    /**
     * @param string[][] $oldToNewConstantsByClass
     */
    public function __construct(array $oldToNewConstantsByClass = [])
    {
        $this->oldToNewConstantsByClass = $oldToNewConstantsByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined class constants in their calls.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
PHP
                ,
                <<<'PHP'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
PHP
                ,
                [
                    'SomeClass' => [
                        'OLD_CONSTANT' => 'NEW_CONSTANT',
                        'OTHER_OLD_CONSTANT' => 'DifferentClass::NEW_CONSTANT',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewConstantsByClass as $type => $oldToNewConstants) {
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($oldToNewConstants as $oldConstant => $newConstant) {
                if (! $this->isName($node->name, $oldConstant)) {
                    continue;
                }

                if (Strings::contains($newConstant, '::')) {
                    return $this->createClassConstantFetchNodeFromDoubleColonFormat($newConstant);
                }

                $node->name = new Identifier($newConstant);

                return $node;
            }
        }

        return $node;
    }

    private function createClassConstantFetchNodeFromDoubleColonFormat(string $constant): ClassConstFetch
    {
        [$constantClass, $constantName] = explode('::', $constant);

        return new ClassConstFetch(new FullyQualified($constantClass), new Identifier($constantName));
    }
}

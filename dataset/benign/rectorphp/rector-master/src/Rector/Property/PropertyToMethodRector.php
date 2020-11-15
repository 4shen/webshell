<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\Property\PropertyToMethodRector\PropertyToMethodRectorTest
 */
final class PropertyToMethodRector extends AbstractRector
{
    /**
     * @var string
     */
    private const GET = 'get';

    /**
     * @var string[][][]
     */
    private $perClassPropertyToMethods = [];

    /**
     * @param string[][][] $perClassPropertyToMethods
     */
    public function __construct(array $perClassPropertyToMethods = [])
    {
        $this->perClassPropertyToMethods = $perClassPropertyToMethods;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces properties assign calls be defined methods.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$result = $object->property;
$object->property = $value;
PHP
                ,
                <<<'PHP'
$result = $object->getProperty();
$object->setProperty($value);
PHP
                ,
                [
                    '$perClassPropertyToMethods' => [
                        'SomeObject' => [
                            'property' => [
                                self::GET => 'getProperty',
                                'set' => 'setProperty',
                            ],
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'PHP'
$result = $object->property;
PHP
                ,
                <<<'PHP'
$result = $object->getProperty('someArg');
PHP
                ,
                [
                    '$perClassPropertyToMethods' => [
                        'SomeObject' => [
                            'property' => [
                                self::GET => [
                                    'method' => 'getConfig',
                                    'arguments' => ['someArg'],
                                ],
                            ],
                        ],
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->var instanceof PropertyFetch) {
            return $this->processSetter($node);
        }

        if ($node->expr instanceof PropertyFetch) {
            return $this->processGetter($node);
        }

        return null;
    }

    private function processSetter(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->var;

        $newMethodMatch = $this->matchPropertyFetchCandidate($propertyFetchNode);
        if ($newMethodMatch === null) {
            return null;
        }

        $args = $this->createArgs([$assign->expr]);

        /** @var Variable $variable */
        $variable = $propertyFetchNode->var;

        return $this->createMethodCall($variable, $newMethodMatch['set'], $args);
    }

    private function processGetter(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->expr;

        $newMethodMatch = $this->matchPropertyFetchCandidate($propertyFetchNode);
        if ($newMethodMatch === null) {
            return null;
        }

        // simple method name
        if (is_string($newMethodMatch[self::GET])) {
            $assign->expr = $this->createMethodCall($propertyFetchNode->var, $newMethodMatch[self::GET]);

            return $assign;

            // method with argument
        }

        if (is_array($newMethodMatch[self::GET])) {
            $args = $this->createArgs($newMethodMatch[self::GET]['arguments']);

            $assign->expr = $this->createMethodCall(
                $propertyFetchNode->var,
                $newMethodMatch[self::GET]['method'],
                $args
            );

            return $assign;
        }

        return $assign;
    }

    /**
     * @return mixed[]|null
     */
    private function matchPropertyFetchCandidate(PropertyFetch $propertyFetch): ?array
    {
        foreach ($this->perClassPropertyToMethods as $type => $propertyToMethods) {
            $properties = array_keys($propertyToMethods);

            if (! $this->isObjectType($propertyFetch->var, $type)) {
                continue;
            }

            if (! $this->isNames($propertyFetch, $properties)) {
                continue;
            }

            /** @var Identifier $identifierNode */
            $identifierNode = $propertyFetch->name;

            //[$type];
            return $propertyToMethods[$identifierNode->toString()];
        }

        return null;
    }
}

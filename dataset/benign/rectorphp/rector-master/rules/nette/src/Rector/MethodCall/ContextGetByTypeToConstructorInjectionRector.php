<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\Rector\FrameworkBundle\AbstractToConstructorInjectionRector;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\ContextGetByTypeToConstructorInjectionRectorTest
 */
final class ContextGetByTypeToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Move dependency get via $context->getByType() to constructor injection',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function run()
    {
        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
    }
}
PHP
,
                    <<<'PHP'
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    /**
     * @var SomeTypeToInject
     */
    private $someTypeToInject;

    public function __construct(SomeTypeToInject $someTypeToInject)
    {
        $this->someTypeToInject = $someTypeToInject;
    }

    public function run()
    {
        $someTypeToInject = $this->someTypeToInject;
    }
}
PHP

                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isInTestClass($node)) {
            return null;
        }

        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        if (! $this->isObjectType($node->var, 'Nette\DI\Container')) {
            return null;
        }

        if (! $this->isName($node->name, 'getByType')) {
            return null;
        }

        return $this->processMethodCallNode($node);
    }

    private function isInTestClass(Node $node): bool
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        return $this->isObjectTypes($classNode, ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase']);
    }
}

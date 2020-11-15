<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 * @see \Rector\Symfony\Tests\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector\ContainerGetToConstructorInjectionRectorTest
 */
final class ContainerGetToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    /**
     * @var string[]
     */
    private $containerAwareParentTypes = [];

    /**
     * @param string[] $containerAwareParentTypes
     */
    public function __construct(array $containerAwareParentTypes = [
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
    ])
    {
        $this->containerAwareParentTypes = $containerAwareParentTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony',
            [
                new CodeSample(
<<<'PHP'
final class SomeCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        // ...
        $this->getContainer()->get('some_service');
        $this->container->get('some_service');
    }
}
PHP
                    ,
<<<'PHP'
final class SomeCommand extends Command
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
        // ...
        $this->someService;
        $this->someService;
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
        if (! $this->isObjectType($node->var, ContainerInterface::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'get')) {
            return null;
        }

        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if (! in_array($parentClassName, $this->containerAwareParentTypes, true)) {
            return null;
        }

        return $this->processMethodCallNode($node);
    }
}

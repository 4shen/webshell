<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Controller;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @see \Rector\Symfony\Tests\Rector\Controller\RedirectToRouteRector\RedirectToRouteRectorTest
 */
final class RedirectToRouteRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns redirect to route to short helper method in Controller in Symfony', [
            new CodeSample(
                '$this->redirect($this->generateUrl("homepage"));',
                '$this->redirectToRoute("homepage");'
            ),
        ]);
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
        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName !== Controller::class) {
            return null;
        }
        if (! $this->isName($node->name, 'redirect')) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $argumentValue = $node->args[0]->value;
        if (! $argumentValue instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($argumentValue->name, 'generateUrl')) {
            return null;
        }

        return $this->createMethodCall('this', 'redirectToRoute', $this->resolveArguments($node));
    }

    /**
     * @return mixed[]
     */
    private function resolveArguments(MethodCall $methodCall): array
    {
        /** @var MethodCall $generateUrlNode */
        $generateUrlNode = $methodCall->args[0]->value;

        $arguments = [];
        $arguments[] = $generateUrlNode->args[0];

        if (isset($generateUrlNode->args[1])) {
            $arguments[] = $generateUrlNode->args[1];
        }

        if (! isset($generateUrlNode->args[1]) && isset($methodCall->args[1])) {
            $arguments[] = [];
        }

        if (isset($methodCall->args[1])) {
            $arguments[] = $methodCall->args[1];
        }

        return $arguments;
    }
}

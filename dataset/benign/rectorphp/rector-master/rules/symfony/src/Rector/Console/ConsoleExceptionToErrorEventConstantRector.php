<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * Covers:
 * - https://github.com/symfony/symfony/pull/22441/files
 * - https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#console
 *
 * @see \Rector\Symfony\Tests\Rector\Console\ConsoleExceptionToErrorEventConstantRector\ConsoleExceptionToErrorEventConstantRectorTest
 */
final class ConsoleExceptionToErrorEventConstantRector extends AbstractRector
{
    /**
     * @var string
     */
    private const CONSOLE_EVENTS_CLASS = 'Symfony\Component\Console\ConsoleEvents';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old event name with EXCEPTION to ERROR constant in Console in Symfony', [
            new CodeSample('"console.exception"', 'Symfony\Component\Console\ConsoleEvents::ERROR'),
            new CodeSample(
                'Symfony\Component\Console\ConsoleEvents::EXCEPTION',
                'Symfony\Component\Console\ConsoleEvents::ERROR'
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class, String_::class];
    }

    /**
     * @param ClassConstFetch|String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassConstFetch && (
            $this->isObjectType($node, self::CONSOLE_EVENTS_CLASS) &&
            $this->isName($node->name, 'EXCEPTION'))
        ) {
            return $this->createClassConstFetch(self::CONSOLE_EVENTS_CLASS, 'ERROR');
        }

        if ($node instanceof String_ && $node->value === 'console.exception') {
            return $this->createClassConstFetch(self::CONSOLE_EVENTS_CLASS, 'ERROR');
        }

        return null;
    }
}

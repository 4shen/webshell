<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\EventListener;

use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Zephir\Exception\CompilerException;
use Zephir\Exception\ExceptionInterface;

/**
 * Zephir\EventListener\ConsoleErrorListener.
 */
class ConsoleErrorListener
{
    public function onCommandError(ConsoleErrorEvent $event)
    {
        if (!filter_var(getenv('ZEPHIR_DEBUG'), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $exitCode = $event->getExitCode();
        $throwable = $event->getError();

        $event->setError(
            new CompilerException(
                $event->getError()->getMessage(),
                $throwable instanceof ExceptionInterface ? $throwable->getExtra() : null,
                $exitCode,
                $event->getError()
            )
        );
    }
}

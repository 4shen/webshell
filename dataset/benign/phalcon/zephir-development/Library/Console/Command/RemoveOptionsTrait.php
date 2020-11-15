<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Console\Command;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

trait RemoveOptionsTrait
{
    protected function removeOptions(array $names)
    {
        /** @var InputDefinition $definition */
        $definition = $this->getDefinition();

        $filtered = array_filter($definition->getOptions(), function (InputOption $option) use ($names) {
            return !\in_array($option->getName(), $names, true);
        });

        $definition->setOptions($filtered);
    }
}

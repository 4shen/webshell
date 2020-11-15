<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Strategies\Check;

/**
 * Strategy for Node projects.
 */
class NodeStrategy extends AbstractCheckStrategy implements CheckStrategyInterface
{
    /**
     * The language of the strategy.
     *
     * @var string
     */
    protected $language = 'Node';

    /**
     * @var string
     */
    protected $binary = 'node';

    /**
     * @var string
     */
    protected $manager = 'npm';

    /**
     * @var string
     */
    protected $description = 'Checks if the server is ready to receive a Node application';

    /**
     * Get the version constraint which should be checked against.
     *
     * @param string $manifest
     *
     * @return string
     */
    protected function getLanguageConstraint($manifest)
    {
        return $this->getLanguageConstraintFromJson($manifest, 'engines.node');
    }

    /**
     * Get the current version in use.
     *
     * @return string
     */
    protected function getCurrentVersion()
    {
        $version = $this->getBinary()->run('--version');
        $version = str_replace('v', null, $version);

        return $version;
    }
}

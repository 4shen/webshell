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

namespace Rocketeer\Tasks;

/**
 * Remove the remote applications and existing caches.
 */
class Teardown extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Remove the remote applications and existing caches';

    /**
     * Whether the task needs to be run on each stage or globally.
     *
     * @var bool
     */
    public $usesStages = false;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // Ask confirmation
        $confirm = $this->command->confirm(
            'This will remove all folders on the server, not just releases. Do you want to proceed ?'
        );

        if (!$confirm) {
            return $this->explainer->info('Teardown aborted');
        }

        // Remove remote folders
        $this->removeFolder($this->paths->getFolder());

        // Remove deployments file
        $this->localStorage->destroy();

        return $this->explainer->success('The application was successfully removed from the remote servers');
    }
}

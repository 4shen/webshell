<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2014, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCI\Service;

use b8\Config;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use PHPCI\BuildFactory;
use PHPCI\Helper\Lang;
use PHPCI\Model\Build;
use PHPCI\Model\Project;
use PHPCI\Store\BuildStore;

/**
 * The build service handles the creation, duplication and deletion of builds.
 * Class BuildService
 * @package PHPCI\Service
 */
class BuildService
{
    /**
     * @var \PHPCI\Store\BuildStore
     */
    protected $buildStore;

    /**
     * @var bool
     */
    public $queueError = false;

    /**
     * @param BuildStore $buildStore
     */
    public function __construct(BuildStore $buildStore)
    {
        $this->buildStore = $buildStore;
    }

    /**
     * @param Project $project
     * @param string|null $commitId
     * @param string|null $branch
     * @param string|null $committerEmail
     * @param string|null $commitMessage
     * @param string|null $extra
     * @return \PHPCI\Model\Build
     */
    public function createBuild(
        Project $project,
        $commitId = null,
        $branch = null,
        $committerEmail = null,
        $commitMessage = null,
        $extra = null
    ) {
        $build = new Build();
        $build->setCreated(new \DateTime());
        $build->setProject($project);
        $build->setStatus(0);

        if (!is_null($commitId)) {
            $build->setCommitId($commitId);
        } else {
            $build->setCommitId('Manual');
            $build->setCommitMessage(Lang::get('manual_build'));
        }

        if (!is_null($branch)) {
            $build->setBranch($branch);
        } else {
            $build->setBranch($project->getBranch());
        }

        if (!is_null($committerEmail)) {
            $build->setCommitterEmail($committerEmail);
        }

        if (!is_null($commitMessage)) {
            $build->setCommitMessage($commitMessage);
        }

        if (!is_null($extra)) {
            $build->setExtra(json_encode($extra));
        }

        $build = $this->buildStore->save($build);

        $buildId = $build->getId();

        if (!empty($buildId)) {
            $build = BuildFactory::getBuild($build);
            $build->sendStatusPostback();
            $this->addBuildToQueue($build);
        }

        return $build;
    }

    /**
     * @param Build $copyFrom
     * @return \PHPCI\Model\Build
     */
    public function createDuplicateBuild(Build $copyFrom)
    {
        $data = $copyFrom->getDataArray();

        // Clean up unwanted properties from the original build:
        unset($data['id']);
        unset($data['status']);
        unset($data['log']);
        unset($data['started']);
        unset($data['finished']);

        $build = new Build();
        $build->setValues($data);
        $build->setCreated(new \DateTime());
        $build->setStatus(0);

        $build = $this->buildStore->save($build);

        $buildId = $build->getId();

        if (!empty($buildId)) {
            $build = BuildFactory::getBuild($build);
            $build->sendStatusPostback();
            $this->addBuildToQueue($build);
        }

        return $build;
    }

    /**
     * Delete a given build.
     * @param Build $build
     * @return bool
     */
    public function deleteBuild(Build $build)
    {
        $build->removeBuildDirectory();
        return $this->buildStore->delete($build);
    }

    /**
     * Takes a build and puts it into the queue to be run (if using a queue)
     * @param Build $build
     */
    public function addBuildToQueue(Build $build)
    {
        $buildId = $build->getId();

        if (empty($buildId)) {
            return;
        }

        $config = Config::getInstance();
        $settings = $config->get('phpci.worker', []);

        if (!empty($settings['host']) && !empty($settings['queue'])) {
            try {
                $jobData = array(
                    'type' => 'phpci.build',
                    'build_id' => $build->getId(),
                );

                if ($config->get('using_custom_file')) {
                    $jobData['config'] = $config->getArray();
                }

                $pheanstalk = new Pheanstalk($settings['host']);
                $pheanstalk->useTube($settings['queue']);
                $pheanstalk->put(
                    json_encode($jobData),
                    PheanstalkInterface::DEFAULT_PRIORITY,
                    PheanstalkInterface::DEFAULT_DELAY,
                    $config->get('phpci.worker.job_timeout', 600)
                );
            } catch (\Exception $ex) {
                $this->queueError = true;
            }
        }
    }
}

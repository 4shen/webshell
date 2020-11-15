<?php

/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2014, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace Tests\PHPCI\Service;

use PHPCI\Model\Build;
use PHPCI\Model\Project;
use PHPCI\Service\BuildService;

/**
 * Unit tests for the ProjectService class.
 * @author Dan Cryer <dan@block8.co.uk>
 */
class BuildServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BuildService $testedService
     */
    protected $testedService;

    /**
     * @var \ $mockBuildStore
     */
    protected $mockBuildStore;

    public function setUp()
    {
        $this->mockBuildStore = $this->getMock('PHPCI\Store\BuildStore');
        $this->mockBuildStore->expects($this->any())
                               ->method('save')
                               ->will($this->returnArgument(0));

        $this->testedService = new BuildService($this->mockBuildStore);
    }

    public function testExecute_CreateBasicBuild()
    {
        $project = new Project();
        $project->setType('github');
        $project->setId(101);

        $returnValue = $this->testedService->createBuild($project);

        $this->assertEquals(101, $returnValue->getProjectId());
        $this->assertEquals(Build::STATUS_NEW, $returnValue->getStatus());
        $this->assertNull($returnValue->getStarted());
        $this->assertNull($returnValue->getFinished());
        $this->assertNull($returnValue->getLog());
        $this->assertEquals(\PHPCI\Helper\Lang::get('manual_build'), $returnValue->getCommitMessage());
        $this->assertNull($returnValue->getCommitterEmail());
        $this->assertNull($returnValue->getExtra());
        $this->assertEquals('master', $returnValue->getBranch());
        $this->assertInstanceOf('DateTime', $returnValue->getCreated());
        $this->assertEquals('Manual', $returnValue->getCommitId());
    }

    public function testExecute_CreateBuildWithOptions()
    {
        $project = new Project();
        $project->setType('hg');
        $project->setId(101);

        $returnValue = $this->testedService->createBuild($project, '123', 'testbranch', 'test@example.com', 'test');

        $this->assertEquals('testbranch', $returnValue->getBranch());
        $this->assertEquals('123', $returnValue->getCommitId());
        $this->assertEquals('test', $returnValue->getCommitMessage());
        $this->assertEquals('test@example.com', $returnValue->getCommitterEmail());
    }

    public function testExecute_CreateBuildWithExtra()
    {
        $project = new Project();
        $project->setType('bitbucket');
        $project->setId(101);

        $returnValue = $this->testedService->createBuild($project, null, null, null, null, array('item1' => 1001));

        $this->assertEquals(1001, $returnValue->getExtra('item1'));
    }

    public function testExecute_CreateDuplicateBuild()
    {
        $build = new Build();
        $build->setId(1);
        $build->setProject(101);
        $build->setCommitId('abcde');
        $build->setStatus(Build::STATUS_FAILED);
        $build->setLog('Test');
        $build->setBranch('example_branch');
        $build->setStarted(new \DateTime());
        $build->setFinished(new \DateTime());
        $build->setCommitMessage('test');
        $build->setCommitterEmail('test@example.com');
        $build->setExtra(json_encode(array('item1' => 1001)));

        $returnValue = $this->testedService->createDuplicateBuild($build);

        $this->assertNotEquals($build->getId(), $returnValue->getId());
        $this->assertEquals($build->getProjectId(), $returnValue->getProjectId());
        $this->assertEquals($build->getCommitId(), $returnValue->getCommitId());
        $this->assertNotEquals($build->getStatus(), $returnValue->getStatus());
        $this->assertEquals(Build::STATUS_NEW, $returnValue->getStatus());
        $this->assertNull($returnValue->getLog());
        $this->assertEquals($build->getBranch(), $returnValue->getBranch());
        $this->assertNotEquals($build->getCreated(), $returnValue->getCreated());
        $this->assertNull($returnValue->getStarted());
        $this->assertNull($returnValue->getFinished());
        $this->assertEquals('test', $returnValue->getCommitMessage());
        $this->assertEquals('test@example.com', $returnValue->getCommitterEmail());
        $this->assertEquals($build->getExtra('item1'), $returnValue->getExtra('item1'));
    }

    public function testExecute_DeleteBuild()
    {
        $store = $this->getMock('PHPCI\Store\BuildStore');
        $store->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(true));

        $service = new BuildService($store);
        $build = new Build();

        $this->assertEquals(true, $service->deleteBuild($build));
    }
}

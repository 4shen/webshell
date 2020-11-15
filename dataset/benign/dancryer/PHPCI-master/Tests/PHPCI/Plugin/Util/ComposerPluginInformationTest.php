<?php

/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2015, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace Tests\PHPCI\Plugin\Util;

use PHPCI\Plugin\Util\ComposerPluginInformation;

class ComposerPluginInformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerPluginInformation
     */
    protected $testedInformation;

    protected function setUpFromFile($file)
    {
        $this->testedInformation = ComposerPluginInformation::buildFromYaml($file);
    }

    protected function phpciSetup()
    {
        $this->setUpFromFile(
            __DIR__ . "/../../../../vendor/composer/installed.json"
        );
    }

    public function testBuildFromYaml_ReturnsInstance()
    {
        $this->phpciSetup();
        $this->assertInstanceOf(
            '\PHPCI\Plugin\Util\ComposerPluginInformation',
            $this->testedInformation
        );
    }

    public function testGetInstalledPlugins_ReturnsStdClassArray()
    {
        $this->phpciSetup();
        $plugins = $this->testedInformation->getInstalledPlugins();
        $this->assertInternalType("array", $plugins);
        $this->assertContainsOnly("stdClass", $plugins);
    }

    public function testGetPluginClasses_ReturnsStringArray()
    {
        $this->phpciSetup();
        $classes = $this->testedInformation->getPluginClasses();
        $this->assertInternalType("array", $classes);
        $this->assertContainsOnly("string", $classes);
    }
}


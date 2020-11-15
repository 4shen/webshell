<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2014, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCI\Helper;

use Exception;
use PHPCI\Logging\BuildLogger;
use Psr\Log\LogLevel;

/**
 * Handles running system commands with variables.
 * @package PHPCI\Helper
 */
abstract class BaseCommandExecutor implements CommandExecutor
{
    /**
     * @var BuildLogger
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $quiet;

    /**
     * @var bool
     */
    protected $verbose;

    protected $lastOutput;
    protected $lastError;

    public $logExecOutput = true;

    /**
     * The path which findBinary will look in.
     * @var string
     */
    protected $rootDir;

    /**
     * Current build path
     * @var string
     */
    protected $buildPath;

    /**
     * @param BuildLogger $logger
     * @param string      $rootDir
     * @param bool        $quiet
     * @param bool        $verbose
     */
    public function __construct(BuildLogger $logger, $rootDir, &$quiet = false, &$verbose = false)
    {
        $this->logger = $logger;
        $this->quiet = $quiet;
        $this->verbose = $verbose;
        $this->lastOutput = array();
        $this->rootDir = $rootDir;
    }

    /**
     * Executes shell commands.
     * @param array $args
     * @return bool Indicates success
     */
    public function executeCommand($args = array())
    {
        $this->lastOutput = array();

        $command = call_user_func_array('sprintf', $args);
        $this->logger->logDebug($command);

        if ($this->quiet) {
            $this->logger->log('Executing: ' . $command);
        }

        $status = 0;
        $descriptorSpec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w"),  // stderr
        );

        $pipes = array();
        $process = proc_open($command, $descriptorSpec, $pipes, $this->buildPath, null);

        if (is_resource($process)) {
            fclose($pipes[0]);

            stream_set_blocking($pipes[1], 0);
            stream_set_blocking($pipes[2], 0);

            $lastOutput = '';
            $lastError  = '';
            do {
                usleep(200); //Give It Time To Start

                while (($newData = fread($pipes[1], 1024)) && !feof($pipes[1])) {
                    $lastOutput .= $newData;
                }

                while (($newData = fread($pipes[2], 1024)) && !feof($pipes[2])) {
                    $lastError .= $newData;
                }

                $processStatus = proc_get_status($process);
            } while ($processStatus['running']);

            $this->lastOutput = $lastOutput;
            $this->lastError  = $lastError;

            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);
            $status = $processStatus['exitcode'];
        }

        $this->lastOutput = array_filter(explode(PHP_EOL, $this->lastOutput));

        $shouldOutput = ($this->logExecOutput && ($this->verbose || $status != 0));

        if ($shouldOutput && !empty($this->lastOutput)) {
            $this->logger->log($this->lastOutput);
        }

        if (!empty($this->lastError)) {
            $this->logger->log("\033[0;31m" . $this->lastError . "\033[0m", LogLevel::ERROR);
        }

        $rtn = false;

        if ($status == 0) {
            $rtn = true;
        }

        return $rtn;
    }

    /**
     * Returns the output from the last command run.
     */
    public function getLastOutput()
    {
        return implode(PHP_EOL, $this->lastOutput);
    }

    /**
     * Returns the stderr output from the last command run.
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Find a binary required by a plugin.
     * @param string $binary
     * @param bool $quiet
     * @return null|string
     */
    public function findBinary($binary, $quiet = false)
    {
        $composerBin = $this->getComposerBinDir(realpath($this->buildPath));

        if (is_string($binary)) {
            $binary = array($binary);
        }

        foreach ($binary as $bin) {
            $this->logger->log(Lang::get('looking_for_binary', $bin), LogLevel::DEBUG);

            if (is_dir($composerBin) && is_file($composerBin.'/'.$bin)) {
                $this->logger->log(Lang::get('found_in_path', $composerBin, $bin), LogLevel::DEBUG);
                return $composerBin . '/' . $bin;
            }

            if (is_file($this->rootDir . $bin)) {
                $this->logger->log(Lang::get('found_in_path', 'root', $bin), LogLevel::DEBUG);
                return $this->rootDir . $bin;
            }

            if (is_file($this->rootDir . 'vendor/bin/' . $bin)) {
                $this->logger->log(Lang::get('found_in_path', 'vendor/bin', $bin), LogLevel::DEBUG);
                return $this->rootDir . 'vendor/bin/' . $bin;
            }

            $findCmdResult = $this->findGlobalBinary($bin);
            if (is_file($findCmdResult)) {
                $this->logger->log(Lang::get('found_in_path', '', $bin), LogLevel::DEBUG);
                return $findCmdResult;
            }
        }

        if ($quiet) {
            return;
        }
        throw new Exception(Lang::get('could_not_find', implode('/', $binary)));
    }

    /**
     * Find a binary which is installed globally on the system
     * @param string $binary
     * @return null|string
     */
    abstract protected function findGlobalBinary($binary);

    /**
     * Try to load the composer.json file in the building project
     * If the bin-dir is configured, return the full path to it
     * @param string $path Current build path
     * @return string|null
     */
    public function getComposerBinDir($path)
    {
        if (is_dir($path)) {
            $composer = $path.'/composer.json';
            if (is_file($composer)) {
                $json = json_decode(file_get_contents($composer));

                if (isset($json->config->{"bin-dir"})) {
                    return $path.'/'.$json->config->{"bin-dir"};
                } elseif (is_dir($path . '/vendor/bin')) {
                    return $path  . '/vendor/bin';
                }
            }
        }
        return null;
    }

    /**
     * Set the buildPath property.
     * @param string $path
     */
    public function setBuildPath($path)
    {
        $this->buildPath = $path;
    }
}

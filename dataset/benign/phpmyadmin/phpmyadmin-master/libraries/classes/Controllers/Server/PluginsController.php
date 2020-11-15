<?php

/**
 * Holds the PhpMyAdmin\Controllers\Server\PluginsController
 */

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Server;

use PhpMyAdmin\Common;
use PhpMyAdmin\Controllers\AbstractController;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Response;
use PhpMyAdmin\Server\Plugins;
use PhpMyAdmin\Template;
use function array_keys;
use function ksort;
use function mb_strtolower;
use function preg_replace;

/**
 * Handles viewing server plugin details
 */
class PluginsController extends AbstractController
{
    /** @var Plugins */
    private $plugins;

    /**
     * @param Response          $response Response object
     * @param DatabaseInterface $dbi      DatabaseInterface object
     * @param Template          $template Template object
     * @param Plugins           $plugins  Plugins object
     */
    public function __construct($response, $dbi, Template $template, Plugins $plugins)
    {
        parent::__construct($response, $dbi, $template);
        $this->plugins = $plugins;
    }

    public function index(): void
    {
        Common::server();

        $header = $this->response->getHeader();
        $scripts = $header->getScripts();
        $scripts->addFile('vendor/jquery/jquery.tablesorter.js');
        $scripts->addFile('server/plugins.js');

        $plugins = [];
        $serverPlugins = $this->plugins->getAll();
        foreach ($serverPlugins as $plugin) {
            $plugins[$plugin->getType()][] = $plugin->toArray();
        }
        ksort($plugins);

        $cleanTypes = [];
        foreach (array_keys($plugins) as $type) {
            $cleanTypes[$type] = preg_replace(
                '/[^a-z]/',
                '',
                mb_strtolower($type)
            );
        }

        $this->render('server/plugins/index', [
            'plugins' => $plugins,
            'clean_types' => $cleanTypes,
        ]);
    }
}

<?php

/**
 * @package    Grav\Common\Config
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\Config;

use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Data\Data;
use Grav\Common\Utils;
use Pimple\Container;
use Psr\Http\Message\ServerRequestInterface;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Setup extends Data
{
    /**
     * @var array Environment aliases normalized to lower case.
     */
    public static $environments = [
        '' => 'unknown',
        '127.0.0.1' => 'localhost',
        '::1' => 'localhost'
    ];

    /**
     * @var string Current environment normalized to lower case.
     */
    public static $environment;

    protected $streams = [
        'system' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['system'],
            ]
        ],
        'user' => [
            'type' => 'ReadOnlyStream',
            'force' => true,
            'prefixes' => [
                '' => ['user'],
            ]
        ],
        'environment' => [
            'type' => 'ReadOnlyStream'
            // If not defined, environment will be set up in the constructor.
        ],
        'asset' => [
            'type' => 'Stream',
            'prefixes' => [
                '' => ['assets'],
            ]
        ],
        'blueprints' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['environment://blueprints', 'user://blueprints', 'system/blueprints'],
            ]
        ],
        'config' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['environment://config', 'user://config', 'system/config'],
            ]
        ],
        'plugins' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['user://plugins'],
             ]
        ],
        'plugin' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['user://plugins'],
            ]
        ],
        'themes' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['user://themes'],
            ]
        ],
        'languages' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['environment://languages', 'user://languages', 'system/languages'],
            ]
        ],
        'cache' => [
            'type' => 'Stream',
            'force' => true,
            'prefixes' => [
                '' => ['cache'],
                'images' => ['images']
            ]
        ],
        'log' => [
            'type' => 'Stream',
            'force' => true,
            'prefixes' => [
                '' => ['logs']
            ]
        ],
        'backup' => [
            'type' => 'Stream',
            'force' => true,
            'prefixes' => [
                '' => ['backup']
            ]
        ],
        'tmp' => [
            'type' => 'Stream',
            'force' => true,
            'prefixes' => [
                '' => ['tmp']
            ]
        ],
        'image' => [
            'type' => 'Stream',
            'prefixes' => [
                '' => ['user://images', 'system://images']
            ]
        ],
        'page' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['user://pages']
            ]
        ],
        'user-data' => [
            'type' => 'Stream',
            'force' => true,
            'prefixes' => [
                '' => ['user://data']
            ]
        ],
        'account' => [
            'type' => 'ReadOnlyStream',
            'prefixes' => [
                '' => ['user://accounts']
            ]
        ],
    ];

    /**
     * @param Container|array $container
     */
    public function __construct($container)
    {
        // If no environment is set, make sure we get one (CLI or hostname).
        if (!static::$environment) {
            if (\defined('GRAV_CLI')) {
                static::$environment = 'cli';
            } else {
                /** @var ServerRequestInterface $request */
                $request = $container['request'];
                $host = $request->getUri()->getHost();

                static::$environment = Utils::substrToString($host, ':');
            }
        }

        // Resolve server aliases to the proper environment.
        $environment = $this->environments[static::$environment] ?? static::$environment;

        // Pre-load setup.php which contains our initial configuration.
        // Configuration may contain dynamic parts, which is why we need to always load it.
        // If "GRAV_SETUP_PATH" has been defined, use it, otherwise use defaults.
        $file = \defined('GRAV_SETUP_PATH') ? GRAV_SETUP_PATH :  GRAV_ROOT . '/setup.php';
        $setup = is_file($file) ? (array) include $file : [];

        // Add default streams defined in beginning of the class.
        if (!isset($setup['streams']['schemes'])) {
            $setup['streams']['schemes'] = [];
        }
        $setup['streams']['schemes'] += $this->streams;

        // Initialize class.
        parent::__construct($setup);

        // Set up environment.
        $this->def('environment', $environment);
        $this->def('streams.schemes.environment.prefixes', ['' => ["user://{$this->get('environment')}"]]);
    }

    /**
     * @return $this
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function init()
    {
        $locator = new UniformResourceLocator(GRAV_ROOT);
        $files = [];

        $guard = 5;
        do {
            $check = $files;
            $this->initializeLocator($locator);
            $files = $locator->findResources('config://streams.yaml');

            if ($check === $files) {
                break;
            }

            // Update streams.
            foreach (array_reverse($files) as $path) {
                $file = CompiledYamlFile::instance($path);
                $content = (array)$file->content();
                if (!empty($content['schemes'])) {
                    $this->items['streams']['schemes'] = $content['schemes'] + $this->items['streams']['schemes'];
                }
            }
        } while (--$guard);

        if (!$guard) {
            throw new \RuntimeException('Setup: Configuration reload loop detected!');
        }

        // Make sure we have valid setup.
        $this->check($locator);

        return $this;
    }

    /**
     * Initialize resource locator by using the configuration.
     *
     * @param UniformResourceLocator $locator
     * @throws \BadMethodCallException
     */
    public function initializeLocator(UniformResourceLocator $locator)
    {
        $locator->reset();

        $schemes = (array) $this->get('streams.schemes', []);

        foreach ($schemes as $scheme => $config) {
            if (isset($config['paths'])) {
                $locator->addPath($scheme, '', $config['paths']);
            }

            $override = $config['override'] ?? false;
            $force = $config['force'] ?? false;

            if (isset($config['prefixes'])) {
                foreach ((array)$config['prefixes'] as $prefix => $paths) {
                    $locator->addPath($scheme, $prefix, $paths, $override, $force);
                }
            }
        }
    }

    /**
     * Get available streams and their types from the configuration.
     *
     * @return array
     */
    public function getStreams()
    {
        $schemes = [];
        foreach ((array) $this->get('streams.schemes') as $scheme => $config) {
            $type = $config['type'] ?? 'ReadOnlyStream';
            if ($type[0] !== '\\') {
                $type = '\\RocketTheme\\Toolbox\\StreamWrapper\\' . $type;
            }

            $schemes[$scheme] = $type;
        }

        return $schemes;
    }

    /**
     * @param UniformResourceLocator $locator
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    protected function check(UniformResourceLocator $locator)
    {
        $streams = $this->items['streams']['schemes'] ?? null;
        if (!\is_array($streams)) {
            throw new \InvalidArgumentException('Configuration is missing streams.schemes!');
        }
        $diff = array_keys(array_diff_key($this->streams, $streams));
        if ($diff) {
            throw new \InvalidArgumentException(
                sprintf('Configuration is missing keys %s from streams.schemes!', implode(', ', $diff))
            );
        }

        try {
            if (!$locator->findResource('environment://config', true)) {
                // If environment does not have its own directory, remove it from the lookup.
                $this->set('streams.schemes.environment.prefixes', ['config' => []]);
                $this->initializeLocator($locator);
            }

            // Create security.yaml if it doesn't exist.
            $filename = $locator->findResource('config://security.yaml', true, true);
            $security_file = CompiledYamlFile::instance($filename);
            $security_content = (array)$security_file->content();

            if (!isset($security_content['salt'])) {
                $security_content = array_merge($security_content, ['salt' => Utils::generateRandomString(14)]);
                $security_file->content($security_content);
                $security_file->save();
                $security_file->free();
            }
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Grav failed to initialize: %s', $e->getMessage()), 500, $e);
        }
    }
}

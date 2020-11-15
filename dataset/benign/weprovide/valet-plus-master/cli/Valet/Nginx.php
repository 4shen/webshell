<?php

namespace Valet;

use DomainException;

class Nginx
{
    public $brew;
    public $cli;
    public $files;
    public $configuration;
    public $site;
    const NGINX_CONF = '/usr/local/etc/nginx/nginx.conf';

    /**
     * Create a new Nginx instance.
     *
     * @param  Brew $brew
     * @param  CommandLine $cli
     * @param  Filesystem $files
     * @param  Configuration $configuration
     * @param  Site $site
     */
    public function __construct(
        Brew $brew,
        CommandLine $cli,
        Filesystem $files,
        Configuration $configuration,
        Site $site
    ) {
        $this->cli = $cli;
        $this->brew = $brew;
        $this->site = $site;
        $this->files = $files;
        $this->configuration = $configuration;
    }

    /**
     * Install service.
     *
     * @return void
     */
    public function install()
    {
        if (!$this->brew->hasInstalledNginx()) {
            $this->brew->installOrFail('nginx');
        }

        $this->installConfiguration();
        $this->installServer();
        $this->installNginxDirectory();

        return $this->configuration->read()['domain'];
    }

    /**
     * Install the configuration files.
     *
     * @return void
     */
    public function installConfiguration()
    {
        $contents = $this->files->get(__DIR__.'/../stubs/nginx.conf');

        $this->files->putAsUser(
            static::NGINX_CONF,
            str_replace(['VALET_USER', 'VALET_HOME_PATH'], [user(), VALET_HOME_PATH], $contents)
        );
    }

    /**
     * Install the Valet server configuration file.
     *
     * @return void
     */
    public function installServer()
    {
        $domain = $this->configuration->read()['domain'];

        $this->files->ensureDirExists('/usr/local/etc/nginx/valet');

        $this->files->putAsUser(
            '/usr/local/etc/nginx/valet/valet.conf',
            str_replace(
                ['VALET_HOME_PATH', 'VALET_SERVER_PATH', 'VALET_STATIC_PREFIX'],
                [VALET_HOME_PATH, VALET_SERVER_PATH, VALET_STATIC_PREFIX],
                $this->files->get(__DIR__.'/../stubs/valet.conf')
            )
        );

        $this->files->putAsUser(
            '/usr/local/etc/nginx/fastcgi_params',
            $this->files->get(__DIR__.'/../stubs/fastcgi_params')
        );
    }

    /**
     * Install the configuration directory to the ~/.valet directory.
     *
     * This directory contains all site-specific Nginx servers.
     *
     * @return void
     */
    public function installNginxDirectory()
    {
        if (! $this->files->isDir($nginxDirectory = VALET_HOME_PATH.'/Nginx')) {
            $this->files->mkdirAsUser($nginxDirectory);
        }

        $this->files->putAsUser($nginxDirectory.'/.keep', "\n");

        $this->rewriteSecureNginxFiles();
    }

    /**
     * Check nginx.conf for errors.
     */
    private function lint()
    {
        $this->cli->quietly(
            'sudo nginx -c '.static::NGINX_CONF.' -t',
            function ($exitCode, $outputMessage) {
                throw new DomainException("Nginx cannot start, please check your nginx.conf [$exitCode: $outputMessage].");
            }
        );
    }

    /**
     * Generate fresh Nginx servers for existing secure sites.
     *
     * @return void
     */
    public function rewriteSecureNginxFiles()
    {
        $domain = $this->configuration->read()['domain'];

        $this->site->resecureForNewDomain($domain, $domain);
    }

    /**
     * Restart the service.
     *
     * @return void
     */
    public function restart()
    {
        $this->lint();

        $this->brew->restartService($this->brew->nginxServiceName());
    }

    /**
     * Stop the service.
     *
     * @return void
     */
    public function stop()
    {
        info('[nginx] Stopping');

        $this->cli->quietly('sudo brew services stop '. $this->brew->nginxServiceName());
    }

    /**
     * Prepare for uninstallation.
     *
     * @return void
     */
    public function uninstall()
    {
        $this->stop();
    }
}

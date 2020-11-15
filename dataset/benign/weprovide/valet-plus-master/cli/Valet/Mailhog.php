<?php

namespace Valet;

class Mailhog extends AbstractService
{
    const NGINX_CONFIGURATION_STUB = __DIR__ . '/../stubs/mailhog.conf';
    const NGINX_CONFIGURATION_PATH = '/usr/local/etc/nginx/valet/mailhog.conf';

    public $brew;
    public $cli;
    public $files;
    public $site;

    /**
     * Create a new instance.
     *
     * @param  Brew          $brew
     * @param  CommandLine   $cli
     * @param  Filesystem    $files
     * @param  Configuration $configuration
     * @param  Site          $site
     */
    public function __construct(
        Brew $brew,
        CommandLine $cli,
        Filesystem $files,
        Configuration $configuration,
        Site $site
    ) {
        $this->cli   = $cli;
        $this->brew  = $brew;
        $this->site  = $site;
        $this->files = $files;
        parent::__construct($configuration);
    }

    /**
     * Install the service.
     *
     * @return void
     */
    public function install()
    {
        if ($this->installed()) {
            info('[mailhog] already installed');
        } else {
            $this->brew->installOrFail('mailhog');
        }
        $this->setEnabled(self::STATE_ENABLED);
        $this->restart();
    }

    /**
     * Returns wether mailhog is installed or not.
     *
     * @return bool
     */
    public function installed()
    {
        return $this->brew->installed('mailhog');
    }

    /**
     * Restart the service.
     *
     * @return void
     */
    public function restart()
    {
        if (!$this->installed() || !$this->isEnabled()) {
            return;
        }

        info('[mailhog] Restarting');
        $this->cli->quietlyAsUser('brew services restart mailhog');
    }

    /**
     * Stop the service.
     *
     * @return void
     */
    public function stop()
    {
        if (!$this->installed()) {
            return;
        }

        info('[mailhog] Stopping');
        $this->cli->quietlyAsUser('brew services stop mailhog');
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

    public function updateDomain($domain)
    {
        $this->files->putAsUser(
            self::NGINX_CONFIGURATION_PATH,
            str_replace(
                ['VALET_DOMAIN'],
                [$domain],
                $this->files->get(self::NGINX_CONFIGURATION_STUB)
            )
        );
    }
}

<?php

namespace PragmaRX\Tracker\Services;

use Illuminate\Foundation\Application;
use PragmaRX\Support\Config as Config;

class Authentication
{
    private $config;

    private $authentication = [];

    private $app;

    public function __construct(Config $config, Application $app)
    {
        $this->app = $app;

        $this->config = $config;
    }

    public function check()
    {
        return $this->executeAuthMethod($this->config->get('authenticated_check_method'));
    }

    private function executeAuthMethod($method)
    {
        $guards = $this->config->get('authentication_guards');
        // Make sure authentication_guards at least contains a null value to DRY code
        if (empty($guards)) {
            $guards[] = null;
        }

        foreach ($this->getAuthentication() as $auth) {
            foreach ($guards as $guard) {
                // Call guard() if not null
                if ($guard && $guard != 'null') {
                    $auth = $auth->guard($guard);
                }
            }
            if (is_callable([$auth, $method], true, $callable_name)) {
                if ($data = $auth->$method()) {
                    return $data;
                }
            }
        }

        return false;
    }

    private function getAuthentication()
    {
        foreach ((array) $this->config->get('authentication_ioc_binding') as $binding) {
            $this->authentication[] = $this->app->make($binding);
        }

        return $this->authentication;
    }

    public function user()
    {
        return $this->executeAuthMethod($this->config->get('authenticated_user_method'));
    }

    public function getCurrentUserId()
    {
        if ($this->check()) {
            return $this->user()->{$this->config->get('authenticated_user_id_column')};
        }
    }
}

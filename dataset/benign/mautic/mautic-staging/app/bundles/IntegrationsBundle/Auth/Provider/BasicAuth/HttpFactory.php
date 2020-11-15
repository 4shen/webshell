<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\IntegrationsBundle\Auth\Provider\BasicAuth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Mautic\IntegrationsBundle\Auth\Provider\AuthConfigInterface;
use Mautic\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;
use Mautic\IntegrationsBundle\Auth\Provider\AuthProviderInterface;
use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;

/**
 * Factory for building HTTP clients using basic auth.
 */
class HttpFactory implements AuthProviderInterface
{
    const NAME = 'basic_auth';

    /**
     * Cache of initialized clients.
     *
     * @var Client[]
     */
    private $initializedClients = [];

    public function getAuthType(): string
    {
        return self::NAME;
    }

    /**
     * @param CredentialsInterface|AuthCredentialsInterface $credentials
     * @param AuthConfigInterface                           $config
     *
     * @throws PluginNotConfiguredException
     */
    public function getClient(AuthCredentialsInterface $credentials, ?AuthConfigInterface $config = null): ClientInterface
    {
        if (!$this->credentialsAreConfigured($credentials)) {
            throw new PluginNotConfiguredException('Username and/or password is missing');
        }

        // Return cached initialized client if there is one.
        if (!empty($this->initializedClients[$credentials->getUsername()])) {
            return $this->initializedClients[$credentials->getUsername()];
        }

        $this->initializedClients[$credentials->getUsername()] = new Client(
            [
                'auth' => [
                    $credentials->getUsername(),
                    $credentials->getPassword(),
                ],
            ]
        );

        return $this->initializedClients[$credentials->getUsername()];
    }

    protected function credentialsAreConfigured(CredentialsInterface $credentials): bool
    {
        return $credentials->getUsername() && $credentials->getPassword();
    }
}

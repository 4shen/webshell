<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\IntegrationsBundle\Auth\Support\Oauth2\Token;

use kamermans\OAuth2\Persistence\TokenPersistenceInterface;
use kamermans\OAuth2\Token\TokenInterface;
use Mautic\IntegrationsBundle\Exception\IntegrationNotSetException;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use Mautic\PluginBundle\Entity\Integration;

class TokenPersistence implements TokenPersistenceInterface
{
    /**
     * @var IntegrationsHelper
     */
    private $integrationsHelper;

    /**
     * @var Integration|null
     */
    private $integration;

    public function __construct(IntegrationsHelper $integrationsHelper)
    {
        $this->integrationsHelper = $integrationsHelper;
    }

    /**
     * Restore the token data into the give token.
     *
     * @param TokenInterface|IntegrationToken $token
     *
     * @return TokenInterface|IntegrationToken Restored token
     */
    public function restoreToken(TokenInterface $token): TokenInterface
    {
        $apiKeys               = $this->getIntegration()->getApiKeys();
        $apiKeys['expires_at'] = $apiKeys['expires_at'] ?? null;

        return new IntegrationToken(
            empty($apiKeys['access_token']) ? null : $apiKeys['access_token'],
            empty($apiKeys['refresh_token']) ? null : $apiKeys['refresh_token'],
            $apiKeys['expires_at'] ? $apiKeys['expires_at'] - time() : -1
        );
    }

    /**
     * Save the token data.
     *
     * @param TokenInterface|IntegrationToken $token
     */
    public function saveToken(TokenInterface $token): void
    {
        $integration = $this->getIntegration();
        $oldApiKeys  = $integration->getApiKeys();

        if (null === $oldApiKeys) {
            $oldApiKeys = [];
        }

        $newApiKeys = [
            'access_token'  => $token->getAccessToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires_at'    => $token->getExpiresAt(),
        ];

        $extraData  = $token instanceof IntegrationToken ? $token->getExtraData() : [];
        $newApiKeys = array_merge($oldApiKeys, $extraData, $newApiKeys);

        $integration->setApiKeys($newApiKeys);
        $this->integrationsHelper->saveIntegrationConfiguration($integration);
    }

    /**
     * Delete the saved token data.
     */
    public function deleteToken(): void
    {
        $integration = $this->getIntegration();

        $apiKeys = $integration->getApiKeys();

        unset($apiKeys['access_token']);

        $integration->setApiKeys($apiKeys);

        $this->integrationsHelper->saveIntegrationConfiguration($integration);
    }

    /**
     * Returns true if a token exists (although it may not be valid).
     */
    public function hasToken(): bool
    {
        return !empty($this->getIntegration()->getApiKeys()['access_token']);
    }

    public function setIntegration(Integration $integration): void
    {
        $this->integration = $integration;
    }

    /**
     * @throws IntegrationNotSetException
     */
    private function getIntegration(): Integration
    {
        if ($this->integration) {
            return $this->integration;
        }

        throw new IntegrationNotSetException('Integration not set');
    }
}

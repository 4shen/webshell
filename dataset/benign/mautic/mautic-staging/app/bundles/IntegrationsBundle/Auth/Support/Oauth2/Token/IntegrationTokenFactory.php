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

use kamermans\OAuth2\Token\TokenInterface;

class IntegrationTokenFactory implements TokenFactoryInterface
{
    private $extraKeysToStore = [];

    public function __construct(array $extraKeysToStore = [])
    {
        $this->extraKeysToStore = $extraKeysToStore;
    }

    public function __invoke(array $data, ?TokenInterface $previousToken = null): IntegrationToken
    {
        $accessToken  = null;
        $refreshToken = null;
        $expiresAt    = null;

        // Read "access_token" attribute
        if (isset($data['access_token'])) {
            $accessToken = $data['access_token'];
        }

        // Read "refresh_token" attribute
        if (isset($data['refresh_token'])) {
            $refreshToken = $data['refresh_token'];
        } elseif (null !== $previousToken) {
            // When requesting a new access token with a refresh token, the
            // server may not resend a new refresh token. In that case we
            // should keep the previous refresh token as valid.
            //
            // See http://tools.ietf.org/html/rfc6749#section-6
            $refreshToken = $previousToken->getRefreshToken();
        }

        // Read the "expires_in" attribute
        $expiresIn = isset($data['expires_in']) ? (int) $data['expires_in'] : null;

        // Facebook unfortunately breaks the spec by using 'expires' instead of 'expires_in'
        if (!$expiresIn && isset($data['expires'])) {
            $expiresIn = (int) $data['expires'];
        }

        // Set the absolute expiration if a relative expiration was provided
        if ($expiresIn) {
            $expiresAt = time() + $expiresIn;
        }

        return new IntegrationToken($accessToken, $refreshToken, $expiresAt, $this->getExtraData($data));
    }

    private function getExtraData(array $data): array
    {
        $extraData = [];
        foreach ($this->extraKeysToStore as $key) {
            $extraData[$key] = $data[$key] ?? null;
        }

        return $extraData;
    }
}

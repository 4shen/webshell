<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ApiBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    public static function hasBasicAuth(Request $request): bool
    {
        return 0 === strpos(strtolower($request->headers->get('Authorization')), 'basic');
    }

    public static function isApiRequest(Request $request): bool
    {
        $requestUrl = $request->getRequestUri();

        // Check if /oauth or /api
        $isApiRequest = (false !== strpos($requestUrl, '/oauth') || false !== strpos($requestUrl, '/api'));

        defined('MAUTIC_API_REQUEST') or define('MAUTIC_API_REQUEST', $isApiRequest);

        return $isApiRequest;
    }
}

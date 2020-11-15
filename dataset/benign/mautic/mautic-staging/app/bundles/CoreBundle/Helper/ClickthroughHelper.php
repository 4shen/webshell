<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Helper;

use Mautic\CoreBundle\Exception\InvalidDecodedStringException;

class ClickthroughHelper
{
    /**
     * Encode an array to append to a URL.
     *
     * @return string
     */
    public static function encodeArrayForUrl(array $array)
    {
        return urlencode(base64_encode(serialize($array)));
    }

    /**
     * Decode a string appended to URL into an array.
     *
     * @param      $string
     * @param bool $urlDecode
     *
     * @return array
     */
    public static function decodeArrayFromUrl($string, $urlDecode = true)
    {
        $raw     = $urlDecode ? urldecode($string) : $string;
        $decoded = base64_decode($raw);

        if (empty($decoded)) {
            return [];
        }

        if (0 !== stripos($decoded, 'a')) {
            throw new InvalidDecodedStringException($decoded);
        }

        return Serializer::decode($decoded);
    }
}

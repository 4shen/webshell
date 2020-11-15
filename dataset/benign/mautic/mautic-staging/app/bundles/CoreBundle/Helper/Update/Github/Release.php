<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://www.mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Helper\Update\Github;

use Mautic\CoreBundle\Helper\Update\Exception\UpdatePackageNotFoundException;
use Mautic\CoreBundle\Release\Metadata;

class Release
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $downloadUrl;

    /**
     * @var string
     */
    private $announcementUrl;

    /**
     * @var string
     */
    private $stability;

    /**
     * @throws UpdatePackageNotFoundException
     */
    public function __construct(array $release, Metadata $metadata)
    {
        $this->version         = $release['tag_name'];
        $this->downloadUrl     = $this->parseUpdatePackage($release['assets']);
        $this->announcementUrl = $metadata->getAnnouncementUrl() ? $metadata->getAnnouncementUrl() : $release['html_url'];
        $this->stability       = $metadata->getStability();
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    public function getAnnouncementUrl(): string
    {
        return $this->announcementUrl;
    }

    public function getStability(): string
    {
        return $this->stability;
    }

    /**
     * @throws UpdatePackageNotFoundException
     */
    private function parseUpdatePackage(array $assets): string
    {
        foreach ($assets as $asset) {
            if (false !== strpos($asset['name'], 'update.zip')) {
                return $asset['browser_download_url'];
            }
        }

        throw new UpdatePackageNotFoundException();
    }
}

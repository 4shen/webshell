<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\IntegrationsBundle\Sync\Helper;

use Doctrine\DBAL\Connection;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;

class SyncDateHelper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \DateTimeInterface|null
     */
    private $syncFromDateTime;

    /**
     * @var \DateTimeInterface|null
     */
    private $syncToDateTime;

    /**
     * @var \DateTimeInterface|null
     */
    private $syncDateTime;

    /**
     * @var \DateTimeInterface[]
     */
    private $lastObjectSyncDates = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function setSyncDateTimes(?\DateTimeInterface $fromDateTime = null, ?\DateTimeInterface $toDateTime = null): void
    {
        $this->syncFromDateTime    = $fromDateTime;
        $this->syncToDateTime      = $toDateTime;
        $this->syncDateTime        = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->lastObjectSyncDates = [];
    }

    public function getSyncFromDateTime(string $integration, string $object): \DateTimeInterface
    {
        if ($this->syncFromDateTime) {
            // The command requested a specific start date so use it

            return $this->syncFromDateTime;
        }

        $key = $integration.$object;
        if (isset($this->lastObjectSyncDates[$key])) {
            // Use the same sync date for integrations to paginate properly

            return $this->lastObjectSyncDates[$key];
        }

        if (MauticSyncDataExchange::NAME !== $integration && $lastSync = $this->getLastSyncDateForObject($integration, $object)) {
            // Use the latest sync date recorded
            $this->lastObjectSyncDates[$key] = $lastSync;
        } else {
            // Otherwise, just sync the last 24 hours
            $this->lastObjectSyncDates[$key] = new \DateTimeImmutable('-24 hours', new \DateTimeZone('UTC'));
        }

        return $this->lastObjectSyncDates[$key];
    }

    public function getSyncToDateTime(): \DateTimeInterface
    {
        if ($this->syncToDateTime) {
            return $this->syncToDateTime;
        }

        return $this->syncDateTime;
    }

    public function getSyncDateTime(): ?\DateTimeInterface
    {
        return $this->syncDateTime;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastSyncDateForObject(string $integration, string $object): ?\DateTimeInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $result = $qb
            ->select('max(m.last_sync_date)')
            ->from(MAUTIC_TABLE_PREFIX.'sync_object_mapping', 'm')
            ->where(
                $qb->expr()->eq('m.integration', ':integration'),
                $qb->expr()->eq('m.integration_object_name', ':object')
            )
            ->setParameter('integration', $integration)
            ->setParameter('object', $object)
            ->execute()
            ->fetchColumn();

        if (!$result) {
            return null;
        }

        $lastSync = new \DateTimeImmutable($result, new \DateTimeZone('UTC'));

        // The last sync is out of the requested sync date/time range
        if ($this->syncFromDateTime && $lastSync < $this->syncFromDateTime) {
            return null;
        }

        // The last sync is out of the requested sync date/time range
        if ($lastSync > $this->getSyncToDateTime()) {
            return null;
        }

        return $lastSync;
    }
}

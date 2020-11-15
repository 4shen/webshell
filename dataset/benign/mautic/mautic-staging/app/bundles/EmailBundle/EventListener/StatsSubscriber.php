<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\EventListener\CommonStatsSubscriber;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\EmailBundle\Entity\StatDevice;

class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManager $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->repositories[]             = $repo             = $entityManager->getRepository(StatDevice::class);
        $devicesTable                     = $repo->getTableName();
        $this->permissions[$devicesTable] = [
            'stat.lead' => 'lead:leads',
        ];

        $this->repositories[]       = $repo       = $entityManager->getRepository(Stat::class);
        $statsTable                 = $repo->getTableName();
        $this->selects[$statsTable] =
            [
                'id',
                'email_id',
                'lead_id',
                'list_id',
                'ip_id',
                'email_address',
                'date_sent',
                'is_read',
                'is_failed',
                'viewed_in_browser',
                'date_read',
                'tracking_hash',
                'retry_count',
                'source',
                'source_id',
                'open_count',
                'last_opened',
                'open_details',
            ];
        $this->permissions[$statsTable] = [
            'lead' => 'lead:leads',
        ];
    }
}

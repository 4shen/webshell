<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Helper;

use Mautic\CampaignBundle\Event\CampaignLeadChangeEvent;

class CampaignEventHelper
{
    /**
     * Determine if this campaign applies.
     *
     * @param CampaignLeadChangeEvent $eventDetails
     *
     * @return bool
     */
    public static function validateLeadChangeTrigger(CampaignLeadChangeEvent $eventDetails = null, array $event)
    {
        if (null == $eventDetails) {
            return true;
        }

        $limitToCampaigns = $event['properties']['campaigns'];
        $action           = $event['properties']['action'];

        //check against selected campaigns
        if (!empty($limitToCampaigns) && !in_array($event['campaign']['id'], $limitToCampaigns)) {
            return false;
        }

        //check against the selected action (was lead removed or added)
        $func = 'was'.ucfirst($action);
        if (!method_exists($eventDetails, $func) || !$eventDetails->$func()) {
            return false;
        }

        return true;
    }
}

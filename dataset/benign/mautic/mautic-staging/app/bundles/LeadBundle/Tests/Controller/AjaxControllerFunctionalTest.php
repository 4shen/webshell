<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Tests\Controller;

use Doctrine\DBAL\Connection;
use Mautic\CampaignBundle\Tests\DataFixtures\Orm\CampaignData;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\InstallBundle\InstallFixtures\ORM\LeadFieldData;
use Mautic\InstallBundle\InstallFixtures\ORM\RoleData;
use Mautic\LeadBundle\DataFixtures\ORM\LoadLeadData;
use Mautic\UserBundle\DataFixtures\ORM\LoadRoleData;
use Mautic\UserBundle\DataFixtures\ORM\LoadUserData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxControllerFunctionalTest extends MauticMysqlTestCase
{
    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->container->get('doctrine.dbal.default_connection');

        defined('MAUTIC_TABLE_PREFIX') or define('MAUTIC_TABLE_PREFIX', '');
    }

    public function testToggleLeadCampaignAction()
    {
        $this->loadFixtures([LeadFieldData::class, RoleData::class, LoadRoleData::class, LoadUserData::class, CampaignData::class, LoadLeadData::class]);

        // Ensure there is no member for campaign 1 yet.
        $this->assertSame([], $this->getMembersForCampaign(1));

        // Create the member now.
        $payload = [
            'action'         => 'lead:toggleLeadCampaign',
            'leadId'         => 1,
            'campaignId'     => 1,
            'campaignAction' => 'add',
        ];

        $this->client->request(Request::METHOD_POST, '/s/ajax', $payload, [], $this->createAjaxHeaders());
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        // Ensure the contact 1 is a campaign 1 member now.
        $this->assertSame([['lead_id' => '1', 'manually_added' => '1', 'manually_removed' => '0']], $this->getMembersForCampaign(1));

        $this->assertEquals(Response::HTTP_OK, $clientResponse->getStatusCode());
        $this->assertTrue(isset($response['success']), 'The response does not contain the `success` param.');
        $this->assertSame(1, $response['success']);

        // Let's remove the member now.
        $payload = [
            'action'         => 'lead:toggleLeadCampaign',
            'leadId'         => 1,
            'campaignId'     => 1,
            'campaignAction' => 'remove',
        ];

        $this->client->request(Request::METHOD_POST, '/s/ajax', $payload, [], $this->createAjaxHeaders());
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        // Ensure the contact 1 was removed as a member of campaign 1 member now.
        $this->assertSame([['lead_id' => '1', 'manually_added' => '0', 'manually_removed' => '1']], $this->getMembersForCampaign(1));

        $this->assertEquals(Response::HTTP_OK, $clientResponse->getStatusCode());
        $this->assertTrue(isset($response['success']), 'The response does not contain the `success` param.');
        $this->assertSame(1, $response['success']);
    }

    private function getMembersForCampaign(int $campaignId): array
    {
        return $this->connection->createQueryBuilder()
            ->select('cl.lead_id, cl.manually_added, cl.manually_removed')
            ->from(MAUTIC_TABLE_PREFIX.'campaign_leads', 'cl')
            ->where("cl.campaign_id = {$campaignId}")
            ->execute()
            ->fetchAll();
    }
}

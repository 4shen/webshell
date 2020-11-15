<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Tests\Controller\Api;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;

class EmailApiControllerFunctionalTest extends MauticMysqlTestCase
{
    public function testSingleEmailWorkflow()
    {
        // Create a couple of segments first:
        $payload = [
            [
                'name'        => 'API segment A',
                'description' => 'Segment created via API test',
            ],
            [
                'name'        => 'API segment B',
                'description' => 'Segment created via API test',
            ],
        ];

        $this->client->request('POST', '/api/segments/batch/new', $payload);
        $clientResponse  = $this->client->getResponse();
        $segmentResponse = json_decode($clientResponse->getContent(), true);
        $segmentAId      = $segmentResponse['lists'][0]['id'];
        $segmentBId      = $segmentResponse['lists'][1]['id'];

        $this->assertSame(201, $clientResponse->getStatusCode(), $clientResponse->getContent());
        $this->assertGreaterThan(0, $segmentAId);

        // Create email with the new segment:
        $payload = [
            'name'       => 'API email',
            'subject'    => 'Email created via API test',
            'emailType'  => 'list',
            'lists'      => [$segmentAId],
            'customHtml' => '<h1>Email content created by an API test</h1>',
        ];

        $this->client->request('POST', '/api/emails/new', $payload);
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);
        $emailId        = $response['email']['id'];

        $this->assertSame(201, $clientResponse->getStatusCode(), $clientResponse->getContent());
        $this->assertGreaterThan(0, $emailId);
        $this->assertEquals($payload['name'], $response['email']['name']);
        $this->assertEquals($payload['subject'], $response['email']['subject']);
        $this->assertEquals($payload['emailType'], $response['email']['emailType']);
        $this->assertCount(1, $response['email']['lists']);
        $this->assertEquals($segmentAId, $response['email']['lists'][0]['id']);
        $this->assertEquals($payload['customHtml'], $response['email']['customHtml']);

        // Edit PATCH:
        $patchPayload = [
            'name'  => 'API email renamed',
            'lists' => [$segmentBId],
        ];
        $this->client->request('PATCH', "/api/emails/{$emailId}/edit", $patchPayload);
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        $this->assertSame(200, $clientResponse->getStatusCode(), $clientResponse->getContent());
        $this->assertSame($emailId, $response['email']['id']);
        $this->assertEquals('API email renamed', $response['email']['name']);
        $this->assertEquals($payload['subject'], $response['email']['subject']);
        $this->assertCount(1, $response['email']['lists']);
        $this->assertEquals($segmentBId, $response['email']['lists'][0]['id']);
        $this->assertEquals($payload['emailType'], $response['email']['emailType']);
        $this->assertEquals($payload['customHtml'], $response['email']['customHtml']);

        // Edit PUT:
        $payload['subject'] .= ' renamed';
        $payload['lists']    = [$segmentAId, $segmentBId];
        $payload['language'] = 'en'; // Must be present for PUT as all empty values are being cleared.
        $this->client->request('PUT', "/api/emails/{$emailId}/edit", $payload);
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        $this->assertSame(200, $clientResponse->getStatusCode(), $clientResponse->getContent());
        $this->assertSame($emailId, $response['email']['id']);
        $this->assertEquals($payload['name'], $response['email']['name']);
        $this->assertEquals('Email created via API test renamed', $response['email']['subject']);
        $this->assertCount(2, $response['email']['lists']);
        $this->assertEquals($segmentAId, $response['email']['lists'][1]['id']);
        $this->assertEquals($segmentBId, $response['email']['lists'][0]['id']);
        $this->assertEquals($payload['emailType'], $response['email']['emailType']);
        $this->assertEquals($payload['customHtml'], $response['email']['customHtml']);

        // Get:
        $this->client->request('GET', "/api/emails/{$emailId}");
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        $this->assertSame(200, $clientResponse->getStatusCode(), $clientResponse->getContent());
        $this->assertSame($emailId, $response['email']['id']);
        $this->assertEquals($payload['name'], $response['email']['name']);
        $this->assertEquals($payload['subject'], $response['email']['subject']);
        $this->assertCount(2, $response['email']['lists']);
        $this->assertEquals($payload['emailType'], $response['email']['emailType']);
        $this->assertEquals($payload['customHtml'], $response['email']['customHtml']);

        // Delete:
        $this->client->request('DELETE', "/api/emails/{$emailId}/delete");
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        $this->assertSame(200, $clientResponse->getStatusCode(), $clientResponse->getContent());
        $this->assertNull($response['email']['id']);
        $this->assertEquals($payload['name'], $response['email']['name']);
        $this->assertEquals($payload['subject'], $response['email']['subject']);
        $this->assertCount(2, $response['email']['lists']);
        $this->assertEquals($payload['emailType'], $response['email']['emailType']);
        $this->assertEquals($payload['customHtml'], $response['email']['customHtml']);

        // Get (ensure it's deleted):
        $this->client->request('GET', "/api/emails/{$emailId}");
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        $this->assertSame(404, $clientResponse->getStatusCode(), $clientResponse->getContent());
        $this->assertSame(404, $response['errors'][0]['code']);

        // Delete also testing segments:
        $this->client->request('DELETE', '/api/segments/batch/delete', [['id' => $segmentAId], ['id' => $segmentBId]]);
        $clientResponse = $this->client->getResponse();
        $response       = json_decode($clientResponse->getContent(), true);

        $this->assertSame(['lists' => []], $response);
        $this->assertSame(200, $clientResponse->getStatusCode(), $clientResponse->getContent());
    }
}

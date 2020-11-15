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

namespace Mautic\IntegrationsBundle\Tests\Unit\Sync\SyncDataExchange\Internal;

use Mautic\IntegrationsBundle\Event\InternalObjectEvent;
use Mautic\IntegrationsBundle\IntegrationEvents;
use Mautic\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use Mautic\LeadBundle\Entity\Lead;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectProviderTest extends TestCase
{
    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dispatcher;

    /**
     * @var ObjectProvider
     */
    private $objectProvider;

    protected function setUp(): void
    {
        $this->dispatcher     = $this->createMock(EventDispatcherInterface::class);
        $this->objectProvider = new ObjectProvider($this->dispatcher);
    }

    public function testGetObjectByNameIfItDoesNotExist(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS,
                $this->isInstanceOf(InternalObjectEvent::class)
            );

        $this->expectException(ObjectNotFoundException::class);
        $this->objectProvider->getObjectByName('Unicorn');
    }

    public function testGetObjectByNameIfItExists(): void
    {
        $contact = new Contact();
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS,
                $this->callback(function (InternalObjectEvent $e) use ($contact) {
                    // Fake a subscriber.
                    $e->addObject($contact);

                    return true;
                })
            );

        $this->assertSame($contact, $this->objectProvider->getObjectByName(Contact::NAME));
    }

    public function testGetObjectByEntityNameIfItDoesNotExist(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS,
                $this->isInstanceOf(InternalObjectEvent::class)
            );

        $this->expectException(ObjectNotFoundException::class);
        $this->objectProvider->getObjectByEntityName('Unicorn');
    }

    public function testGetObjectByEntityNameIfItExists(): void
    {
        $contact = new Contact();
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS,
                $this->callback(function (InternalObjectEvent $e) use ($contact) {
                    // Fake a subscriber.
                    $e->addObject($contact);

                    return true;
                })
            );

        $this->assertSame($contact, $this->objectProvider->getObjectByEntityName(Lead::class));
    }
}

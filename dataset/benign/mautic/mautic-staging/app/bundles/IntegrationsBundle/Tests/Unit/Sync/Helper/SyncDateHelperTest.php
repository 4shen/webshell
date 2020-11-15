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

namespace Mautic\IntegrationsBundle\Tests\Unit\Sync\Helper;

use Mautic\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use PHPUnit\Framework\TestCase;

class SyncDateHelperTest extends TestCase
{
    /**
     * @var SyncDateHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $syncDateHelper;

    protected function setUp(): void
    {
        $this->syncDateHelper = $this->getMockBuilder(SyncDateHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLastSyncDateForObject'])
            ->getMock();
    }

    public function testSpecifiedFromDateTimeIsReturned(): void
    {
        $syncFromDateTime = new \DateTimeImmutable('2018-10-08 00:00:00');

        $this->syncDateHelper->setSyncDateTimes($syncFromDateTime);

        $this->assertEquals($syncFromDateTime, $this->syncDateHelper->getSyncFromDateTime('Test', 'Object'));
    }

    public function testLastSyncDateForIntegrationSyncObjectIsReturned(): void
    {
        $objectLastSyncDate = new \DateTimeImmutable('2018-10-08 00:00:00');

        $this->syncDateHelper->method('getLastSyncDateForObject')
            ->willReturn($objectLastSyncDate);

        $this->assertEquals($objectLastSyncDate, $this->syncDateHelper->getSyncFromDateTime('Test', 'Object'));
    }

    public function testSyncToDateTimeIsReturnedIfSpecified(): void
    {
        $syncToDateTime = new \DateTimeImmutable('2018-10-08 00:00:00');

        $this->syncDateHelper->setSyncDateTimes(null, $syncToDateTime);

        $this->assertEquals($syncToDateTime, $this->syncDateHelper->getSyncToDateTime());
    }

    public function testSyncDateTimeIsReturnedForSyncToDateTimeIfNotSpecified(): void
    {
        $this->syncDateHelper->setSyncDateTimes();

        $this->assertInstanceOf(\DateTimeImmutable::class, $this->syncDateHelper->getSyncToDateTime());
    }
}

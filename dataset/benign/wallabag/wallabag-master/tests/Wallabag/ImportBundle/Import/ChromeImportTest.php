<?php

namespace Tests\Wallabag\ImportBundle\Import;

use M6Web\Component\RedisMock\RedisMockFactory;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Simpleue\Queue\RedisQueue;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\ImportBundle\Import\ChromeImport;
use Wallabag\ImportBundle\Redis\Producer;
use Wallabag\UserBundle\Entity\User;

class ChromeImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;

    public function testInit()
    {
        $chromeImport = $this->getChromeImport();

        $this->assertSame('Chrome', $chromeImport->getName());
        $this->assertNotEmpty($chromeImport->getUrl());
        $this->assertSame('import.chrome.description', $chromeImport->getDescription());
    }

    public function testImport()
    {
        $chromeImport = $this->getChromeImport(false, 1);
        $chromeImport->setFilepath(__DIR__ . '/../fixtures/chrome-bookmarks');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(1))
            ->method('findByUrlAndUserId')
            ->willReturn(false);

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $entry = $this->getMockBuilder('Wallabag\CoreBundle\Entity\Entry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->exactly(1))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $chromeImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 1, 'queued' => 0], $chromeImport->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $chromeImport = $this->getChromeImport(false, 1);
        $chromeImport->setFilepath(__DIR__ . '/../fixtures/chrome-bookmarks');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(1))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->exactly(1))
            ->method('updateEntry')
            ->willReturn(new Entry($this->user));

        // check that every entry persisted are archived
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(function ($persistedEntry) {
                return (bool) $persistedEntry->isArchived();
            }));

        $res = $chromeImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 0, 'imported' => 1, 'queued' => 0], $chromeImport->getSummary());
    }

    public function testImportWithRabbit()
    {
        $chromeImport = $this->getChromeImport();
        $chromeImport->setFilepath(__DIR__ . '/../fixtures/chrome-bookmarks');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = $this->getMockBuilder('Wallabag\CoreBundle\Entity\Entry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();

        $producer
            ->expects($this->exactly(1))
            ->method('publish');

        $chromeImport->setProducer($producer);

        $res = $chromeImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 1], $chromeImport->getSummary());
    }

    public function testImportWithRedis()
    {
        $chromeImport = $this->getChromeImport();
        $chromeImport->setFilepath(__DIR__ . '/../fixtures/chrome-bookmarks');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = $this->getMockBuilder('Wallabag\CoreBundle\Entity\Entry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter('Predis\Client', true);

        $queue = new RedisQueue($redisMock, 'chrome');
        $producer = new Producer($queue);

        $chromeImport->setProducer($producer);

        $res = $chromeImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 1], $chromeImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('chrome'));
    }

    public function testImportBadFile()
    {
        $chromeImport = $this->getChromeImport();
        $chromeImport->setFilepath(__DIR__ . '/../fixtures/wallabag-v1.jsonx');

        $res = $chromeImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Wallabag Browser Import: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $chromeImport = $this->getChromeImport(true);
        $chromeImport->setFilepath(__DIR__ . '/../fixtures/chrome-bookmarks');

        $res = $chromeImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Wallabag Browser Import: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getChromeImport($unsetUser = false, $dispatched = 0)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy = $this->getMockBuilder('Wallabag\CoreBundle\Helper\ContentProxy')
            ->disableOriginalConstructor()
            ->getMock();

        $this->tagsAssigner = $this->getMockBuilder('Wallabag\CoreBundle\Helper\TagsAssigner')
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->exactly($dispatched))
            ->method('dispatch');

        $wallabag = new ChromeImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher);

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);
        $wallabag->setLogger($logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }
}

<?php
/**
 * @author Piotr Mrowczynski <piotr@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Test\Diagnostics;

use OC\Diagnostics\EventLogger;
use Test\TestCase;

class EventLoggerTest extends TestCase {

	/** @var \OC\Diagnostics\EventLogger */
	private $logger;

	public function setUp(): void {
		parent::setUp();

		$this->logger = new EventLogger();
	}

	public function testQueryLogger() {
		// Module is not activated and this should not be logged
		$this->logger->start("test1", "testevent1");
		$this->logger->end("test1");
		$this->logger->log("test2", "testevent2", \microtime(true), \microtime(true));
		$events = $this->logger->getEvents();
		$this->assertCount(0, $events);

		// Activate module and log some query
		$this->logger->activate();

		// start one event
		$this->logger->start("test3", "testevent3");

		// force log of another event
		$this->logger->log("test4", "testevent4", \microtime(true), \microtime(true));

		// log started event
		$this->logger->end("test3");

		$events = $this->logger->getEvents();
		$this->assertSame("test4", $events['test4']->getId());
		$this->assertSame("testevent4", $events['test4']->getDescription());
		$this->assertSame("test3", $events['test3']->getId());
		$this->assertSame("testevent3", $events['test3']->getDescription());
		$this->assertCount(2, $events);
	}
}

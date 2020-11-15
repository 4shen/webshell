<?php
 /**
 * ownCloud
 *
 * @author Thomas Müller
 * @copyright Copyright (c) 2013 Thomas Müller deepdiver@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Test\OCS;

use OC_OCS_Privatedata;

/**
 * Class PrivatedataTest
 *
 * @group DB
 */
class PrivatedataTest extends \Test\TestCase {
	private $appKey;

	protected function setUp(): void {
		parent::setUp();
		\OC::$server->getSession()->set('user_id', 'user1');
		$this->appKey = $this->getUniqueID('app');
	}

	public function testGetEmptyOne() {
		$params = ['app' => $this->appKey, 'key' => '123'];
		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(0, $result);
	}

	public function testGetEmptyAll() {
		$params = ['app' => $this->appKey];
		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(0, $result);
	}

	public function testSetOne() {
		$_POST = ['value' => 123456789];
		$params = ['app' => $this->appKey, 'key' => 'k-1'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(1, $result);
	}

	public function testSetExisting() {
		$_POST = ['value' => 123456789];
		$params = ['app' => $this->appKey, 'key' => 'k-10'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(1, $result);
		$data = $result->getData();
		$data = $data[0];
		$this->assertEquals('123456789', $data['value']);

		$_POST = ['value' => 'updated'];
		$params = ['app' => $this->appKey, 'key' => 'k-10'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(1, $result);
		$data = $result->getData();
		$data = $data[0];
		$this->assertEquals('updated', $data['value']);
	}

	public function testSetSameValue() {
		$_POST = ['value' => 123456789];
		$params = ['app' => $this->appKey, 'key' => 'k-10'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(1, $result);
		$data = $result->getData();
		$data = $data[0];
		$this->assertEquals('123456789', $data['value']);

		// set the same value again
		$_POST = ['value' => 123456789];
		$params = ['app' => $this->appKey, 'key' => 'k-10'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(1, $result);
		$data = $result->getData();
		$data = $data[0];
		$this->assertEquals('123456789', $data['value']);
	}

	public function testSetMany() {
		$_POST = ['value' => 123456789];

		// set key 'k-1'
		$params = ['app' => $this->appKey, 'key' => 'k-1'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		// set key 'k-2'
		$params = ['app' => $this->appKey, 'key' => 'k-2'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		// query for all
		$params = ['app' => $this->appKey];
		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(2, $result);
	}

	public function testDelete() {
		$_POST = ['value' => 123456789];

		// set key 'k-1'
		$params = ['app' => $this->appKey, 'key' => 'k-3'];
		$result = OC_OCS_Privatedata::set($params);
		$this->assertEquals(100, $result->getStatusCode());

		$result = OC_OCS_Privatedata::delete($params);
		$this->assertEquals(100, $result->getStatusCode());

		$result = OC_OCS_Privatedata::get($params);
		$this->assertOcsResult(0, $result);
	}

	/**
	 * @dataProvider deleteWithEmptyKeysProvider
	 */
	public function testDeleteWithEmptyKeys($params) {
		$result = OC_OCS_Privatedata::delete($params);
		$this->assertEquals(101, $result->getStatusCode());
	}

	public function deleteWithEmptyKeysProvider() {
		return [
			[[]],
			[['app' => '123']],
			[['key' => '123']],
		];
	}

	/**
	 * @param \OC_OCS_Result $result
	 * @param integer $expectedArraySize
	 */
	public function assertOcsResult($expectedArraySize, $result) {
		$this->assertEquals(100, $result->getStatusCode());
		$data = $result->getData();
		$this->assertIsArray($data);
		$this->assertCount($expectedArraySize, $data);
	}
}

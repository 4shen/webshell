<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files\Cache\Wrapper;

use Test\Files\Cache\CacheTest;

/**
 * Class CacheJail
 *
 * @group DB
 *
 * @package Test\Files\Cache\Wrapper
 */
class CacheJailTest extends CacheTest {
	/**
	 * @var \OC\Files\Cache\Cache $sourceCache
	 */
	protected $sourceCache;

	public function setUp(): void {
		parent::setUp();
		$this->storage->mkdir('foo');
		$this->sourceCache = $this->cache;
		$this->cache = new \OC\Files\Cache\Wrapper\CacheJail($this->sourceCache, 'foo');
	}

	protected function tearDown(): void {
		if ($this->sourceCache) {
			$this->sourceCache->clear();
		}
	}

	public function testSearchOutsideJail() {
		$file1 = 'foo/foobar';
		$file2 = 'folder/foobar';
		$data1 = ['size' => 100, 'mtime' => 50, 'mimetype' => 'foo/folder'];

		$this->sourceCache->put($file1, $data1);
		$this->sourceCache->put($file2, $data1);

		$this->assertCount(2, $this->sourceCache->search('%foobar'));

		$result = $this->cache->search('%foobar%');
		$this->assertCount(1, $result);
		$this->assertEquals('foobar', $result[0]['path']);
	}

	public function testClearKeepEntriesOutsideJail() {
		$file1 = 'foo/foobar';
		$file2 = 'foo/foobar/asd';
		$file3 = 'folder/foobar';
		$data1 = ['size' => 100, 'mtime' => 50, 'mimetype' => 'httpd/unix-directory'];

		$this->sourceCache->put('foo', $data1);
		$this->sourceCache->put($file1, $data1);
		$this->sourceCache->put($file2, $data1);
		$this->sourceCache->put($file3, $data1);

		$this->cache->clear();

		$this->assertFalse($this->cache->inCache('foobar'));
		$this->assertTrue($this->sourceCache->inCache('folder/foobar'));
	}

	public function testGetById() {
		$data1 = ['size' => 100, 'mtime' => 50, 'mimetype' => 'httpd/unix-directory'];
		$id = $this->sourceCache->put('foo/bar', $data1);

		// path from jailed foo of foo/bar is bar
		$path = $this->cache->getPathById($id);
		$this->assertEquals('bar', $path);

		// path from jailed '' of foo/bar is foo/bar
		$this->cache = new \OC\Files\Cache\Wrapper\CacheJail($this->sourceCache, '');
		$path = $this->cache->getPathById($id);
		$this->assertEquals('foo/bar', $path);
	}

	public function testGetIncomplete() {
		//not supported
		$this->assertTrue(true);
	}
}

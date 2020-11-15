<?php
/**
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace Test\Files\Type;

use OC\Files\Type\Detection;
use org\bovigo\vfs\vfsStream;

class DetectionTest extends \Test\TestCase {
	/** @var Detection */
	private $detection;

	public function setUp(): void {
		parent::setUp();
		$this->detection = new Detection(
			\OC::$server->getURLGenerator(),
			\OC::$SERVERROOT . '/config/',
			\OC::$SERVERROOT . '/resources/config/'
		);
	}

	public function testDetect() {
		$dir = \OC::$SERVERROOT.'/tests/data';

		$result = $this->detection->detect($dir."/");
		$expected = 'httpd/unix-directory';
		$this->assertEquals($expected, $result);

		$result = $this->detection->detect($dir."/data.tar.gz");
		$expected = 'application/x-gzip';
		$this->assertEquals($expected, $result);

		$result = $this->detection->detect($dir."/data.zip");
		$expected = 'application/zip';
		$this->assertEquals($expected, $result);

		$result = $this->detection->detect($dir."/testimagelarge.svg");
		$expected = 'image/svg+xml';
		$this->assertEquals($expected, $result);

		$result = $this->detection->detect($dir."/testimage.png");
		$expected = 'image/png';
		$this->assertEquals($expected, $result);
	}

	public function testGetSecureMimeType() {
		$result = $this->detection->getSecureMimeType('image/svg+xml');
		$expected = 'text/plain';
		$this->assertEquals($expected, $result);

		$result = $this->detection->getSecureMimeType('image/png');
		$expected = 'image/png';
		$this->assertEquals($expected, $result);
	}

	public function testDetectPath() {
		$this->assertEquals('text/plain', $this->detection->detectPath('foo.txt'));
		$this->assertEquals('image/png', $this->detection->detectPath('foo.png'));
		$this->assertEquals('image/png', $this->detection->detectPath('foo.bar.png'));
		$this->assertEquals('image/png', $this->detection->detectPath('.hidden/foo.png'));
		$this->assertEquals('image/png', $this->detection->detectPath('test.jpg/foo.png'));
		$this->assertEquals('application/octet-stream', $this->detection->detectPath('.png'));
		$this->assertEquals('application/x-sharedlib', $this->detection->detectPath('test.so'));
		$this->assertEquals('application/x-sharedlib', $this->detection->detectPath('test.so.0.0.1'));
		$this->assertEquals('application/x-sharedlib', $this->detection->detectPath('test.1.2.so.0.0.1'));
		$this->assertEquals('application/x-sharedlib', $this->detection->detectPath('test.1.2.so.1'));
		$this->assertEquals('application/x-sharedlib', $this->detection->detectPath('foo.so.'));
		$this->assertEquals('application/octet-stream', $this->detection->detectPath('foo'));
		$this->assertEquals('application/octet-stream', $this->detection->detectPath('foo.somany'));
		$this->assertEquals('application/octet-stream', $this->detection->detectPath('foo.so*'));
		$this->assertEquals('application/octet-stream', $this->detection->detectPath(''));
	}

	public function testDetectString() {
		$result = $this->detection->detectString("/data/data.tar.gz");
		$expected = 'text/plain; charset=us-ascii';
		$this->assertEquals($expected, $result);
	}

	public function testMimeTypeIcon() {
		$confDir = vfsStream::setup();
		$mimetypealiases_dist = vfsStream::newFile('mimetypealiases.dist.json')->at($confDir);

		//Empty alias file
		$mimetypealiases_dist->setContent(\json_encode([], JSON_FORCE_OBJECT));

		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('core'), $this->equalTo('filetypes/folder.svg'))
			->willReturn('folder.svg');

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('dir');
		$this->assertEquals('folder.svg', $mimeType);

		/*
		 * Test dir-shareed mimetype
		 */
		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('core'), $this->equalTo('filetypes/folder-shared.svg'))
			->willReturn('folder-shared.svg');

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('dir-shared');
		$this->assertEquals('folder-shared.svg', $mimeType);

		/*
		 * Test dir external
		 */

		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('core'), $this->equalTo('filetypes/folder-external.svg'))
			->willReturn('folder-external.svg');

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('dir-external');
		$this->assertEquals('folder-external.svg', $mimeType);

		/*
		 * Test complete mimetype
		 */

		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('core'), $this->equalTo('filetypes/my-type.svg'))
			->willReturn('my-type.svg');

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('my-type');
		$this->assertEquals('my-type.svg', $mimeType);

		/*
		 * Test subtype
		 */

		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->exactly(2))
			->method('imagePath')
			->withConsecutive(
				[$this->equalTo('core'), $this->equalTo('filetypes/my-type.svg')],
				[$this->equalTo('core'), $this->equalTo('filetypes/my.svg')]
			)
			->will($this->returnCallback(
				function ($appName, $file) {
					if ($file === 'filetypes/my.svg') {
						return 'my.svg';
					}
					throw new \RuntimeException();
				}
			));

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('my-type');
		$this->assertEquals('my.svg', $mimeType);

		/*
		 * Test default mimetype
		 */

		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->exactly(3))
			->method('imagePath')
			->withConsecutive(
				[$this->equalTo('core'), $this->equalTo('filetypes/foo-bar.svg')],
				[$this->equalTo('core'), $this->equalTo('filetypes/foo.svg')],
				[$this->equalTo('core'), $this->equalTo('filetypes/file.svg')]
			)
			->will($this->returnCallback(
				function ($appName, $file) {
					if ($file === 'filetypes/file.svg') {
						return 'file.svg';
					}
					throw new \RuntimeException();
				}
			));

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('foo-bar');
		$this->assertEquals('file.svg', $mimeType);

		/*
		 * Test chaching
		 */

		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('core'), $this->equalTo('filetypes/foo-bar.svg'))
			->willReturn('foo-bar.svg');

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('foo-bar');
		$this->assertEquals('foo-bar.svg', $mimeType);
		$mimeType = $detection->mimeTypeIcon('foo-bar');
		$this->assertEquals('foo-bar.svg', $mimeType);

		/*
		 * Test aliases
		 */

		//Put alias
		$mimetypealiases_dist->setContent(\json_encode(['foo' => 'foobar/baz'], JSON_FORCE_OBJECT));

		//Mock UrlGenerator
		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//Only call the url generator once
		$urlGenerator->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('core'), $this->equalTo('filetypes/foobar-baz.svg'))
			->willReturn('foobar-baz.svg');

		$detection = new Detection($urlGenerator, $confDir->url(), $confDir->url());
		$mimeType = $detection->mimeTypeIcon('foo');
		$this->assertEquals('foobar-baz.svg', $mimeType);
	}
}

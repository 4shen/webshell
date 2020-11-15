<?php

/**
* ownCloud - App Framework
*
* @author Bernhard Posselt
* @copyright Copyright (c) 2012 Bernhard Posselt dev@bernhard-posselt.com
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

namespace Test\AppFramework\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method integer getId()
 * @method void setId(integer $id)
 * @method integer getTestId()
 * @method void setTestId(integer $id)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getEmail()
 * @method void setEmail(string $email)
 * @method string getPreName()
 * @method void setPreName(string $preName)
 */
class TestEntity extends Entity {
	protected $name;
	protected $email;
	protected $testId;
	protected $preName;

	public function __construct($name=null) {
		$this->addType('testId', 'integer');
		$this->name = $name;
	}
};

class EntityTest extends \Test\TestCase {
	private $entity;

	protected function setUp(): void {
		parent::setUp();
		$this->entity = new TestEntity();
	}

	public function testResetUpdatedFields() {
		$entity = new TestEntity();
		$entity->setId(3);
		$entity->resetUpdatedFields();

		$this->assertEquals([], $entity->getUpdatedFields());
	}

	public function testFromRow() {
		$row = [
			'pre_name' => 'john',
			'email' => 'john@something.com'
		];
		$this->entity = TestEntity::fromRow($row);

		$this->assertEquals($row['pre_name'], $this->entity->getPreName());
		$this->assertEquals($row['email'], $this->entity->getEmail());
	}

	public function testGetSetId() {
		$id = 3;
		$this->entity->setId(3);

		$this->assertEquals($id, $this->entity->getId());
	}

	public function testColumnToPropertyNoReplacement() {
		$column = 'my';
		$this->assertEquals('my',
			$this->entity->columnToProperty($column));
	}

	public function testColumnToProperty() {
		$column = 'my_attribute';
		$this->assertEquals('myAttribute',
			$this->entity->columnToProperty($column));
	}

	public function testPropertyToColumnNoReplacement() {
		$property = 'my';
		$this->assertEquals('my',
			$this->entity->propertyToColumn($property));
	}

	public function testSetterMarksFieldUpdated() {
		$this->entity->setId(3);

		$this->assertArrayHasKey('id', $this->entity->getUpdatedFields());
	}

	public function testCallShouldOnlyWorkForGetterSetter() {
		$this->expectException('\BadFunctionCallException');

		$this->entity->something();
	}

	public function testGetterShouldFailIfAttributeNotDefined() {
		$this->expectException('\BadFunctionCallException');

		$this->entity->getTest();
	}

	public function testSetterShouldFailIfAttributeNotDefined() {
		$this->expectException('\BadFunctionCallException');

		$this->entity->setTest();
	}

	public function testFromRowShouldNotAssignEmptyArray() {
		$row = [];
		$entity2 = new TestEntity();

		$this->entity = TestEntity::fromRow($row);
		$this->assertEquals($entity2, $this->entity);
	}

	public function testIdGetsConvertedToInt() {
		$row = ['id' => '4'];

		$this->entity = TestEntity::fromRow($row);
		$this->assertSame(4, $this->entity->getId());
	}

	public function testSetType() {
		$row = ['testId' => '4'];

		$this->entity = TestEntity::fromRow($row);
		$this->assertSame(4, $this->entity->getTestId());
	}

	public function testFromParams() {
		$params = [
			'testId' => 4,
			'email' => 'john@doe'
		];

		$entity = TestEntity::fromParams($params);

		$this->assertEquals($params['testId'], $entity->getTestId());
		$this->assertEquals($params['email'], $entity->getEmail());
		$this->assertInstanceOf(TestEntity::class, $entity);
	}

	public function testSlugify() {
		$entity = new TestEntity();
		$entity->setName('Slugify this!');
		$this->assertEquals('slugify-this', $entity->slugify('name'));
		$entity->setName('°!"§$%&/()=?`´ß\}][{³²#\'+~*-_.:,;<>|äöüÄÖÜSlugify this!');
		$this->assertEquals('slugify-this', $entity->slugify('name'));
	}

	public function testSetterCasts() {
		$entity = new TestEntity();
		$entity->setId('3');
		$this->assertSame(3, $entity->getId());
	}

	public function testSetterDoesNotCastOnNull() {
		$entity = new TestEntity();
		$entity->setId(null);
		$this->assertNull($entity->getId());
	}

	public function testGetFieldTypes() {
		$entity = new TestEntity();
		$this->assertEquals([
			'id' => 'integer',
			'testId' => 'integer'
		], $entity->getFieldTypes());
	}

	public function testGetItInt() {
		$entity = new TestEntity();
		$entity->setId(3);
		$this->assertEquals('integer', \gettype($entity->getId()));
	}

	public function testFieldsNotMarkedUpdatedIfNothingChanges() {
		$entity = new TestEntity('hey');
		$entity->setName('hey');
		$this->assertCount(0, $entity->getUpdatedFields());
	}
}

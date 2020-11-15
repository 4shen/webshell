<?php
/**
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Vincent Petry <pvince81@owncloud.com>
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

namespace OC\Files\External;

/**
 * Trait for objects requiring an identifier (and/or identifier aliases)
 * Also supports deprecation to a different object, linking the objects
 */
trait IdentifierTrait {

	/** @var string */
	protected $identifier;

	/** @var string[] */
	protected $identifierAliases = [];

	/** @var object | null */
	protected $deprecateTo = null;

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param string $identifier
	 * @return self
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
		$this->identifierAliases[] = $identifier;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getIdentifierAliases() {
		return $this->identifierAliases;
	}

	/**
	 * @param string $alias
	 * @return self
	 */
	public function addIdentifierAlias($alias) {
		$this->identifierAliases[] = $alias;
		return $this;
	}

	/**
	 * @return object|null
	 */
	public function getDeprecateTo() {
		return $this->deprecateTo;
	}

	/**
	 * @param object $destinationObject
	 * @return self
	 */
	public function deprecateTo($destinationObject) {
		$this->deprecateTo = $destinationObject;
		return $this;
	}

	/**
	 * @return array
	 */
	public function jsonSerializeIdentifier() {
		$data = [
			'identifier' => $this->identifier,
			'identifierAliases' => $this->identifierAliases,
		];
		if ($this->deprecateTo) {
			$data['deprecateTo'] = $this->deprecateTo->getIdentifier();
		}
		return $data;
	}
}

<?php
/**
 * @author Andreas Fischer <bantu@owncloud.com>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OC\App;

use InvalidArgumentException;
use OCP\App\AppNotFoundException;

class InfoParser {

	/**
	 * @param string $file the xml file to be loaded
	 * @return array
	 * @throws AppNotFoundException if file does not exist
	 * @throws InvalidArgumentException on malformed XML
	 */
	public function parse($file) {
		if (!\is_file($file)) {
			throw new AppNotFoundException(
				\sprintf('%s does not exist', $file)
			);
		}

		\libxml_use_internal_errors(true);
		$loadEntities = \libxml_disable_entity_loader(false);
		$xml = \simplexml_load_file($file);

		\libxml_disable_entity_loader($loadEntities);
		if ($xml === false) {
			\libxml_clear_errors();
			throw new InvalidArgumentException('Invalid XML');
		}
		$array = $this->xmlToArray($xml);

		if (!\is_array($array)) {
			throw new InvalidArgumentException('Could not convert XML to array');
		}

		if (!\array_key_exists('info', $array)) {
			$array['info'] = [];
		}
		if (!\array_key_exists('remote', $array)) {
			$array['remote'] = [];
		}
		if (!\array_key_exists('public', $array)) {
			$array['public'] = [];
		}
		if (!\array_key_exists('types', $array)) {
			$array['types'] = [];
		}
		if (!\array_key_exists('repair-steps', $array)) {
			$array['repair-steps'] = [];
		}
		if (!\array_key_exists('install', $array['repair-steps'])) {
			$array['repair-steps']['install'] = [];
		}
		if (!\array_key_exists('pre-migration', $array['repair-steps'])) {
			$array['repair-steps']['pre-migration'] = [];
		}
		if (!\array_key_exists('post-migration', $array['repair-steps'])) {
			$array['repair-steps']['post-migration'] = [];
		}
		if (!\array_key_exists('live-migration', $array['repair-steps'])) {
			$array['repair-steps']['live-migration'] = [];
		}
		if (!\array_key_exists('uninstall', $array['repair-steps'])) {
			$array['repair-steps']['uninstall'] = [];
		}
		if (!\array_key_exists('background-jobs', $array)) {
			$array['background-jobs'] = [];
		}
		if (!\array_key_exists('two-factor-providers', $array)) {
			$array['two-factor-providers'] = [];
		}
		if (!\array_key_exists('commands', $array)) {
			$array['commands'] = [];
		}

		if (\array_key_exists('types', $array)) {
			if (\is_array($array['types'])) {
				foreach ($array['types'] as $type => $v) {
					unset($array['types'][$type]);
					if (\is_string($type)) {
						$array['types'][] = $type;
					}
				}
			} else {
				$array['types'] = [];
			}
		}
		if (isset($array['repair-steps']['install']['step']) && \is_array($array['repair-steps']['install']['step'])) {
			$array['repair-steps']['install'] = $array['repair-steps']['install']['step'];
		}
		if (isset($array['repair-steps']['pre-migration']['step']) && \is_array($array['repair-steps']['pre-migration']['step'])) {
			$array['repair-steps']['pre-migration'] = $array['repair-steps']['pre-migration']['step'];
		}
		if (isset($array['repair-steps']['post-migration']['step']) && \is_array($array['repair-steps']['post-migration']['step'])) {
			$array['repair-steps']['post-migration'] = $array['repair-steps']['post-migration']['step'];
		}
		if (isset($array['repair-steps']['live-migration']['step']) && \is_array($array['repair-steps']['live-migration']['step'])) {
			$array['repair-steps']['live-migration'] = $array['repair-steps']['live-migration']['step'];
		}
		if (isset($array['repair-steps']['uninstall']['step']) && \is_array($array['repair-steps']['uninstall']['step'])) {
			$array['repair-steps']['uninstall'] = $array['repair-steps']['uninstall']['step'];
		}
		if (isset($array['background-jobs']['job']) && \is_array($array['background-jobs']['job'])) {
			$array['background-jobs'] = $array['background-jobs']['job'];
		}
		if (isset($array['commands']['command']) && \is_array($array['commands']['command'])) {
			$array['commands'] = $array['commands']['command'];
		}
		return $array;
	}

	/**
	 * @param \SimpleXMLElement $xml
	 * @return array|string
	 */
	protected function xmlToArray($xml) {
		if (!$xml->children()) {
			return (string)$xml;
		}

		$array = [];
		foreach ($xml->children() as $element => $node) {
			$totalElement = \count($xml->{$element});

			if (!isset($array[$element])) {
				$array[$element] = $totalElement > 1 ? [] : "";
			}
			/** @var \SimpleXMLElement $node */
			// Has attributes
			if ($attributes = $node->attributes()) {
				$data = [
					'@attributes' => [],
				];
				if (!\count($node->children())) {
					$value = (string)$node;
					if (!empty($value)) {
						$data['@value'] = (string)$node;
					}
				} else {
					$data = \array_merge($data, $this->xmlToArray($node));
				}
				foreach ($attributes as $attr => $value) {
					$data['@attributes'][$attr] = (string)$value;
				}

				if ($totalElement > 1) {
					$array[$element][] = $data;
				} else {
					$array[$element] = $data;
				}
				// Just a value
			} else {
				if ($totalElement > 1) {
					$array[$element][] = $this->xmlToArray($node);
				} else {
					$array[$element] = $this->xmlToArray($node);
				}
			}
		}

		return $array;
	}
}

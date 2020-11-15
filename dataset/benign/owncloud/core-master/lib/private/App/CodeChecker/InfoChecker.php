<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
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

namespace OC\App\CodeChecker;

use OC\App\InfoParser;
use OC\Hooks\BasicEmitter;
use OCP\App\IAppManager;

class InfoChecker extends BasicEmitter {

	/** @var InfoParser */
	private $infoParser;

	/** @var IAppManager */
	private $appManager;

	private $mandatoryFields = [
		'author',
		'description',
		'id',
		'licence',
		'name',
		'version',
	];
	private $optionalFields = [
		'bugs',
		'category',
		'default_enable',
		'dependencies', // TODO: Mandatory as of ownCloud 11
		'documentation',
		'namespace',
		'public',
		'remote',
		'repository',
		'types',
		'website',
	];
	private $deprecatedFields = [
		'info',
		'require',
		'requiremax',
		'requiremin',
		'ocsid',
		'shipped',
		'standalone',
	];

	public function __construct(InfoParser $infoParser, IAppManager $appManager) {
		$this->infoParser = $infoParser;
		$this->appManager = $appManager;
	}

	/**
	 * @param string $appId
	 * @return array
	 */
	public function analyse($appId) {
		$appPath = $this->appManager->getAppPath($appId);
		if ($appPath === false) {
			throw new \RuntimeException("No app with given id <$appId> known.");
		}

		$errors = [];

		try {
			$info = $this->infoParser->parse($appPath . '/appinfo/info.xml');
		} catch (\Exception $e) {
			$this->emit('InfoChecker', 'invalidAppInfo', [$appId]);
			return [
				[
					'type' => 'invalidAppInfo',
					'message' => "App <$appId> has invalid XML in appinfo.xml",
				]
			];
		}

		if (isset($info['dependencies']['owncloud']['@attributes']['min-version']) && ($info['requiremin'] || $info['require'])) {
			$this->emit('InfoChecker', 'duplicateRequirement', ['min']);
			$errors[] = [
				'type' => 'duplicateRequirement',
				'field' => 'min',
			];
		} elseif (!isset($info['dependencies']['owncloud']['@attributes']['min-version'])) {
			$this->emit('InfoChecker', 'missingRequirement', ['min']);
		}

		if (isset($info['dependencies']['owncloud']['@attributes']['max-version']) && $info['requiremax']) {
			$this->emit('InfoChecker', 'duplicateRequirement', ['max']);
			$errors[] = [
				'type' => 'duplicateRequirement',
				'field' => 'max',
			];
		} elseif (!isset($info['dependencies']['owncloud']['@attributes']['max-version'])) {
			$this->emit('InfoChecker', 'missingRequirement', ['max']);
		}

		foreach ($info as $key => $value) {
			if (\is_array($value)) {
				$value = \json_encode($value);
			}
			if (\in_array($key, $this->mandatoryFields)) {
				$this->emit('InfoChecker', 'mandatoryFieldFound', [$key, $value]);
				continue;
			}

			if (\in_array($key, $this->optionalFields)) {
				$this->emit('InfoChecker', 'optionalFieldFound', [$key, $value]);
				continue;
			}

			if (\in_array($key, $this->deprecatedFields)) {
				// skip empty arrays - empty arrays for remote and public are always added
				if ($value === '[]' && \in_array($key, ['public', 'remote', 'info'])) {
					continue;
				}
				$this->emit('InfoChecker', 'deprecatedFieldFound', [$key, $value]);
				continue;
			}

			$this->emit('InfoChecker', 'unusedFieldFound', [$key, $value]);
		}

		foreach ($this->mandatoryFields as $key) {
			if (!isset($info[$key])) {
				$this->emit('InfoChecker', 'mandatoryFieldMissing', [$key]);
				$errors[] = [
					'type' => 'mandatoryFieldMissing',
					'field' => $key,
				];
			}
		}

		$versionFile = $appPath . '/appinfo/version';
		if (\is_file($versionFile)) {
			$version = \trim(\file_get_contents($versionFile));
			if (isset($info['version'])) {
				if ($info['version'] !== $version) {
					$this->emit('InfoChecker', 'differentVersions',
						[$version, $info['version']]);
					$errors[] = [
						'type' => 'differentVersions',
						'message' => 'appinfo/version: ' . $version .
							' - appinfo/info.xml: ' . $info['version'],
					];
				} else {
					$this->emit('InfoChecker', 'sameVersions', [$versionFile]);
				}
			} else {
				$this->emit('InfoChecker', 'migrateVersion', [$version]);
			}
		}

		return $errors;
	}
}

<?php
/**
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OC\Settings\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IConfig;

/**
 * Class LogSettingsController
 *
 * @package OC\Settings\Controller
 */
class LogSettingsController extends Controller {
	/**
	 * @var \OCP\IConfig
	 */
	private $config;

	/**
	 * @var \OCP\IL10N
	 */
	private $l10n;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								IL10N $l10n) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->l10n = $l10n;
	}

	/**
	 * set log level for logger
	 *
	 * @param int $level
	 * @return JSONResponse
	 */
	public function setLogLevel($level) {
		if ($level < 0 || $level > 4) {
			return new JSONResponse([
				'message' => (string) $this->l10n->t('log-level out of allowed range'),
			], Http::STATUS_BAD_REQUEST);
		}

		$this->config->setSystemValue('loglevel', $level);
		return new JSONResponse([
			'level' => $level,
		]);
	}

	/**
	 * download logfile
	 *
	 * @NoCSRFRequired
	 *
	 * @return StreamResponse
	 */
	public function download() {
		$resp = new StreamResponse(\OC\Log\Owncloud::getLogFilePath());
		$resp->addHeader('Content-Type', 'application/octet-stream');
		$resp->addHeader('Content-Disposition', 'attachment; filename="owncloud.log"');
		return $resp;
	}
}

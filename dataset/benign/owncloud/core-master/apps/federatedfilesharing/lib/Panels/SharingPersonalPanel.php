<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
 * @copyright Copyright (c) 2019, ownCloud GmbH
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

namespace OCA\FederatedFileSharing\Panels;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\Settings\ISettings;
use OCP\Template;

class SharingPersonalPanel implements ISettings {

	/** @var IConfig $config */
	private $config;

	/** @var IUserSession $userSession */
	private $userSession;

	public function __construct(IConfig $config, IUserSession $userSession) {
		$this->config = $config;
		$this->userSession = $userSession;
	}

	/**
	 * The panel controller method that returns a template to the UI
	 *
	 * @return TemplateResponse | Template
	 */
	public function getPanel() {
		$tmpl = new Template('federatedfilesharing', 'settings-personal-sharing');
		$showEmptyTemplate = true;
		$globalAutoAcceptShareEnabled  = $this->config->getAppValue(
			'federatedfilesharing',
			'auto_accept_trusted',
			'no'
		);
		$autoAcceptShareEnabled = $this->config->getUserValue(
			$this->userSession->getUser()->getUID(),
			'federatedfilesharing',
			'auto_accept_share_trusted',
			$globalAutoAcceptShareEnabled
		);
		if ($globalAutoAcceptShareEnabled === 'yes') {
			$showEmptyTemplate = false;
			$tmpl->assign(
				'userAutoAcceptShareTrustedEnabled',
				$autoAcceptShareEnabled
			);
		}
		if ($showEmptyTemplate) {
			return new Template('federatedfilesharing', 'settings-personal-sharing-empty');
		}
		return $tmpl;
	}

	/**
	 * A string to identify the section in the UI / HTML and URL
	 *s
	 * @return string
	 */
	public function getSectionID() {
		return 'sharing';
	}

	/**
	 * The number used to order the section in the UI.
	 *
	 * @return int between 0 and 100, with 100 being the highest priority
	 */
	public function getPriority() {
		return 40;
	}
}

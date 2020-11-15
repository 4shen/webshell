<?php
/**
 * @copyright Copyright (c) 2019 Robin Appelman <robin@icewind.nl>
 *
 * @author Robin Appelman <robin@icewind.nl>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Theming\AppInfo;

use OCA\Theming\Service\JSDataService;
use OCP\AppFramework\IAppContainer;
use OCP\IInitialStateService;

class Application extends \OCP\AppFramework\App {
	public const APP_ID = 'theming';

	public function __construct() {
		parent::__construct(self::APP_ID);

		$container = $this->getContainer();
		$this->registerInitialState($container);
	}

	private function registerInitialState(IAppContainer $container) {
		/** @var IInitialStateService $initialState */
		$initialState = $container->query(IInitialStateService::class);

		$initialState->provideLazyInitialState(self::APP_ID, 'data', function () use ($container) {
			/** @var JSDataService $data */
			$data = $container->query(JSDataService::class);
			return $data;
		});
	}
}

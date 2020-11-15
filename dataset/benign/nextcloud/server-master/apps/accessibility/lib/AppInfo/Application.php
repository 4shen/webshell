<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2018 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author Alexey Pyltsyn <lex61rus@gmail.com>
 * @author Janis Köhr <janis.koehr@novatec-gmbh.de>
 * @author John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>
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

namespace OCA\Accessibility\AppInfo;

use OCA\Accessibility\Service\JSDataService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\IAppContainer;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\IURLGenerator;
use OCP\IUserSession;
use function count;
use function implode;
use function md5;

class Application extends App implements IBootstrap {

	/** @var string */
	public const APP_ID = 'accessibility';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
	}

	public function boot(IBootContext $context): void {
		$this->injectCss(
			$context->getAppContainer()->query(IUserSession::class),
			$context->getAppContainer()->query(IConfig::class),
			$context->getAppContainer()->query(IURLGenerator::class)
		);

		$this->registerInitialState($context->getAppContainer());
	}

	private function injectCss(IUserSession $userSession,
							   IConfig $config,
							   IURLGenerator $urlGenerator) {
		// Inject the fake css on all pages if enabled and user is logged
		$loggedUser = $userSession->getUser();
		if ($loggedUser !== null) {
			$userValues = $config->getUserKeys($loggedUser->getUID(), self::APP_ID);
			// we want to check if any theme or font is enabled.
			if (count($userValues) > 0) {
				$hash = $config->getUserValue($loggedUser->getUID(), self::APP_ID, 'icons-css', md5(implode('-', $userValues)));
				$linkToCSS = $urlGenerator->linkToRoute(self::APP_ID . '.accessibility.getCss', ['md5' => $hash]);
				\OCP\Util::addHeader('link', ['rel' => 'stylesheet', 'href' => $linkToCSS]);
			}
		} else {
			$userValues = ['dark'];

			$hash = md5(implode('-', $userValues));
			$linkToCSS = $urlGenerator->linkToRoute(self::APP_ID . '.accessibility.getCss', ['md5' => $hash]);
			\OCP\Util::addHeader('link', ['rel' => 'stylesheet', 'media' => '(prefers-color-scheme: dark)', 'href' => $linkToCSS]);
		}
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

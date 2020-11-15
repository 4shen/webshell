<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

// WARNING: this should be moved to proper AppFramework handling
// Check if we are a user
if (!\OC::$server->getUserSession()->isLoggedIn()) {
	header('Location: ' . \OC::$server->getURLGenerator()->linkToRoute(
			'core.login.showLoginForm',
			[
				'redirect_url' => \OC::$server->getRequest()->getRequestUri(),
			]
		)
	);
	exit();
}
// Redirect to 2FA challenge selection if 2FA challenge was not solved yet
if (\OC::$server->getTwoFactorAuthManager()->needsSecondFactor(\OC::$server->getUserSession()->getUser())) {
	header('Location: ' . \OC::$server->getURLGenerator()->linkToRoute('core.TwoFactorChallenge.selectChallenge'));
	exit();
}

$tmpl = new OCP\Template('systemtags', 'list', '');
$tmpl->printPage();

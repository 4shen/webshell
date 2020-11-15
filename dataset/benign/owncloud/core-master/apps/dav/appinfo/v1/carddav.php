<?php
/**
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
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

// Backends
use OCA\DAV\CardDAV\AddressBookRoot;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\Connector\LegacyDAVACL;
use OCA\DAV\Connector\Sabre\Auth;
use OCA\DAV\Connector\Sabre\ExceptionLoggerPlugin;
use OCA\DAV\Connector\Sabre\MaintenancePlugin;
use OCA\DAV\Connector\Sabre\Principal;
use Sabre\CardDAV\Plugin;

$authBackend = new Auth(
	\OC::$server->getSession(),
	\OC::$server->getUserSession(),
	\OC::$server->getRequest(),
	\OC::$server->getTwoFactorAuthManager(),
	\OC::$server->getAccountModuleManager(),
	'principals/'
);
$principalBackend = new Principal(
	\OC::$server->getUserManager(),
	\OC::$server->getGroupManager(),
	'principals/'
);
$groupPrincipalBackend = new \OCA\DAV\DAV\GroupPrincipalBackend(
	\OC::$server->getGroupManager()
);
$db = \OC::$server->getDatabaseConnection();
$cardDavBackend = new CardDavBackend($db, $principalBackend, $groupPrincipalBackend, null, true);

$debugging = \OC::$server->getConfig()->getSystemValue('debug', false);

// Root nodes
$principalCollection = new \Sabre\CalDAV\Principal\Collection($principalBackend);
$principalCollection->disableListing = !$debugging; // Disable listing

$addressBookRoot = new AddressBookRoot($principalBackend, $cardDavBackend);
$addressBookRoot->disableListing = !$debugging; // Disable listing

$nodes = [
	$principalCollection,
	$addressBookRoot,
];

// Fire up server
$server = new \Sabre\DAV\Server($nodes);
$server->httpRequest->setUrl(\OC::$server->getRequest()->getRequestUri());
$server->setBaseUri($baseuri);
// Add plugins
$server->addPlugin(new MaintenancePlugin());
$server->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend));
$server->addPlugin(new Plugin());

$server->addPlugin(new LegacyDAVACL());
if ($debugging) {
	$server->addPlugin(new Sabre\DAV\Browser\Plugin());
}

$server->addPlugin(new Sabre\DAV\Sync\Plugin());
$server->addPlugin(new \Sabre\CardDAV\VCFExportPlugin());
$server->addPlugin(new \OCA\DAV\CardDAV\ImageExportPlugin(\OC::$server->getLogger()));
$server->addPlugin(new ExceptionLoggerPlugin('carddav', \OC::$server->getLogger()));

// And off we go!
$server->exec();

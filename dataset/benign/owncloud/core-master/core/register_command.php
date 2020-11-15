<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Christian Kampka <christian@kampka.net>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
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

/** @var Symfony\Component\Console\Application $application */
'@phan-var \Symfony\Component\Console\Application $application';
$application->add(new OC\Core\Command\Status);
$application->add(new OC\Core\Command\Check(\OC::$server->getConfig()));
$application->add(new OC\Core\Command\L10n\CreateJs());
$application->add(new OC\Core\Command\App\CheckCode(
	new \OC\App\InfoParser(),
	\OC::$server->getAppManager()
));
$application->add(new \OC\Core\Command\Integrity\SignApp(
		\OC::$server->getIntegrityCodeChecker(),
		new \OC\IntegrityCheck\Helpers\FileAccessHelper(),
		\OC::$server->getURLGenerator()
));
$application->add(new \OC\Core\Command\Integrity\SignCore(
		\OC::$server->getIntegrityCodeChecker(),
		new \OC\IntegrityCheck\Helpers\FileAccessHelper()
));
$application->add(new \OC\Core\Command\Integrity\CheckApp(
		\OC::$server->getIntegrityCodeChecker()
));
$application->add(new \OC\Core\Command\Integrity\CheckCore(
		\OC::$server->getIntegrityCodeChecker()
));

if (\OC::$server->getConfig()->getSystemValue('installed', false)) {
	$application->add(new OC\Core\Command\App\Disable(\OC::$server->getAppManager()));
	$application->add(new OC\Core\Command\App\Enable(\OC::$server->getAppManager()));
	$application->add(new OC\Core\Command\App\GetPath());
	$application->add(new OC\Core\Command\App\ListApps(\OC::$server->getAppManager()));
	
	$application->add(new OC\Core\Command\TwoFactorAuth\Enable(
		\OC::$server->getTwoFactorAuthManager(), \OC::$server->getUserManager()
	));
	$application->add(new OC\Core\Command\TwoFactorAuth\Disable(
		\OC::$server->getTwoFactorAuthManager(), \OC::$server->getUserManager()
	));

	$application->add(new OC\Core\Command\Background\Cron(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Background\WebCron(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Background\Ajax(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Background\Queue\Status(\OC::$server->getJobList()));
	$application->add(new OC\Core\Command\Background\Queue\Delete(\OC::$server->getJobList()));
	$application->add(new OC\Core\Command\Background\Queue\Execute(\OC::$server->getJobList(), \OC::$server->getTimeFactory()));

	$application->add(new OC\Core\Command\Config\App\DeleteConfig(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Config\App\GetConfig(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Config\App\SetConfig(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Config\Import(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Config\ListConfigs(\OC::$server->getSystemConfig(), \OC::$server->getAppConfig()));
	$application->add(new OC\Core\Command\Config\System\DeleteConfig(\OC::$server->getSystemConfig()));
	$application->add(new OC\Core\Command\Config\System\GetConfig(\OC::$server->getSystemConfig()));
	$application->add(new OC\Core\Command\Config\System\SetConfig(\OC::$server->getSystemConfig()));
	$application->add(
		new OC\Core\Command\Db\ConvertType(
			\OC::$server->getConfig(),
			new \OC\DB\ConnectionFactory(\OC::$server->getSystemConfig()),
			\OC::$server->getAppManager()
		)
	);
	$application->add(new OC\Core\Command\Db\ConvertMysqlToMB4(\OC::$server->getConfig(), \OC::$server->getDatabaseConnection(), \OC::$server->getURLGenerator()));
	$application->add(new OC\Core\Command\Db\Migrations\StatusCommand(\OC::$server->getDatabaseConnection()));
	$application->add(new OC\Core\Command\Db\Migrations\MigrateCommand(\OC::$server->getDatabaseConnection()));
	$application->add(new OC\Core\Command\Db\Migrations\GenerateCommand(\OC::$server->getDatabaseConnection()));
	$application->add(new OC\Core\Command\Db\Migrations\ExecuteCommand(\OC::$server->getDatabaseConnection()));

	$application->add(new OC\Core\Command\Encryption\Disable(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Encryption\Enable(\OC::$server->getConfig(), \OC::$server->getEncryptionManager()));
	$application->add(new OC\Core\Command\Encryption\ListModules(\OC::$server->getEncryptionManager()));
	$application->add(new OC\Core\Command\Encryption\SetDefaultModule(\OC::$server->getEncryptionManager()));
	$application->add(new OC\Core\Command\Encryption\Status(\OC::$server->getEncryptionManager()));
	$application->add(new OC\Core\Command\Encryption\EncryptAll(\OC::$server->getEncryptionManager(), \OC::$server->getAppManager(), \OC::$server->getConfig(), new \Symfony\Component\Console\Helper\QuestionHelper()));
	$application->add(new OC\Core\Command\Encryption\DecryptAll(
		\OC::$server->getEncryptionManager(),
		\OC::$server->getAppManager(),
		\OC::$server->getConfig(),
		new \OC\Encryption\DecryptAll(\OC::$server->getEncryptionManager(), \OC::$server->getUserManager(), new \OC\Files\View(), \OC::$server->getLogger()),
		new \Symfony\Component\Console\Helper\QuestionHelper())
	);

	$application->add(new OC\Core\Command\Log\Manage(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Log\OwnCloud(\OC::$server->getConfig()));

	$view = new \OC\Files\View();
	$util = new \OC\Encryption\Util(
		$view,
		\OC::$server->getUserManager(),
		\OC::$server->getGroupManager(),
		\OC::$server->getConfig()
	);
	$application->add(new OC\Core\Command\Encryption\ChangeKeyStorageRoot(
			$view,
			\OC::$server->getUserManager(),
			\OC::$server->getConfig(),
			$util,
			new \Symfony\Component\Console\Helper\QuestionHelper()
		)
	);
	$application->add(new OC\Core\Command\Encryption\ShowKeyStorageRoot($util));

	$application->add(new OC\Core\Command\Maintenance\DataFingerprint(\OC::$server->getConfig(), new \OC\AppFramework\Utility\TimeFactory(), \OC::$server->getLogger()));
	$application->add(new OC\Core\Command\Maintenance\Mimetype\UpdateDB(\OC::$server->getMimeTypeDetector(), \OC::$server->getMimeTypeLoader()));
	$application->add(new OC\Core\Command\Maintenance\Mimetype\UpdateJS(\OC::$server->getMimeTypeDetector()));
	$application->add(new OC\Core\Command\Maintenance\Mode(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Maintenance\SingleUser(\OC::$server->getConfig()));
	$application->add(new OC\Core\Command\Maintenance\UpdateHtaccess());

	$application->add(new OC\Core\Command\Upgrade(\OC::$server->getConfig(), \OC::$server->getLogger()));
	$application->add(new OC\Core\Command\Maintenance\Repair(
		new \OC\Repair(\OC\Repair::getRepairSteps(), \OC::$server->getEventDispatcher()),
		\OC::$server->getConfig(),
		\OC::$server->getEventDispatcher(),
		\OC::$server->getAppManager())
	);

	$application->add(new OC\Core\Command\User\Add(\OC::$server->getUserManager(), \OC::$server->getGroupManager(), \OC::$server->getMailer()));
	$application->add(new OC\Core\Command\User\Delete(\OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\User\Disable(\OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\User\Enable(\OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\User\Inactive(\OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\User\LastSeen(\OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\User\ListUsers(\OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\User\ListUserGroups(\OC::$server->getUserManager(), \OC::$server->getGroupManager()));
	$application->add(new OC\Core\Command\User\Report(\OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\User\ResetPassword(\OC::$server->getUserManager(), \OC::$server->getConfig(), \OC::$server->getTimeFactory(),
		new \OC\Helper\EnvironmentHelper(), new \OC\Core\Controller\LostController('settings',
			\OC::$server->getRequest(), \OC::$server->getURLGenerator(), \OC::$server->getUserManager(), new \OC_Defaults(),
			\OC::$server->getL10N('settings'), \OC::$server->getConfig(), \OC::$server->getSecureRandom(),
			\OCP\Util::getDefaultEmailAddress('lostpassword-noreply'), \OC::$server->getEncryptionManager()->isEnabled(),
			\OC::$server->getMailer(), \OC::$server->getTimeFactory(), \OC::$server->getLogger(), \OC::$server->getUserSession())));
	$application->add(new OC\Core\Command\User\Setting(\OC::$server->getUserManager(), \OC::$server->getConfig(), \OC::$server->getDatabaseConnection()));
	$application->add(new OC\Core\Command\User\Modify(\OC::$server->getUserManager(), \OC::$server->getMailer()));
	$application->add(new OC\Core\Command\User\SyncBackend(\OC::$server->getAccountMapper(), \OC::$server->getConfig(), \OC::$server->getUserManager(), \OC::$server->getLogger()));

	$application->add(new OC\Core\Command\Group\Add(\OC::$server->getGroupManager()));
	$application->add(new OC\Core\Command\Group\Delete(\OC::$server->getGroupManager()));
	$application->add(new OC\Core\Command\Group\AddMember(\OC::$server->getGroupManager(), \OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\Group\RemoveMember(\OC::$server->getGroupManager(), \OC::$server->getUserManager()));
	$application->add(new OC\Core\Command\Group\ListGroups(\OC::$server->getGroupManager()));
	$application->add(new OC\Core\Command\Group\ListGroupMembers(\OC::$server->getGroupManager()));

	$application->add(new OC\Core\Command\Security\ListCertificates(\OC::$server->getCertificateManager(null), \OC::$server->getL10N('core')));
	$application->add(new OC\Core\Command\Security\ImportCertificate(\OC::$server->getCertificateManager(null)));
	$application->add(new OC\Core\Command\Security\RemoveCertificate(\OC::$server->getCertificateManager(null)));
	$application->add(new OC\Core\Command\Security\ListRoutes(\OC::$server->getRouter()));
	$application->add(new OC\Core\Command\System\Cron(\OC::$server->getJobList(), \OC::$server->getConfig(), \OC::$server->getLogger(), \OC::$server->getTempManager()));
} else {
	$application->add(new OC\Core\Command\Maintenance\Install(\OC::$server->getConfig()));
}

<?php
/**
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Piotr Filiciak <piotr@filiciak.pl>
 * @author Robin Appelman <icewind@owncloud.com>
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

namespace OCA\Files_Sharing\Controllers;

use OC;
use OC_Files;
use OC_Util;
use OCA\Files_Sharing\Activity;
use OCP;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IPreview;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\Share;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Template;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class ShareController
 *
 * @package OCA\Files_Sharing\Controllers
 */
class ShareController extends Controller {

	/** @var IConfig */
	protected $config;
	/** @var IURLGenerator */
	protected $urlGenerator;
	/** @var IUserManager */
	protected $userManager;
	/** @var ILogger */
	protected $logger;
	/** @var OCP\Activity\IManager */
	protected $activityManager;
	/** @var OCP\Share\IManager */
	protected $shareManager;
	/** @var ISession */
	protected $session;
	/** @var IPreview */
	protected $previewManager;
	/** @var IRootFolder */
	protected $rootFolder;
	/** @var EventDispatcher */
	protected $eventDispatcher;

	/**
	 * ShareController constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param IURLGenerator $urlGenerator
	 * @param IUserManager $userManager
	 * @param ILogger $logger
	 * @param OCP\Activity\IManager $activityManager
	 * @param Share\IManager $shareManager
	 * @param ISession $session
	 * @param IPreview $previewManager
	 * @param IRootFolder $rootFolder
	 * @param EventDispatcher $eventDispatcher
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								IURLGenerator $urlGenerator,
								IUserManager $userManager,
								ILogger $logger,
								\OCP\Activity\IManager $activityManager,
								\OCP\Share\IManager $shareManager,
								ISession $session,
								IPreview $previewManager,
								IRootFolder $rootFolder,
								EventDispatcher $eventDispatcher) {
		parent::__construct($appName, $request);

		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
		$this->userManager = $userManager;
		$this->logger = $logger;
		$this->activityManager = $activityManager;
		$this->shareManager = $shareManager;
		$this->session = $session;
		$this->previewManager = $previewManager;
		$this->rootFolder = $rootFolder;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $token
	 * @return TemplateResponse|RedirectResponse
	 */
	public function showAuthenticate($token) {
		$share = $this->shareManager->getShareByToken($token);

		if ($this->linkShareAuth($share)) {
			return new RedirectResponse($this->urlGenerator->linkToRoute('files_sharing.sharecontroller.showShare', ['token' => $token]));
		}

		return new TemplateResponse($this->appName, 'authenticate', [], 'guest');
	}

	/**
	 * @PublicPage
	 * @UseSession
	 *
	 * Authenticates against password-protected shares
	 * @param string $token
	 * @param string $password
	 * @return RedirectResponse|TemplateResponse
	 */
	public function authenticate($token, $password = '') {

		// Check whether share exists
		try {
			$share = $this->shareManager->getShareByToken($token);
		} catch (ShareNotFound $e) {
			return new NotFoundResponse();
		}

		$authenticate = $this->linkShareAuth($share, $password);

		if ($authenticate === true) {
			return new RedirectResponse($this->urlGenerator->linkToRoute('files_sharing.sharecontroller.showShare', ['token' => $token]));
		}

		return new TemplateResponse($this->appName, 'authenticate', ['wrongpw' => true], 'guest');
	}

	/**
	 * Authenticate a link item with the given password.
	 * Or use the session if no password is provided.
	 *
	 * This is a modified version of Helper::authenticate
	 * TODO: Try to merge back eventually with Helper::authenticate
	 *
	 * @param \OCP\Share\IShare $share
	 * @param string|null $password
	 * @return bool
	 */
	private function linkShareAuth(\OCP\Share\IShare $share, $password = null) {
		$beforeEvent = new GenericEvent(null, ['shareObject' => $share]);
		$this->eventDispatcher->dispatch('share.beforelinkauth', $beforeEvent);
		if ($password !== null) {
			if ($this->shareManager->checkPassword($share, $password)) {
				$this->session->set('public_link_authenticated', (string)$share->getId());
			} else {
				$this->emitAccessShareHook($share, 403, 'Wrong password');
				return false;
			}
		} else {
			// not authenticated ?
			if (! $this->session->exists('public_link_authenticated')
				|| $this->session->get('public_link_authenticated') !== (string)$share->getId()) {
				return false;
			}
		}
		$afterEvent = new GenericEvent(null, ['shareObject' => $share]);
		$this->eventDispatcher->dispatch('share.afterlinkauth', $afterEvent);
		return true;
	}

	/**
	 * throws hooks when a share is attempted to be accessed
	 *
	 * @param \OCP\Share\IShare|string $share the Share instance if available,
	 * otherwise token
	 * @param int $errorCode
	 * @param string $errorMessage
	 * @throws OC\HintException
	 * @throws OC\ServerNotAvailableException
	 */
	protected function emitAccessShareHook($share, $errorCode = 200, $errorMessage = '') {
		$itemType = $itemSource = $uidOwner = '';
		$token = $share;
		$exception = null;
		if ($share instanceof \OCP\Share\IShare) {
			try {
				$token = $share->getToken();
				$uidOwner = $share->getSharedBy();
				$itemType = $share->getNodeType();
				$itemSource = $share->getNodeId();
			} catch (\Exception $e) {
				// we log what we know and pass on the exception afterwards
				$exception = $e;
			}
		}
		\OC_Hook::emit('OCP\Share', 'share_link_access', [
			'itemType' => $itemType,
			'itemSource' => $itemSource,
			'uidOwner' => $uidOwner,
			'token' => $token,
			'errorCode' => $errorCode,
			'errorMessage' => $errorMessage,
		]);

		if ($share instanceof \OCP\Share\IShare) {
			$cloneShare = clone $share;
			$publicShareLinkAccessEvent = new GenericEvent(null,
				['shareObject' => $cloneShare, 'errorCode' => $errorCode,
					'errorMessage' => $errorMessage]);
			$this->eventDispatcher->dispatch('share.linkaccess', $publicShareLinkAccessEvent);
		}

		if ($exception !== null) {
			throw $exception;
		}
	}

	/**
	 * Validate the permissions of the share
	 *
	 * @param Share\IShare $share
	 * @return bool
	 */
	private function validateShare(\OCP\Share\IShare $share) {
		return $share->getNode()->isReadable() && $share->getNode()->isShareable();
	}

	/**
	 * Renders and displays the public link page template
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $token
	 * @param string $path
	 * @return NotFoundResponse|RedirectResponse|TemplateResponse
	 * @throws NotFoundException
	 */
	public function showShare($token, $path = '') {
		\OC_User::setIncognitoMode(true);

		// Check whether share exists
		try {
			$share = $this->shareManager->getShareByToken($token);
		} catch (ShareNotFound $e) {
			$this->emitAccessShareHook($token, 404, 'Share not found');
			return new NotFoundResponse();
		}

		// Share is password protected - check whether the user is permitted to access the share
		if ($share->getPassword() !== null && !$this->linkShareAuth($share)) {
			return new RedirectResponse($this->urlGenerator->linkToRoute('files_sharing.sharecontroller.authenticate',
				['token' => $token]));
		}

		if (!$this->validateShare($share)) {
			throw new NotFoundException();
		}
		// We can't get the path of a file share
		try {
			if ($share->getNode() instanceof \OCP\Files\File && $path !== '') {
				$this->emitAccessShareHook($share, 404, 'Share not found');
				throw new NotFoundException();
			}
		} catch (\Exception $e) {
			$this->emitAccessShareHook($share, 404, 'Share not found');
			throw $e;
		}

		$rootFolder = null;
		if ($share->getNode() instanceof \OCP\Files\Folder) {
			/** @var \OCP\Files\Folder $rootFolder */
			$rootFolder = $share->getNode();

			try {
				$path = $rootFolder->get($path);
			} catch (\OCP\Files\NotFoundException $e) {
				$this->emitAccessShareHook($share, 404, 'Share not found');
				throw new NotFoundException();
			}
		}

		$shareTmpl = [];
		$shareTmpl['displayName'] = $this->userManager->get($share->getShareOwner())->getDisplayName();
		$shareTmpl['owner'] = $share->getShareOwner();
		$shareTmpl['filename'] = $share->getNode()->getName();
		$shareTmpl['directory_path'] = $share->getTarget();
		$shareTmpl['mimetype'] = $share->getNode()->getMimetype();
		$shareTmpl['previewSupported'] = $this->previewManager->isMimeSupported($share->getNode()->getMimetype());
		$shareTmpl['dirToken'] = $token;
		$shareTmpl['sharingToken'] = $token;
		$shareTmpl['protected'] = $share->getPassword() !== null ? 'true' : 'false';
		$shareTmpl['dir'] = '';
		$shareTmpl['nonHumanFileSize'] = $share->getNode()->getSize();
		$shareTmpl['fileSize'] = \OCP\Util::humanFileSize($share->getNode()->getSize());

		// Show file list
		if ($share->getNode() instanceof \OCP\Files\Folder) {
			$shareTmpl['dir'] = $rootFolder->getRelativePath($path->getPath());

			/*
			 * The OC_Util methods require a view. This just uses the node API
			 */
			$freeSpace = $share->getNode()->getStorage()->free_space($share->getNode()->getInternalPath());
			if ($freeSpace < \OCP\Files\FileInfo::SPACE_UNLIMITED) {
				$freeSpace = \max($freeSpace, 0);
			} else {
				$freeSpace = (INF > 0) ? INF: PHP_INT_MAX; // work around https://bugs.php.net/bug.php?id=69188
			}

			$maxUploadFilesize = $freeSpace;

			if ($share->getPermissions() & \OCP\Constants::PERMISSION_READ > 0) {
				$folder = new Template('files', 'list', '');
				$folder->assign('dir', $rootFolder->getRelativePath($path->getPath()));
				$folder->assign('dirToken', $token);
				$folder->assign('permissions', \OCP\Constants::PERMISSION_READ);
				$folder->assign('isPublic', true);
				$folder->assign('publicUploadEnabled', 'no');
				$folder->assign('uploadMaxFilesize', $maxUploadFilesize);
				$folder->assign('uploadMaxHumanFilesize', OCP\Util::humanFileSize($maxUploadFilesize));
				$folder->assign('freeSpace', $freeSpace);
				$folder->assign('usedSpacePercent', 0);
				$folder->assign('trash', false);
				$shareTmpl['folder'] = $folder->fetchPage();
			} else {
				$folder = new Template('files_sharing', 'upload', '');
				$shareTmpl['folder'] = $folder->fetchPage();
			}
		}

		$shareTmpl['canDownload'] = !!($share->getPermissions() & \OCP\Constants::PERMISSION_READ > 0);
		$shareTmpl['sharePermission'] = $share->getPermissions();
		$shareTmpl['downloadURL'] = $this->urlGenerator->linkToRouteAbsolute('files_sharing.sharecontroller.downloadShare', ['token' => $token]);
		$shareTmpl['shareUrl'] = $this->urlGenerator->linkToRouteAbsolute('files_sharing.sharecontroller.showShare', ['token' => $token]);
		$shareTmpl['maxSizeAnimateGif'] = $this->config->getSystemValue('max_filesize_animated_gifs_public_sharing', 10);
		$shareTmpl['previewEnabled'] = $this->config->getSystemValue('enable_previews', true);
		$shareTmpl['previewMaxX'] = $this->config->getSystemValue('preview_max_x', 1024);
		$shareTmpl['previewMaxY'] = $this->config->getSystemValue('preview_max_y', 1024);
		if ($shareTmpl['previewSupported']) {
			$shareTmpl['previewImage'] = $this->urlGenerator->linkToRouteAbsolute('core_ajax_public_preview',
				['x' => 200, 'y' => 200, 'file' => $shareTmpl['directory_path'], 't' => $shareTmpl['dirToken']]);
		} else {
			$shareTmpl['previewImage'] = $this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('core', 'favicon-fb.png'));
		}

		$this->eventDispatcher->dispatch('OCA\Files_Sharing::loadAdditionalScripts');

		$csp = new OCP\AppFramework\Http\ContentSecurityPolicy();
		$csp->addAllowedFrameDomain('\'self\'');
		$response = new TemplateResponse($this->appName, 'public', $shareTmpl, 'base');
		$response->setContentSecurityPolicy($csp);

		$this->emitAccessShareHook($share);

		return $response;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $token
	 * @param string $files
	 * @param string $path
	 * @param string $downloadStartSecret
	 * @return NotFoundResponse|RedirectResponse|void
	 */
	public function downloadShare($token, $files = null, $path = '', $downloadStartSecret = '') {
		\OC_User::setIncognitoMode(true);

		$share = $this->shareManager->getShareByToken($token);

		// Share is password protected - check whether the user is permitted to access the share
		if ($share->getPassword() !== null && !$this->linkShareAuth($share)) {
			return new RedirectResponse($this->urlGenerator->linkToRoute('files_sharing.sharecontroller.authenticate',
				['token' => $token]));
		}

		if (($share->getPermissions() & \OCP\Constants::PERMISSION_READ) === 0) {
			throw new NotFoundException();
		}

		$files_list = $files;
		if ($files !== null) { // download selected files
			// in case we get only a single file
			if (!\is_array($files_list)) {
				$files_list = [(string)$files_list];
			}
		}

		$userFolder = $this->rootFolder->getUserFolder($share->getShareOwner());
		$originalSharePath = $userFolder->getRelativePath($share->getNode()->getPath());

		if (!$this->validateShare($share)) {
			throw new NotFoundException();
		}

		// Single file share
		if ($share->getNode() instanceof \OCP\Files\File) {
			// Single file download
			$event = $this->activityManager->generateEvent();
			$event->setApp('files_sharing')
				->setType(Activity::TYPE_PUBLIC_LINKS)
				->setSubject(Activity::SUBJECT_PUBLIC_SHARED_FILE_DOWNLOADED, [$userFolder->getRelativePath($share->getNode()->getPath())])
				->setAffectedUser($share->getShareOwner())
				->setObject('files', $share->getNode()->getId(), $userFolder->getRelativePath($share->getNode()->getPath()));
			$this->activityManager->publish($event);
		}
		// Directory share
		else {
			/** @var \OCP\Files\Folder $node */
			$node = $share->getNode();

			// Try to get the path
			if ($path !== '') {
				try {
					$node = $node->get($path);
				} catch (NotFoundException $e) {
					$this->emitAccessShareHook($share, 404, 'Share not found');
					return new NotFoundResponse();
				}
			}

			$originalSharePath = $userFolder->getRelativePath($node->getPath());

			if ($node instanceof \OCP\Files\File) {
				// Single file download
				$event = $this->activityManager->generateEvent();
				$event->setApp('files_sharing')
					->setType(Activity::TYPE_PUBLIC_LINKS)
					->setSubject(Activity::SUBJECT_PUBLIC_SHARED_FILE_DOWNLOADED, [$userFolder->getRelativePath($node->getPath())])
					->setAffectedUser($share->getShareOwner())
					->setObject('files', $node->getId(), $userFolder->getRelativePath($node->getPath()));
				$this->activityManager->publish($event);
			} elseif (!empty($files_list)) {
				/** @var \OCP\Files\Folder $node */

				// Subset of files is downloaded
				foreach ($files_list as $file) {
					$subNode = $node->get($file);

					$event = $this->activityManager->generateEvent();
					$event->setApp('files_sharing')
						->setType(Activity::TYPE_PUBLIC_LINKS)
						->setAffectedUser($share->getShareOwner())
						->setObject('files', $subNode->getId(), $userFolder->getRelativePath($subNode->getPath()));

					if ($subNode instanceof \OCP\Files\File) {
						$event->setSubject(Activity::SUBJECT_PUBLIC_SHARED_FILE_DOWNLOADED, [$userFolder->getRelativePath($subNode->getPath())]);
					} else {
						$event->setSubject(Activity::SUBJECT_PUBLIC_SHARED_FOLDER_DOWNLOADED, [$userFolder->getRelativePath($subNode->getPath())]);
					}

					$this->activityManager->publish($event);
				}
			} else {
				// The folder is downloaded
				$event = $this->activityManager->generateEvent();
				$event->setApp('files_sharing')
					->setType(Activity::TYPE_PUBLIC_LINKS)
					->setSubject(Activity::SUBJECT_PUBLIC_SHARED_FOLDER_DOWNLOADED, [$userFolder->getRelativePath($node->getPath())])
					->setAffectedUser($share->getShareOwner())
					->setObject('files', $node->getId(), $userFolder->getRelativePath($node->getPath()));
				$this->activityManager->publish($event);
			}
		}

		/* FIXME: We should do this all nicely in OCP */
		OC_Util::tearDownFS();
		OC_Util::setupFS($share->getShareOwner());

		/**
		 * this sets a cookie to be able to recognize the start of the download
		 * the content must not be longer than 32 characters and must only contain
		 * alphanumeric characters
		 */
		if (!empty($downloadStartSecret)
			&& !isset($downloadStartSecret[32])
			&& \preg_match('!^[a-zA-Z0-9]+$!', $downloadStartSecret) === 1) {

			// FIXME: set on the response once we use an actual app framework response
			\setcookie('ocDownloadStarted', $downloadStartSecret, \time() + 20, '/');
		}

		$this->emitAccessShareHook($share);

		$server_params = ['head' => $this->request->getMethod() == 'HEAD'];

		/**
		 * Http range requests support
		 */
		if (isset($_SERVER['HTTP_RANGE'])) {
			$server_params['range'] = $this->request->getHeader('Range');
		}

		// download selected files
		if ($files !== null && $files !== '') {
			// FIXME: The exit is required here because otherwise the AppFramework is trying to add headers as well
			// after dispatching the request which results in a "Cannot modify header information" notice.
			OC_Files::get($originalSharePath, $files_list, $server_params);
			exit();
		} else {
			// FIXME: The exit is required here because otherwise the AppFramework is trying to add headers as well
			// after dispatching the request which results in a "Cannot modify header information" notice.
			OC_Files::get(\dirname($originalSharePath), \basename($originalSharePath), $server_params);
			exit();
		}
	}
}

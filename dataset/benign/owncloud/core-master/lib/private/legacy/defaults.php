<?php

use OCP\IConfig;

/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Jan-Christoph Borchardt <hey@jancborchardt.net>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Pascal de Bruijn <pmjdebruijn@pcode.nl>
 * @author Philipp Schaffrath <github@philippschaffrath.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author scolebrook <scolebrook@mac.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Volkan Gezer <volkangezer@gmail.com>
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
class OC_Defaults {
	private $theme;
	private $l;

	private $defaultEntity;
	private $defaultName;
	private $defaultTitle;
	private $defaultBaseUrl;
	private $defaultSyncClientUrl;
	private $defaultiOSClientUrl;
	private $defaultiTunesAppId;
	private $defaultAndroidClientUrl;
	private $defaultDocBaseUrl;
	private $defaultDocVersion;
	private $defaultSlogan;
	private $defaultLogoClaim;
	private $defaultMailHeaderColor;
	/**
	 * @var IConfig
	 */
	private $config;

	public function __construct() {
		$this->l = \OC::$server->getL10N('lib');
		$this->config = \OC::$server->getConfig();
		$version = \OCP\Util::getVersion();

		$this->defaultEntity = 'ownCloud'; /* e.g. company name, used for footers and copyright notices */
		$this->defaultName = 'ownCloud'; /* short name, used when referring to the software */
		$this->defaultTitle = 'ownCloud'; /* can be a longer name, for titles */
		$this->defaultBaseUrl = 'https://owncloud.org';
		$this->defaultSyncClientUrl = 'https://owncloud.org/install/#install-clients';
		$this->defaultiOSClientUrl = 'https://apps.apple.com/app/id1359583808';
		$this->defaultiTunesAppId = '1359583808';
		$this->defaultAndroidClientUrl = 'https://play.google.com/store/apps/details?id=com.owncloud.android';
		$this->defaultDocBaseUrl = 'https://doc.owncloud.org';
		$this->defaultDocVersion = $version[0] . '.' . $version[1]; // used to generate doc links
		$this->defaultSlogan = $this->l->t('A safe home for all your data');
		$this->defaultLogoClaim = '';
		$this->defaultMailHeaderColor = '#1d2d44'; /* header color of mail notifications */

		$themePath = OC_Util::getTheme()->getDirectory();

		$defaultsPath = OC::$SERVERROOT . '/' . $themePath . '/defaults.php';
		if (\file_exists($defaultsPath)) {
			// prevent defaults.php from printing output
			\ob_start();
			require_once $defaultsPath;
			\ob_end_clean();
			if (\class_exists('OC_Theme')) {
				$this->theme = new OC_Theme();
			}
		}
	}

	/**
	 * @param string $method
	 * @return bool
	 */
	private function themeExist($method) {
		if (isset($this->theme) && \method_exists($this->theme, $method)) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the base URL
	 * @return string URL
	 */
	public function getBaseUrl() {
		if ($this->themeExist('getBaseUrl')) {
			return $this->theme->getBaseUrl();
		} else {
			return $this->defaultBaseUrl;
		}
	}

	/**
	 * Returns the URL where the sync clients are listed
	 * @return string URL
	 */
	public function getSyncClientUrl() {
		if ($this->themeExist('getSyncClientUrl')) {
			return $this->theme->getSyncClientUrl();
		} else {
			return $this->defaultSyncClientUrl;
		}
	}

	/**
	 * Returns the URL to the App Store for the iOS Client
	 * @return string URL
	 */
	public function getiOSClientUrl() {
		if ($this->themeExist('getiOSClientUrl')) {
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			return $this->theme->getiOSClientUrl();
		} else {
			return $this->defaultiOSClientUrl;
		}
	}

	/**
	 * Returns the AppId for the App Store for the iOS Client
	 * @return string AppId
	 */
	public function getiTunesAppId() {
		if ($this->themeExist('getiTunesAppId')) {
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			return $this->theme->getiTunesAppId();
		} else {
			return $this->defaultiTunesAppId;
		}
	}

	/**
	 * Returns the URL to Google Play for the Android Client
	 * @return string URL
	 */
	public function getAndroidClientUrl() {
		if ($this->themeExist('getAndroidClientUrl')) {
			/* @phan-suppress-next-line PhanUndeclaredMethod */
			return $this->theme->getAndroidClientUrl();
		} else {
			return $this->defaultAndroidClientUrl;
		}
	}

	/**
	 * Returns the documentation URL
	 * @return string URL
	 */
	public function getDocBaseUrl() {
		if ($this->themeExist('getDocBaseUrl')) {
			return $this->theme->getDocBaseUrl();
		} else {
			return $this->defaultDocBaseUrl;
		}
	}

	/**
	 * Returns the title
	 * @return string title
	 */
	public function getTitle() {
		if ($this->themeExist('getTitle')) {
			return $this->theme->getTitle();
		} else {
			return $this->defaultTitle;
		}
	}

	/**
	 * Returns the short name of the software
	 * @return string title
	 */
	public function getName() {
		if ($this->themeExist('getName')) {
			return $this->theme->getName();
		} else {
			return $this->defaultName;
		}
	}

	/**
	 * Returns the short name of the software containing HTML strings
	 * @return string title
	 */
	public function getHTMLName() {
		if ($this->themeExist('getHTMLName')) {
			return $this->theme->getHTMLName();
		} else {
			return $this->defaultName;
		}
	}

	/**
	 * Returns entity (e.g. company name) - used for footer, copyright
	 * @return string entity name
	 */
	public function getEntity() {
		if ($this->themeExist('getEntity')) {
			return $this->theme->getEntity();
		} else {
			return $this->defaultEntity;
		}
	}

	/**
	 * Returns slogan
	 * @return string slogan
	 */
	public function getSlogan() {
		if ($this->themeExist('getSlogan')) {
			return $this->theme->getSlogan();
		} else {
			return $this->defaultSlogan;
		}
	}

	/**
	 * Returns logo claim
	 * @return string logo claim
	 */
	public function getLogoClaim() {
		if ($this->themeExist('getLogoClaim')) {
			return $this->theme->getLogoClaim();
		} else {
			return $this->defaultLogoClaim;
		}
	}

	/**
	 * Returns short version of the footer
	 * @return string short footer
	 */
	public function getShortFooter() {
		if ($this->themeExist('getShortFooter')) {
			$footer = $this->theme->getShortFooter();
		} else {
			$footer  = '<a href="' . $this->getBaseUrl() . '" target="_blank" rel="noreferrer">' . $this->getEntity() . '</a>';
			$footer .= ' &ndash; ' . $this->getSlogan();

			if ($this->getImprintUrl() !== '') {
				$footer .= '<span class="nowrap"> | <a href="' . $this->getImprintUrl() . '" target="_blank">' . $this->l->t('Imprint') . '</a></span>';
			}

			if ($this->getPrivacyPolicyUrl() !== '') {
				$footer .= '<span class="nowrap"> | <a href="'. $this->getPrivacyPolicyUrl() .'" target="_blank">'. $this->l->t('Privacy Policy') .'</a></span>';
			}
		}

		return $footer;
	}

	/**
	 * Returns long version of the footer
	 * @return string long footer
	 */
	public function getLongFooter() {
		if ($this->themeExist('getLongFooter')) {
			$footer = $this->theme->getLongFooter();
		} else {
			$footer = $this->getShortFooter();
		}

		return $footer;
	}

	/**
	 * @param string $key
	 */
	public function buildDocLinkToKey($key) {
		if ($this->themeExist('buildDocLinkToKey')) {
			return $this->theme->buildDocLinkToKey($key);
		}
		return $this->getDocBaseUrl() . '/server/' . $this->defaultDocVersion . '/go.php?to=' . $key;
	}

	/**
	 * Returns mail header color
	 * @return string
	 */
	public function getMailHeaderColor() {
		if ($this->themeExist('getMailHeaderColor')) {
			return $this->theme->getMailHeaderColor();
		} else {
			return $this->defaultMailHeaderColor;
		}
	}

	/**
	 * Returns URL to imprint
	 * @return string
	 */
	public function getImprintUrl() {
		try {
			return $this->config->getAppValue('core', 'legal.imprint_url', '');
		} catch (\Exception $e) {
			return '';
		}
	}

	/**
	 * Returns URL to Privacy Policy
	 * @return string
	 */
	public function getPrivacyPolicyUrl() {
		try {
			return $this->config->getAppValue('core', 'legal.privacy_policy_url', '');
		} catch (\Exception $e) {
			return '';
		}
	}

	/**
	 * @internal Used for unit tests
	 *
	 * @param IConfig $config
	 */
	public function setConfig(IConfig $config) {
		$this->config = $config;
	}
}

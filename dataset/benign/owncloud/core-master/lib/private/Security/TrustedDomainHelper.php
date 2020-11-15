<?php
/**
 * @author Lukas Reschke <lukas@statuscode.ch>
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

namespace OC\Security;
use OC\AppFramework\Http\Request;
use OCP\IConfig;

/**
 * Class TrustedDomain
 *
 * @package OC\Security
 */
class TrustedDomainHelper {
	/** @var IConfig */
	private $config;

	/**
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * Strips a potential port from a domain (in format domain:port)
	 * @param string $host
	 * @return string $host without appended port
	 */
	private function getDomainWithoutPort($host) {
		$pos = \strrpos($host, ':');
		if ($pos !== false) {
			$port = \substr($host, $pos + 1);
			if (\is_numeric($port)) {
				$host = \substr($host, 0, $pos);
			}
		}
		return $host;
	}

	/**
	 * Checks whether a domain is considered as trusted from the list
	 * of trusted domains. If no trusted domains have been configured, returns
	 * true.
	 * This is used to prevent Host Header Poisoning.
	 * @param string $domainWithPort
	 * @return bool true if the given domain is trusted or if no trusted domains
	 * have been configured
	 */
	public function isTrustedDomain($domainWithPort) {
		$domain = $this->getDomainWithoutPort($domainWithPort);

		// Read trusted domains from config
		$trustedList = $this->config->getSystemValue('trusted_domains', []);
		if (!\is_array($trustedList)) {
			return false;
		}

		// Always allow access from localhost
		if (\preg_match(Request::REGEX_LOCALHOST, $domain) === 1) {
			return true;
		}

		// Compare with port appended
		if (\in_array($domainWithPort, $trustedList, true)) {
			return true;
		}

		return \in_array($domain, $trustedList, true);
	}
}

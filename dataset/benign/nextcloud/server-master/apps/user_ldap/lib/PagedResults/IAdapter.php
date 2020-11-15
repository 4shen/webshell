<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
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

namespace OCA\User_LDAP\PagedResults;

interface IAdapter {

	/**
	 * Methods for initiating Paged Results Control
	 */

	/**
	 * The adapter receives paged result parameters from the client. It may
	 * store the parameters for later use.
	 */
	public function setRequestParameters($link, int $pageSize, bool $isCritical): void;

	/**
	 * The adapter is asked for an function that is being explicitly called to
	 * send the control parameters to LDAP. If not function has to be called,
	 * null shall be returned.
	 *
	 * It will used by the callee for diagnosis and error handling.
	 */
	public function getRequestCallFunc(): ?string;

	/**
	 * The adapter is asked to provide the arguments it would pass to the
	 * function returned by getRequestCallFunc(). If none shall be called, an
	 * empty array should be returned.
	 *
	 * It will used by the callee for diagnosis and error handling.
	 */
	public function getRequestCallArgs($link): array;

	/**
	 * The adapter is asked to do the necessary calls to LDAP, if
	 * getRequestCallFunc returned a function. If none, it will not be called
	 * so the return value is best set to false. Otherwise it shall respond
	 * whether setting the controls was successful.
	 */
	public function requestCall($link): bool;

	/**
	 * The adapter shall report which PHP function will be called to process
	 * the paged results call
	 *
	 * It will used by the callee for diagnosis and error handling.
	 */
	public function getResponseCallFunc(): string;

	/**
	 * The adapter shall report with arguments will be provided to the LDAP
	 * function it will call
	 *
	 * It will used by the callee for diagnosis and error handling.
	 */
	public function getResponseCallArgs(array $originalArgs): array;

	/**
	 * the adapter should do it's LDAP function call and return success state
	 *
	 * @param resource $link LDAP resource
	 * @return bool
	 */
	public function responseCall($link): bool;

	/**
	 * The adapter receives the parameters that were passed to a search
	 * operation. Typically it wants to save the them for the call proper later
	 * on.
	 */
	public function setSearchArgs(
		$link,
		string $baseDN,
		string $filter,
		array $attr,
		int $attrsOnly,
		int $limit
	): void;

	/**
	 * The adapter shall report which arguments shall be passed to the
	 * ldap_search function.
	 */
	public function getSearchArgs($link): array;

	/**
	 * The adapter receives the parameters that were passed to a read
	 * operation. Typically it wants to save the them for the call proper later
	 * on.
	 */
	public function setReadArgs($link, string $baseDN, string $filter, array $attr): void;

	/**
	 * The adapter shall report which arguments shall be passed to the
	 * ldap_read function.
	 */
	public function getReadArgs($link): array;

	/**
	 * Returns the current paged results cookie
	 *
	 * @param resource $link LDAP resource
	 * @return string
	 */
	public function getCookie($link): string;
}

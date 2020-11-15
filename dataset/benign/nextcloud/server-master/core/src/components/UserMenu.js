/*
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

import OC from '../OC'

import $ from 'jquery'

export const setUp = () => {
	const $menu = $('#header #settings')

	// show loading feedback
	$menu.delegate('a', 'click', event => {
		let $page = $(event.target)
		if (!$page.is('a')) {
			$page = $page.closest('a')
		}
		if (event.which === 1 && !event.ctrlKey && !event.metaKey) {
			$page.find('img').remove()
			$page.find('div').remove() // prevent odd double-clicks
			$page.prepend($('<div/>').addClass('icon-loading-small'))
		} else {
			// Close navigation when opening menu entry in
			// a new tab
			OC.hideMenus(() => false)
		}
	})

	$menu.delegate('a', 'mouseup', event => {
		if (event.which === 2) {
			// Close navigation when opening app in
			// a new tab via middle click
			OC.hideMenus(() => false)
		}
	})
}

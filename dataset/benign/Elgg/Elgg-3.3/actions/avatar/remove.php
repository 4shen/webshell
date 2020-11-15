<?php
/**
 * Avatar remove action
 */

elgg_deprecated_notice("The action 'avatar/remove' has been deprecated, use 'avatar/upload'", '3.1');

$user_guid = (int) get_input('guid');
$user = get_user($user_guid);

if (!$user || !$user->canEdit()) {
	return elgg_error_response(elgg_echo('avatar:remove:fail'));
}

$user->deleteIcon();

return elgg_ok_response('', elgg_echo('avatar:remove:success'));

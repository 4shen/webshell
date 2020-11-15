<?php
/**
 * Aggregate action for saving settings
 *
 * To see the individual action methods, enable the developers plugin, visit Admin > Inspect > Plugin Hooks
 * and search for "usersettings:save". The default methods are listed below:
 *
 * @see _elgg_set_user_language
 * @see _elgg_set_user_password
 * @see _elgg_set_user_default_access
 * @see _elgg_set_user_name
 * @see _elgg_set_user_username
 * @see _elgg_set_user_email
 */

/* @var $request \Elgg\Request */

$guid = $request->getParam('guid');
if (isset($guid)) {
	$user = get_user($guid);
} else {
	$user = elgg_get_logged_in_user_entity();
}

if (!$user instanceof ElggUser) {
	throw new \Elgg\EntityNotFoundException();
}

if (!$user->canEdit()) {
	throw new \Elgg\EntityPermissionsException();
}

elgg_make_sticky_form('usersettings');

$hooks_params = [
	'user' => $user,
	'request' => $request,
];

// callbacks should return false to indicate that the sticky form should not be cleared
if (elgg_trigger_plugin_hook('usersettings:save', 'user', $hooks_params, true)) {
	elgg_clear_sticky_form('usersettings');
}

foreach ($request->validation()->all() as $item) {
	if ($item->isValid()) {
		if ($message = $item->getMessage()) {
			system_message($message);
		}
	} else {
		if ($error = $item->getError()) {
			register_error($error);
		}
	}
}

$data = [
	'user' => $user,
	'validation' => $request->validation(),
];

return elgg_ok_response($data);

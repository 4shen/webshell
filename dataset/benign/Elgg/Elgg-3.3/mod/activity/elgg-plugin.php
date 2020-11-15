<?php

return [
	'routes' => [
		'collection:river:owner' => [
			'path' => '/activity/owner/{username}',
			'resource' => 'river',
		],
		'collection:river:friends' => [
			'path' => '/activity/friends/{username?}',
			'resource' => 'river',
			'required_plugins' => [
				'friends',
			],
		],
		'collection:river:group' => [
			'path' => '/activity/group/{guid}',
			'resource' => 'activity/group',
			'required_plugins' => [
				'groups',
			],
		],
		'collection:river:all' => [
			'path' => '/activity/all',
			'resource' => 'river',
		],
		'default:river' => [
			'path' => '/activity',
			'resource' => 'river',
		],
	],
	'widgets' => [
		'group_activity' => [
			'context' => ['dashboard'],
			'multiple' => true,
			'required_plugin' => 'groups',
		],
		'river_widget' => [
			'context' => ['profile', 'dashboard'],
		],
	],
];

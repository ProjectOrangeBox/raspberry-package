<?php

return [
	'all' => ['get', 'cli', 'post', 'put', 'delete'], /* optional */
	'default' => ['get'], /* optional */
	'routes format' => 'human', /* optional */
	'routes' => [
		/* default routes */
		'/help' => ['\application\controllers\main', 'help'],
		'/test<number>' => ['\application\controllers\main', 'test<number>'],

		'/handlebars' => ['\application\controllers\handlebars', 'index'],
		'/warm' => ['\application\controllers\handlebars', 'warm'],

		'[cli & delete]/collect' => ['\application\controllers\main', 'collect'],
		'/phpinfo' => ['\application\controllers\main', 'phpinfo'],

		'[post]/' =>	['\application\controllers\formPost', 'post'],

		/* exception route */
		'[@]/::exception::' => ['\application\controllers\main', 'fourohfour'],

		/* all routes */
		'[@]/' => ['\application\controllers\main', 'index'],
		'[@]/(.*)' => ['\application\controllers\main', 'fourohfour'],
	],
];

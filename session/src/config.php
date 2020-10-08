<?php

return [
	'file' => [
		'path' => '/var/sessions',
	],
	'handler' => '\projectorangebox\session\handlers\SessionFile',
	'name' => null,
	'lifetime' => 7200,
	'path' => null,
	'domain' => null,
	'secure' => false,
	'httponly' => true,
	'cache limiter' => 'nocache',
	'isAjax' => false,
	'regenerate percent' => 10,
];

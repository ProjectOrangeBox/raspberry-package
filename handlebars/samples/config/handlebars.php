<?php

return [
	'plugin folder' => '/hb-plugins/',
	'plugin extension' => '.hbs.php',
	'view folder' => '/hbsViews/',
	'view extension' => 'hbs',
	'cache folder' => '/var/views',
	'cache prefix' => 'hbs.',
	'force compile' => true,
	'data' => [
		'name' => 'Don Meyers',
		'address' => '12 South Main Street<br>Phoenixville, Pa 19460',
		'year' => date('Y'),
	],
	'plugins' => [
		'samples' => '/packages/projectorangebox/handlebars/samples/hb-plugins/samples.hbs.php',
		'iff' => '/packages/projectorangebox/handlebars/src/hb-plugins/iff.hbs.php',
		'now' => '/packages/projectorangebox/handlebars/src/hb-plugins/now.hbs.php',
		'if_ne' => '/packages/projectorangebox/handlebars/src/hb-plugins/if_ne.hbs.php',
		'exp.query' => '/packages/projectorangebox/handlebars/src/hb-plugins/exp.query.hbs.php',
		'if_eq' => '/packages/projectorangebox/handlebars/src/hb-plugins/if_eq.hbs.php',
		'format.date' => '/packages/projectorangebox/handlebars/src/hb-plugins/format.date.hbs.php',
		'if_gt' => '/packages/projectorangebox/handlebars/src/hb-plugins/if_gt.hbs.php',
		'hbp.deferred' => '/packages/projectorangebox/handlebars/src/hb-plugins/hbp.deferred.hbs.php',
		'uppercase' => '/packages/projectorangebox/handlebars/src/hb-plugins/uppercase.hbs.php',
		'q.cache_demo' => '/packages/projectorangebox/handlebars/src/hb-plugins/q.cache_demo.hbs.php',
		'exp.channel.entries' => '/packages/projectorangebox/handlebars/src/hb-plugins/exp.channel.entries.hbs.php',
		'lowercase' => '/packages/projectorangebox/handlebars/src/hb-plugins/lowercase.hbs.php',
		'if_lt' => '/packages/projectorangebox/handlebars/src/hb-plugins/if_lt.hbs.php',
		'exp.block' => '/packages/projectorangebox/handlebars/src/hb-plugins/exp.block.hbs.php',
		'is_odd' => '/packages/projectorangebox/handlebars/src/hb-plugins/is_odd.hbs.php',
		'set' => '/packages/projectorangebox/handlebars/src/hb-plugins/set.hbs.php',
		'is_even' => '/packages/projectorangebox/handlebars/src/hb-plugins/is_even.hbs.php',
	],
	'views' =>
	[
		'welcome' => 'application/hbsViews/welcome.hbs',
		'header' => 'application/hbsViews/header.hbs',
		'footer' => 'application/hbsViews/footer.hbs',
	],
];

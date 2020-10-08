<?php

/*
Example

<p>{{#q:cache_demo cache="5"}}This will be cached for 5 minutes [{{now}}] {{/q:cache_demo}}</p>

*/

$helpers['q:cache_demo'] = function ($options) {
	$cache = service('cache');

	$ttl = (int)($options['hash']['cache'] * 60);
	$key = $options['hash']['key'] ?? md5(json_encode($options));

	if (!$html = $cache->get($key)) {
		$html = $options['fn']($options['_this']) . PHP_EOL;
		$html .= 'Cached on: ' . date('Y-m-d H:i:s') . PHP_EOL;
		$html .= 'For ' . $options['hash']['cache'] . ' Minutes' . PHP_EOL;
		$html .= 'At ' . date('Y-m-d H:i:s', strtotime('+' . (int)$options['hash']['cache'] . ' minutes'));

		$cache->save($key, $html, $ttl);
	}

	return $html;
};

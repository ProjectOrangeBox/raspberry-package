<?php

namespace projectorangebox\router;

class Formatter
{
	/*
		'/test/(?<primaryid>\d+)' => ['\application\controllers\main', 'test', '<primaryid>'],
		'/test/<anything>' => ['\application\controllers\main', 'test', '<anything>'],
		'/test/<aaa>' => ['\application\controllers\main', 'test/<aaa>'],
		'/test<number>' => ['\application\controllers\main', 'test<number>'],
		'/test/<aaa>/<bbb>' => ['\application\controllers\main', 'test', '<bbb>/<aaa>'],
	*/
	static public function format(array $config): array
	{
		/* if none specified */
		$config['default'] = $config['default'] ?? ['get', 'cli'];

		/* @ */
		$config['all'] = $config['all'] ?? ['get', 'cli', 'post', 'put', 'delete'];

		/* look for <tag> but can't start with ? which would mean it's already formatted correctly */
		$re = '@([^\?])<(.[^>]*)>@m';

		$formatted = [];

		foreach ($config['routes'] as $regex => $rewrite) {
			/* regex passed by reference */
			$httpMethods = self::getMethods($regex, $config);

			if (preg_match_all($re, $regex, $matches, PREG_SET_ORDER, 0)) {
				foreach ($matches as $match) {
					$regex = str_replace(substr($match[0], 1), '(?<' . $match[2] . '>[^/]*)', $regex);
				}
			}

			$regex = '#^/' . ltrim($regex, '/') . '$#im';

			foreach ($httpMethods as $method) {
				/* if set higher up the array don't replace */
				if (!isset($formatted[$method][$regex])) {
					$formatted[$method][$regex] = $rewrite;
				}
			}
		}

		return $formatted;
	}

	/**
	 * '[@]/' => ['\application\controllers\main', 'index'],
	 * '[cli]/' => ['\application\controllers\main', 'index'],
	 * '[cli & get]/' => ['\application\controllers\main', 'index'],
	 */
	static protected function getMethods(&$regex, array $config): array
	{
		$re = '#(\[(?<httpmethod>.*)\])?(?<route>.*)#';

		if (preg_match($re, $regex, $matches, PREG_OFFSET_CAPTURE, 0)) {
			/* reassign cleaned up regex to the variable passed by reference */
			$regex = $matches['route'][0];

			$httpMethods = $matches['httpmethod'][0];

			switch ($httpMethods) {
				case '@':
					/* no methods specified use all */
					$httpMethods = $config['all'];
					break;
				case '':
					/* if it's empty than use the defaults */
					$httpMethods = $config['default'];
					break;
				default:
					/* use supplied methods ie. get&delete&put */
					$httpMethods = explode('&', str_replace(' ', '', $httpMethods));
			}
		}

		return $httpMethods;
	}
} /* end class */

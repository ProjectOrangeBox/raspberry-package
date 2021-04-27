<?php

/* wrapper to get service from container which is attached to the application */

use projectorangebox\app\exceptions\notInstanceOf;

if (!function_exists('service')) {
	/**
	 * service
	 *
	 * @param string $serviceName Service you are requesting
	 * @return mixed
	 */
	function service(string $serviceName = null)
	{
		$container = \projectorangebox\app\App::container();

		return (!$serviceName) ? $container : ((!$container->has($serviceName)) ? null : $container->get($serviceName));
	}
} /* end service */

/* The most basic exception handler */
if (!function_exists('showException')) {
	/**
	 * showException
	 *
	 * @param Throwable $throwable
	 * @return void
	 */
	function showException(\Throwable $throwable): void
	{
		/* log service avaiable yet? */
		if (service('log')) {
			service('log')->log('critical', (string)$throwable);
		}

		/* send static class avaiable yet? */
		if (class_exists('send', true)) {
			if (method_exists('send', 'exception')) {
				/* exit called in the send class */
				send::exception($throwable);
			}
		}

		/* fall back to low level exception display */
		echo (PHP_SAPI == 'cli') ?  'Error Thrown:' . PHP_EOL . (string)$throwable . PHP_EOL : '<h1>Error</h1><p><pre>' . (string)$throwable . '</pre></p>';

		/* exit if we havn't already ie. in send class */
		exit(1);
	}
} /* end showException */

/* test that the class in a instance of ______ */
if (!function_exists('mustBe')) {
	function mustBe($class, $of)
	{
		if (!($class instanceof $of)) {
			throw new notInstanceOf(get_class($class) . ' not ' . $of);
		}
	}
}

if (!function_exists('buildConfig')) {
	function buildConfig(array $config, array $required = [],/* string|array */ $default = []): array
	{
		if (is_string($default)) {
			$defaultFile = $default;

			$default = require $default;

			if (!is_array($default)) {
				throw new Exception('Configuration default file "' . $defaultFile . '" did not return a array.');
			}
		} elseif (!is_array($default)) {
			throw new Exception('Configuration default is not an array.');
		}


		// Merge the passed config over the default config
		$config = array_replace($default, $config);

		if (is_array($required) && is_array($missing = array_keys_exists($required, $config))) {
			throw new \projectorangebox\config\exceptions\MissingConfig('Configuration Key(s) "' . implode('","', $missing) . '" missing.');
		}

		return $config;
	}
}

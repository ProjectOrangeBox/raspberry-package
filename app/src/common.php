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
		if (is_null($class)) {
			throw new notInstanceOf('class is null not ' . $of);
		}

		if (!($class instanceof $of)) {
			throw new notInstanceOf(get_class($class) . ' not ' . $of);
		}
	}
}

if (!function_exists('mergeConfig')) {
	/**
	 * take the passed configuration array
	 * merge it over the default configuration array
	 * 	passed as an array or loaded from a configration file (absolute path only supported)
	 * then check it for required array keys
	 * 
	 * @param array configuration array
	 * @param array array of required keys
	 * @param string|array absolute path to configuration file or array of default key => value pairs
	 * 
	 * @return array
	 */
	function mergeConfig(/* string|array|null|false */$defaultConfig, array $newConfig, array $requiredKeys = []): array
	{
		if (is_string($defaultConfig)) {
			/* need this to display error */
			$defaultFile = $defaultConfig;

			if (!file_exists($defaultFile)) {
				throw new Exception('Configuration file "' . $defaultFile . '" not found.');
			}

			$defaultConfig = require $defaultFile;

			if (!is_array($defaultConfig)) {
				throw new Exception('Configuration file "' . $defaultFile . '" did not return a array.');
			}
		} elseif ($defaultConfig === null || $defaultConfig === false) {
			$defaultConfig = [];
		} elseif (!is_array($defaultConfig)) {
			throw new Exception('Configuration default is not an array.');
		}

		// replace the passed new config over the default config
		$config = array_replace($defaultConfig, $newConfig);

		$missing = true;

		foreach ($requiredKeys as $key) {
			if (!array_key_exists($key, $config)) {
				$missing[] = $key;
			}
		}

		if ($missing !== true) {
			throw new \projectorangebox\config\exceptions\MissingConfig('Configuration Key(s) "' . implode('","', $missing) . '" missing.');
		}

		return $config;
	}
}

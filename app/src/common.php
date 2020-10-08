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

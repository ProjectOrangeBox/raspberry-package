<?php

namespace projectorangebox\app;

use FS;
use projectorangebox\log\LoggerTrait;
use projectorangebox\request\RequestInterface;
use projectorangebox\response\ResponseInterface;
use projectorangebox\container\ContainerInterface;
use projectorangebox\app\exceptions\RootNotDefined;
use projectorangebox\app\exceptions\fsClassNotFound;

class App implements AppInterface
{
	use LoggerTrait;

	/**
	 * $container
	 *
	 * @var \projectorangebox\container\ContainerInterface
	 */
	protected static $container = null;

	/**
	 * Method __construct
	 *
	 * @param ContainerInterface $container [explicite description]
	 *
	 * @return App
	 */
	public function __construct(ContainerInterface $container)
	{
		/* Save a copy of our container for later use */
		self::$container = $container;

		/* set End Of Line based on request type */
		define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		/* set DEBUG value if set */
		define('DEBUG', (bool)$_ENV['DEBUG']);

		/* setup default error handling */
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

		/* turn on or off errors based on DEBUG value */
		ini_set('display_errors', (int)DEBUG);

		/*
		set the most basic exception handler
		This has already been loaded via composer autoload inside common.php file
		this could be overridden by creating / loading different functions before instantiating App
		*/
		set_exception_handler('showException');

		/* Check for this because it's required */
		if (!\defined('__ROOT__')) {
			throw new RootNotDefined();
		}

		/*
		Check for this because it's required
		This has already been loaded via composer autoload
		*/
		if (!\class_exists('FS')) {
			throw new FsClassNotFound();
		}

		/* Set File System Functions Root Directory and chdir to it */
		FS::setRoot(__ROOT__, true);

		/* ready to go! */
	}

	/**
	 * dispatch - kind of a pass thru for the dispatcher
	 *
	 * @param string $uri Uniform Resource Identifier.
	 * @param string $httpMethod Http Method ie. get, put, post, header, delete.
	 * @return void
	 */
	public function dispatch(RequestInterface $request = null): ResponseInterface
	{
		/* If they did not send in a request object use the one setup in the container */
		return self::$container->dispatcher->dispatch($request ?? self::$container->request);
	}

	/**
	 * Method container
	 * return our dependency container
	 *
	 * @return containerInterface
	 */
	static public function container(): ContainerInterface
	{
		/* using self because there is only 1 container */
		return self::$container;
	}
} /* end app */

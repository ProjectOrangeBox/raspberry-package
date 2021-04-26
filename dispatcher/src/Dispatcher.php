<?php

namespace projectorangebox\dispatcher;

use projectorangebox\log\LoggerTrait;
use projectorangebox\router\RouterInterface;
use projectorangebox\request\RequestInterface;
use projectorangebox\response\ResponseInterface;
use projectorangebox\container\ContainerInterface;
use projectorangebox\middleware\MiddlewareRequest;
use projectorangebox\middleware\MiddlewareResponse;
use projectorangebox\dispatcher\exceptions\ClassNotFound;
use projectorangebox\dispatcher\exceptions\MethodNotFound;
use projectorangebox\middleware\MiddlewareHandlerInterface;

/*
Array key is for the router
Array value is for the dispatcher

'/help' => ['\application\controllers\main', 'help'],
'/test<number>/<name>/<food>' => ['\application\controllers\main', 'test<number>', '<name>/<food>'],

'/handlebars' => ['\application\controllers\handlebars', 'index'],
'/warm' => ['\application\controllers\handlebars', 'warm'],

'[cli & delete]/collect' => ['\application\controllers\main', 'collect'],
'/phpinfo' => ['\application\controllers\main', 'phpinfo'],

'[post]/' =>	['\application\controllers\formPost', 'post'],

// all routes
'[@]/' => ['\application\controllers\main', 'index'],
'[@]/(.*)' => ['\application\controllers\main', 'fourohfour'],

// exception thrown route
'[@]/::exception::' => ['\application\controllers\main', 'fourohfour'],

*/

class Dispatcher implements DispatcherInterface
{
	use LoggerTrait;

	protected $config = [];

	protected $regexCaptured = [];
	protected $segments = [];

	protected $container = null;
	protected $routerService = null;
	protected $responseService = null;
	protected $middlewareService = null;

	public function __construct(array $config)
	{
		$this->config = $config;

		/* This is injected into the controller constructor */
		$this->container = $this->config['containerService'];

		mustBe($this->container, ContainerInterface::class);

		/* get router */
		$this->routerService = $this->container->router;

		mustBe($this->routerService, RouterInterface::class);

		/* get response */
		$this->responseService = $this->container->response;

		mustBe($this->responseService, ResponseInterface::class);

		/* Do we even have the middleware service? it's not needed */
		if ($this->container->has('middleware')) {
			$this->middlewareService = $this->container->middleware;

			mustBe($this->middlewareService, MiddlewareHandlerInterface::class);
		}
	}

	public function dispatch(RequestInterface $request): ResponseInterface
	{
		/* middleware input / request */
		if ($this->middlewareService) {
			$this->middlewareService->request(new MiddlewareRequest($this->container, $request, $this->responseService));
		}

		$this->log('debug', __CLASS__ . ' "' . $request->uri() . '" "' . $request->requestMethod() . '" ' . $request->server('request_method') . '::' . $request->server('request_uri'));

		/* throws an exception if a route isn't found */
		$matched = $this->routerService->handle($request->uri(), $request->requestMethod());

		/* save these for the method and parameter merges */

		/* regular expression captured */
		$this->regexCaptured = $this->routerService->captured();

		/* get the uri segments */
		$this->segments = $request->segments();

		/* throws an exception if a class or method isn't found */
		$this->responseService->append($this->callControllerMethod($matched));

		/* middleware output / response */
		if ($this->middlewareService) {
			/* passed by reference */
			$this->middlewareService->response(new MiddlewareResponse($this->container, $request, $this->responseService));
		}

		/* return the response */
		return $this->responseService;
	}

	/**
	 * Method callControllerMethod
	 *
	 * @param array $matched [namespaced Class, Methos, [array of parameters]]
	 * @param array $captured [explicite description]
	 *
	 * @return string
	 */
	protected function callControllerMethod(array $routeMatched): string
	{
		/* array returned from match */
		list($namespaceClass, $method, $params) = $routeMatched;

		$method = $method ?? 'index';
		$params = $params ?? [];

		if (!\class_exists($namespaceClass, true)) {
			/* throw low level error */
			throw new ClassNotFound($namespaceClass);
		}

		/* instantiate the controller injecting the container */
		$controller = new $namespaceClass($this->container);

		mustBe($controller, ControllerInterface::class);

		$method = $this->mergeAll($method);

		if (!\method_exists($controller, $method)) {
			/* throw low level error */
			throw new MethodNotFound($namespaceClass . '::' . $method);
		}

		/* convert parameters into a string for easy find and replace then back together again */
		$params = explode(chr(7), $this->mergeAll(implode(chr(7), $params)));

		/* call the controller method */
		$output = \call_user_func_array([$controller, $method], $params);

		return $output ?? '';
	}

	protected function mergeAll(string $string): string
	{
		return $this->merge($this->merge($string, $this->segments, '<seg', '>'), $this->regexCaptured, '<', '>');
	}
	protected function merge(string $string, array $parameters, string $left_delimiter, string $right_delimiter): string
	{
		$replacer = function ($match) use ($parameters) {
			return isset($parameters[$match[1]]) ? $parameters[$match[1]] : $match[0];
		};

		return preg_replace_callback('/' . $left_delimiter . '\s*(.+?)\s*' . $right_delimiter . '/', $replacer, $string);
	}
} /* end class */

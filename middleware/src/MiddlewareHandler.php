<?php

namespace projectorangebox\middleware;

use projectorangebox\log\LoggerTrait;
use projectorangebox\middleware\MiddlewarePayload;
use projectorangebox\middleware\MiddlewareRequest;
use projectorangebox\middleware\MiddlewareResponse;
use projectorangebox\middleware\exceptions\ClassNotFound;
use projectorangebox\middleware\exceptions\MethodNotFound;
use projectorangebox\middleware\MiddlewareHandlerInterface;

class MiddlewareHandler implements MiddlewareHandlerInterface
{
	use LoggerTrait;

	protected $config = [];

	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/Config.php', $config);
	}

	public function request(MiddlewareRequest &$payload): void
	{
		$this->call('request', $payload);
	}

	public function response(MiddlewareResponse &$payload): void
	{
		$this->call('response', $payload);
	}

	protected function call(string $method, MiddlewarePayload &$payload): void
	{
		$httpMethod = $payload->request->requestMethod();
		$uri = $payload->request->uri();

		if (isset($this->config[$method][$httpMethod]) && is_array($this->config[$method][$httpMethod])) {
			foreach ($this->getMatches($this->config[$method][$httpMethod], $uri) as $namedSpacedClass) {
				if ($this->trigger($namedSpacedClass, $method, $payload) === false) {
					break; /* break out if false returned */
				}
			}
		}
	}

	protected function trigger(string $namedSpacedClass, string $method, MiddlewarePayload &$payload): bool
	{
		$continue = true;

		if ($middlewareInstance = $this->exists($namedSpacedClass, $method, $payload)) {
			if ($middlewareInstance->$method($payload) === false) {
				$continue = false;
			}
		}

		/* call the controller method */
		return $continue;
	}

	protected function exists(string $namedSpacedClass, string $method, MiddlewarePayload &$payload) /* mixed */
	{
		$middlewareInstance = false;

		if (\class_exists($namedSpacedClass, true)) {
			/* create new middleware class and pass in the container */
			$middlewareInstance = new $namedSpacedClass($payload->container);

			mustBe($middlewareInstance, MiddlewareInterface::class);

			if (!\method_exists($middlewareInstance, $method)) {
				throw new MethodNotFound($method);
			}
		} else {
			throw new ClassNotFound($namedSpacedClass);
		}

		return $middlewareInstance;
	}

	protected function getMatches(array $routes, string $uri): array
	{
		/* default to no match */
		$matched = [];

		foreach ($routes as $regex => $match) {
			if (preg_match($regex, $uri, $params)) {
				if (is_array($match)) {
					$matched = $matched + array_combine($match, $match);
				} else {
					$matched[$match] = $match;
				}
			}
		}

		return $matched;
	}
} /* end middleware class */

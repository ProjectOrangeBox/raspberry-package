<?php

namespace projectorangebox\router;

use projectorangebox\log\LoggerTrait;
use projectorangebox\router\exceptions\NoMatchingRouteFound;

/*
 * '/test/(?<primaryid>\d+)' => ['\application\controllers\main', 'test', '<primaryid>'],
 * '/test/<anything>' => ['\application\controllers\main', 'test', '<anything>'],
 * '/test/<aaa>' => ['\application\controllers\main', 'test/<aaa>'],
 * '/test<number>' => ['\application\controllers\main', 'test<number>'],
 * '/test/<aaa>/<bbb>' => ['\application\controllers\main', 'test', '<bbb>/<aaa>'],
 *
 * '[@]/' => ['\application\controllers\main', 'index'],
 * '[cli]/' => ['\application\controllers\main', 'index'],
 * '[cli & get]/' => ['\application\controllers\main', 'index'],
 */

class Router implements RouterInterface
{
	use LoggerTrait;

	protected $config = [];
	protected $routes = [];
	protected $captured = [];

	public function __construct(array $config)
	{
		$this->log('debug', 'Router::__construct');

		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/config.php', $config);

		$this->routes = $this->config['routes'];
	}

	public function handle(string $uri, string $httpMethod) /* mixed */
	{
		$this->log('debug', 'URI ' . $uri);

		/* clear captured */
		$this->captured = [];

		/* default to no match */
		$matched = false;

		if (is_array($this->routes[$httpMethod])) {
			foreach ($this->routes[$httpMethod] as $regex => $match) {
				if (preg_match($regex, $uri, $params)) {
					$this->log('debug', 'Matched the URI: ' . $uri . ' Against: ' . $regex);

					foreach ($params as $key => $value) {
						$this->captured[$key] = $value;
					}

					/* found one no need to stay in loop */
					$matched = $match;
					break;
				}
			}
		}

		if (!$matched) {
			/* throw low level error */
			throw new NoMatchingRouteFound($uri . '::' . $httpMethod);
		}

		return $matched;
	}

	public function captured(): array
	{
		return $this->captured;
	}
} /* end class */

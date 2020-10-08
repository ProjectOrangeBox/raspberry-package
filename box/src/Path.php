<?php

use projectorangebox\request\RequestInterface;

class Path
{
	static $config = null;

	static protected function _construct(): void
	{
		if (!self::$config) {
			self::$config = service('config')->get('paths', []);
		}
	}

	static public function autoDetect(bool $bool): void
	{
		self::_construct();

		self::$config['auto detect https'] = $bool;
	}

	static public function protocol(string $protocol): void
	{
		self::_construct();

		self::$config['protocol'] = $protocol;
	}

	static public function host(string $host): void
	{
		self::_construct();

		self::$config['host'] = $host;
	}

	static public function add(string $name, string $uri): void
	{
		self::_construct();

		self::$config[$name] = $uri;
	}

	static public function resolve(string $name, array $parameters = []): string
	{
		self::_construct();

		if (!isset(self::$config['paths'][$name])) {
			throw new Exception('Could not locate the path for ' . $name . '.');
		}

		return self::merge(self::$config['paths'][$name], $parameters);
	}

	static public function redirect(string $name, array $parameters = []): void
	{
		$path = self::resolve($name, $parameters);

		/* if auto detect is on than use that instead */
		if (self::$config['auto detect https']) {
			$request = service('request');

			mustBe($request, RequestInterface::class);

			$protocol = ($request->isHttps()) ? 'https://' : 'http://';
		} else {
			$protocol = self::$config['protocol'] ?? 'http://';
		}

		if (!isset(self::$config['host'])) {
			throw new Exception('Path "host" not set in path config file.');
		}

		$response = service('response');

		mustBe($response, ResponseInterface::class);

		/* and away we go! */
		$response->redirect($protocol . self::$config['host'] . '/' . ltrim($path, '/'));
	}

	static protected function merge(string $string, array $parameters): string
	{
		$left_delimiter = preg_quote('{');
		$right_delimiter = preg_quote('}');

		$replacer = function ($match) use ($parameters) {
			return isset($parameters[$match[1]]) ? $parameters[$match[1]] : $match[0];
		};

		return preg_replace_callback('/' . $left_delimiter . '\s*(.+?)\s*' . $right_delimiter . '/', $replacer, $string);
	}
} /* end class */

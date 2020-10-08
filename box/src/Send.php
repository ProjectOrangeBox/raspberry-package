<?php

use projectorangebox\views\ViewsInterface;
use projectorangebox\config\ConfigInterface;
use projectorangebox\request\RequestInterface;
use projectorangebox\response\ResponseInterface;
use projectorangebox\container\ContainerInterface;

class Send
{
	static protected $showingException = false;

	static public function config(): array
	{
		return [
			'view alias' => [],
			'view prefix by mime' => [
				// mime => prefix view with
				'application/cli' => 'errors/cli/',
				'application/json' => 'errors/ajax/',
				'text/html' => 'errors/html/',
			],
		];
	}

	static public function redirect(string $uri): void
	{
		$response = service('response');

		mustBe($response, ResponseInterface::class);

		$response->redirect($uri);
	}

	/**
	 * @param string|null $body
	 * @param string|null $header
	 * @return void
	 * @throws Exception
	 */
	static public function error(string $body = null, string $header = null)
	{
		$header = $header ?? 'System Error.';
		$body = $body ?? 'The application cannot continue.';

		self::response('error', ['header' => $header, 'body' => $body], 500);
	}

	/**
	 * @param string|null $body
	 * @param string|null $header
	 * @return void
	 * @throws Exception
	 */
	static public function exception(Throwable $exception)
	{
		if (!self::$showingException) {
			self::$showingException = true;

			self::response('exception', ['exception' => $exception], 500);
		} else {
			/* fall back to low level exception display */
			echo (PHP_SAPI == 'cli') ?  'Error Thrown:' . PHP_EOL . (string)$exception . PHP_EOL : '<h1>Error</h1><p><pre>' . (string)$exception . '</pre></p>';

			exit(1);
		}
	}

	/**
	 * @param string|null $body
	 * @param string|null $header
	 * @return void
	 * @throws Exception
	 */
	static public function fourohfour(string $body = null, string $header = null)
	{
		$header = $header ?? 'Page Not Found.';
		$body = $body ?? 'The page you where looking for was not found.';

		self::response('404', ['header' => $header, 'body' => $body], 404);
	}

	/**
	 * @param array $array
	 * @param int $statusCode
	 * @return void
	 * @throws Exception
	 */
	static public function data(array $array, int $statusCode = 200)
	{
		self::response('send', ['array' => $array], $statusCode);
	}

	static public function ajax(array $array, int $statusCode = 200)
	{
		self::response('ajax', ['array' => $array], $statusCode, 'application/json');
	}

	/**
	 * @param string $view
	 * @param array $data
	 * @param int|null $statusCode
	 * @param string|null $mimeType
	 * @return void
	 * @throws Exception
	 */
	static public function response(string $view, array $data, int $statusCode = null, string $mimeType = null): void
	{
		$container = service();

		mustBe($container, ContainerInterface::class);
		mustBe($container->config, ConfigInterface::class);
		mustBe($container->response, ResponseInterface::class);
		mustBe($container->view, ViewsInterface::class);

		$config = array_replace(self::config(), $container->config->get('send', []));

		/* determine the mime type from the request type if it's not already sent in */
		if (!$mimeType) {
			/* we only need this if they don't send in the mimeType */
			mustBe($container->request, RequestInterface::class);

			if ($container->request->isCli()) {
				$mimeType = 'application/cli';
			} elseif ($container->request->isAjax()) {
				$mimeType = 'application/json';
			} else {
				$mimeType = 'text/html';
			}
		}

		/* set the content type unless it's a cli application */
		if ($mimeType != 'application/cli') {
			$container->response->contentType($mimeType);
		}

		/* http header response code */
		if (is_numeric($view) && $statusCode === null) {
			$statusCode = (int) $view;
		} elseif ($statusCode == null) {
			$statusCode = 200;
		} else {
			$statusCode = abs($statusCode);
		}

		/* set the http header response code */
		$container->response->responseCode($statusCode);

		/* is there a view alias for the requested view? */
		$view = $config['view alias'][$view] ?? $view;

		/* are we prefixing the view with anything? */
		$view = ($config['view prefix by mime'][$mimeType]) ? trim($config['view prefix by mime'][$mimeType], '/') . '/' . trim($view, '/') : trim($view, '/');

		/* process the view and send it */
		echo $container->view->render($view, $data);

		/* now figure out the exit code - we can't use 0-9 because they are real UNIX exit codes */
		$exitStatus = (int)(($statusCode / 100) + 9);

		/* exit application */
		$container->response->exit($exitStatus);
	}
} /* end class */

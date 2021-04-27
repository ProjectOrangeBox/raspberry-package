<?php

namespace projectorangebox\response;

use Exception;
use projectorangebox\log\LoggerTrait;
use projectorangebox\request\RequestInterface;
use projectorangebox\response\ResponseInterface;
use projectorangebox\response\exceptions\UnknownContentType;
use projectorangebox\response\exceptions\UnknownResponseCode;

class Response implements ResponseInterface
{
	use LoggerTrait;

	protected $config = [];
	protected $finalOutput = '';
	protected $cookies = [];
	protected $headers = [];

	protected $charSet = 'UTF-8';
	protected $statusCodes = [];
	protected $mimesTypes = [];

	protected $request = null;

	/**
	 * @param array $config
	 * @return void
	 * @throws Exception
	 */
	public function __construct(array $config)
	{
		$this->log('debug', 'Response::__construct');

		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/config.php', $config);

		$this->charSet = $this->config['charset'] ?? $this->charSet;

		$this->statusCodes = $this->config['statusCodes'];
		$this->mimesTypes = $this->config['mimesTypes'];

		mustBe($this->config['request'], RequestInterface::class);

		$this->request = $this->config['request'];
	}

	/** @return string  */
	public function get(): string
	{
		return $this->finalOutput;
	}

	/**
	 * @param string $output
	 * @return ResponseInterface
	 */
	public function set(string $output): ResponseInterface
	{
		$this->finalOutput = $output;

		return $this;
	}

	/**
	 * @param string $output
	 * @return ResponseInterface
	 */
	public function append(string $output): ResponseInterface
	{
		$this->finalOutput .= $output;

		return $this;
	}

	/**
	 * @param string|null $output
	 * @return void
	 */
	public function display(string $output = null): void
	{
		$this->sendHeader();

		echo ($output) ? $output : $this->finalOutput;

		$this->exit(0);
	}

	/** @return void  */
	protected function sendHeader(): void
	{
		if (!$this->config['request']->isCli()) {
			foreach ($this->cookies as $record) {
				setcookie($record[0], $record[1], $record[2], $record[3], $record[4], $record[5], $record[6]);
			}

			foreach ($this->headers as $record) {
				header($record[0], $record[1]);
			}
		}
	}

	/**
	 * @param string $string
	 * @param bool $replace
	 * @return ResponseInterface
	 */
	public function header(string $string, bool $replace = true): ResponseInterface
	{
		$this->headers[] = [$string, $replace];

		return $this;
	}

	/**
	 * @param [int|string] $code
	 * @return ResponseInterface
	 */
	public function responseCode($code): ResponseInterface
	{
		$statusCode = null;

		/* let's flip the status codes so we can search them as text if a number isn't sent in */
		if (isset($this->statusCodes[$code])) {
			$statusCode = $code . ' ' . $this->statusCodes[$code];
		} else {
			$inverse = array_change_key_case(array_flip($this->statusCodes), CASE_LOWER);
			$lcCode = strtolower((string)$code);

			if (isset($inverse[$lcCode])) {
				$statusCode = $inverse[$lcCode] . ' ' . $this->statusCodes[$inverse[$lcCode]];
			}
		}

		if (!$statusCode) {
			throw new UnknownResponseCode($code);
		}

		$this->header('HTTP/1.0 ' . $statusCode, true);

		return $this;
	}

	/**
	 * @param string $mimeType
	 * @param string|null $charset
	 * @return ResponseInterface
	 */
	public function contentType(string $mimeType, string $charset = null): ResponseInterface
	{
		/* if it's a extension then determine the mime type from that */
		if (strpos($mimeType, '/') === false) {
			$extension = ltrim($mimeType, '.');

			/* Is this extension supported? */
			if (isset($this->mimesTypes[$extension])) {
				$mimeType = $this->mimesTypes[$extension];
			} else {
				throw new UnknownContentType($mimeType);
			}
		}

		if (!$charset) {
			$charset = $this->charSet;
		}

		$this->header('Content-Type: ' . $mimeType . '; charset=' . $charset, true);

		return $this;
	}

	/**
	 * Set cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param	string|mixed[]	$name		Cookie name or an array containing parameters
	 * @param	string		$value		Cookie value
	 * @param	int		$expire		Cookie expiration time in seconds
	 * @param	string		$domain		Cookie domain (e.g.: '.yourdomain.com')
	 * @param	string		$path		Cookie path (default: '/')
	 * @param	string		$prefix		Cookie name prefix
	 * @param	bool		$secure		Whether to only transfer cookies via SSL
	 * @param	bool		$httponly	Whether to only makes the cookie accessible via HTTP (no javascript)
	 * @return ResponseInterface
	 */
	public function setCookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = null, $httponly = null): ResponseInterface
	{
		if (is_array($name)) {
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item) {
				if (isset($name[$item])) {
					$$item = $name[$item];
				}
			}
		}

		if ($prefix === '' && $this->config['cookie prefix'] !== '') {
			$prefix = $this->config['cookie prefix'];
		}

		if ($domain == '' && $this->config['cookie domain'] !== '') {
			$domain = $this->config['cookie domain'];
		}

		if ($path === '/' && $this->config['cookie path'] !== '/') {
			$path = $this->config['cookie path'];
		}

		$secure = ($secure === null && $this->config['cookie secure'] !== null) ? (bool) $this->config['cookie secure'] : (bool) $secure;

		$httponly = ($httponly === null && $this->config['cookie httponly'] !== null) ? (bool) $this->config['cookie httponly'] : (bool) $httponly;

		if (!is_numeric($expire)) {
			$expire = time() - 86500;
		} else {
			$expire = ($expire > 0) ? time() + $expire : 0;
		}

		$this->cookies[$prefix . $name] = [$prefix . $name, $value, $expire, $path, $domain, $secure, $httponly];

		return $this;
	}

	/**
	 * @param string $name
	 * @param string $domain
	 * @param string $path
	 * @param string $prefix
	 * @return void
	 */
	public function deleteCookie(string $name, string $domain = '', string $path = '/', string $prefix = ''): ResponseInterface
	{
		return $this->setCookie($name, '', '', $domain, $path, $prefix);
	}

	/* redirect - cuz you always need one */
	public function redirect(string $url = '/'): void
	{
		/* send redirect header */
		header('Location: ' . $url);

		/* exit */
		$this->exit(0);
	} /* end redirect() */

	/**
	 * @param int $status
	 * @return void
	 */
	public function exit(int $status = 0): void
	{
		exit($status);
	}
} /* end class */

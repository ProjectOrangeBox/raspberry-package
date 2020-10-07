<?php

namespace projectorangebox\request;

use projectorangebox\log\LoggerTrait;

class Request implements RequestInterface
{
	use LoggerTrait;

	protected $config = [];
	protected $server = [];
	protected $request = [];
	protected $headers = [];
	protected $baseUrl = '';
	protected $requestMethod = '';
	protected $uri = '';
	protected $segments = [];
	protected $isAjax = false;
	protected $isCli = false;
	protected $isHttps = false;
	protected $argv = [];
	protected $get = [];
	protected $post = [];
	protected $cookies = [];
	protected $env = [];
	protected $ipAddress = '';
	protected $arrayChangeKeyCase = null;
	protected $stream = [];
	protected $files = [];

	/**
	 * @param array $config
	 * @return void
	 * @throws Exception
	 */
	public function __construct(array $config)
	{
		$this->log('info', 'Request::__construct');

		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/Config.php', $config);

		$this->headers = [];

		$this->ipAddress = $config['ip address'] ?? '';

		$this->arrayChangeKeyCase = $config['change key case'] ?? \CASE_LOWER;

		$this->server = $config['server'] ?? array_change_key_case($_SERVER, $this->arrayChangeKeyCase);

		$this->cookies = $config['cookies'] ?? array_change_key_case($_COOKIE, $this->arrayChangeKeyCase);

		$this->files = $config['files'] ?? array_change_key_case($_FILES, $this->arrayChangeKeyCase);

		$this->get = $config['get'] ?? array_change_key_case($_GET, $this->arrayChangeKeyCase);

		$this->post = $config['post'] ?? array_change_key_case($_POST, $this->arrayChangeKeyCase);

		$this->env = $config['env'] ?? array_change_key_case($_ENV, $this->arrayChangeKeyCase);

		/* capture the stream */
		parse_str(file_get_contents('php://input'), $this->stream);

		if ($config['request']) {
			$this->request = $config['request'];
		} elseif ($this->stream) {
			$this->request = array_change_key_case($this->stream, $this->arrayChangeKeyCase);
		} else {
			$this->request = $this->post;
		}

		$this->isCli = $config['isCli'] ?? (php_sapi_name() == 'cli');

		$this->isHttps = $config['isHttps'] ?? (isset($this->server['https']) && $this->server['https'] == 'on');

		$this->isAjax = $config['isAjax'] ?? (isset($this->server['http_x_requested_with']) && strtolower($this->server['http_x_requested_with']) === 'xmlhttprequest') ? true : false;

		/* what's our base url */
		$this->baseUrl = $config['baseUrl'] ?? trim($this->server['http_host'] . dirname($this->server['script_name']), '/');

		/* get the http request method */
		$this->requestMethod = $config['requestMethod'] ?? (($this->isCli) ? 'cli' : strtolower($this->server['request_method']));

		$this->argv = $config['argv'] ?? $this->server['argv'];

		/* determine uri */
		if (isset($config['uri'])) {
			$uri = $config['uri'];
		} else {
			/* get the uri (uniform resource identifier) */
			if ($this->isCli) {
				/* shift off index.php */
				array_shift($this->argv);

				$uri = ltrim(trim(implode(' ', $this->argv)), '/');
			} else {
				$allow = $config['allow'] ?? 'A BCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/0_-.+';

				$uri = trim(urldecode(substr(parse_url($this->server['request_uri'], PHP_URL_PATH), strlen(dirname($this->server['script_name'])))), '/');

				/* filter out NOT in allow */
				$uri = preg_replace("/[^" . preg_quote($allow, '/') . "]/i", '', $uri);
			}
		}

		$this->uri = trim($uri, '/');

		/* get the uri pieces */
		$this->segments = explode('/', trim($this->uri, '/'));
	}

	/**
	 * Method set
	 *
	 * @param string $name request property
	 * @param mixed $value replace with value
	 *
	 * @return RequestInterface
	 */
	public function set(string $name, $value): RequestInterface
	{
		if (property_exists($this, $name)) {
			$this->$name = $value;
		}

		return $this;
	}

	/** @return bool  */
	public function isAjax(): bool
	{
		return $this->isAjax;
	}

	/** @return bool  */
	public function isCli(): bool
	{
		return $this->isCli;
	}

	/** @return bool  */
	public function isHttps(): bool
	{
		return $this->isHttps;
	}

	/** @return string  */
	public function baseUrl(): string
	{
		return $this->baseUrl;
	}

	/** @return string  */
	public function requestMethod(): string
	{
		return $this->requestMethod;
	}

	/** @return string  */
	public function uri(): string
	{
		return '/' . $this->uri;
	}

	/** @return array  */
	public function segments(): array
	{
		return $this->segments;
	}

	/**
	 * @param int $index
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function segment(int $index, $default = null) /* mixed */
	{
		return $this->fetchFromArray($this->segments, $index, $default);
	}

	/**
	 * @param string|null $name
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function server(string $name = null, $default = null) /* mixed */
	{
		return $this->fetchFromArray($this->server, $name, $default);
	}

	/**
	 * @param string|null $name
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function cookie(string $name = null, $default = null) /* mixed */
	{
		return $this->fetchFromArray($this->cookies, $name, $default);
	}

	/**
	 * @param string|null $name
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function request(string $name = null, $default = null) /* mixed */
	{
		return $this->fetchFromArray($this->request, $name, $default);
	}

	/**
	 * @param string|null $name
	 * @return array
	 */
	public function file(string $name = null): array
	{
		return $this->fetchFromArray($this->files, $name, []);
	}

	/**
	 * @param string|null $name
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get(string $name = null, $default = null) /* mixed */
	{
		return $this->fetchFromArray($this->get, $name, $default);
	}

	/**
	 * @param string|null $name
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function post(string $name = null, $default = null) /* mixed */
	{
		return $this->fetchFromArray($this->post, $name, $default);
	}

	/**
	 * @param string|null $name
	 * @return mixed
	 */
	public function header(string $name = null) /* mixed */
	{
		if (!$this->headers) {
			if (function_exists('apache_request_headers')) {
				$this->headers = apache_request_headers();
			} else {
				foreach ($this->server as $key => $value) {
					if (substr(strtolower($key), 0, 5) == 'http_') {
						// Take SOME_HEADER and turn it into Some-Header
						$this->headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
					}
				}
			}

			$this->headers = array_change_key_case($this->headers, $this->arrayChangeKeyCase);
		}

		$key = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($name))));

		return $this->fetchFromArray($this->headers, $key, null);
	}

	/** @return string  */
	public function requestType(): string
	{
		return explode(',', $this->header('Accept'))[0];
	}

	/** @return string  */
	public function ipAddress(): string
	{
		if ($this->ipAddress !== '') {
			return $this->ipAddress;
		}

		$proxy_ips = $this->config['proxy_ips'];

		if (!empty($proxy_ips) && !is_array($proxy_ips)) {
			$proxy_ips = explode(',', str_replace(' ', '', $proxy_ips));
		}

		$this->ipAddress = $this->server('remote_addr');

		if ($proxy_ips) {
			foreach (array('http_x_forwarded_for', 'http_client_ip', 'http_x_client_ip', 'http_x_cluster_client_ip') as $header) {
				$spoof = (string)$this->server($header);

				if ($spoof !== NULL) {
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf($spoof, '%[^,]', $spoof);

					if (!$this->isValidIP($spoof)) {
						$spoof = NULL;
					} else {
						break;
					}
				}
			}

			if ($spoof) {
				for ($i = 0, $c = count($proxy_ips); $i < $c; $i++) {
					// Check if we have an IP address or a subnet
					if (strpos($proxy_ips[$i], '/') === FALSE) {
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxy_ips[$i] === $this->ipAddress) {
							$this->ipAddress = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					isset($separator) or $separator = $this->isValidIP($this->ipAddress, 'ipv6') ? ':' : '.';

					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos($proxy_ips[$i], $separator) === FALSE) {
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
					if (!isset($ip, $sprintf)) {
						if ($separator === ':') {
							// Make sure we're have the "full" IPv6 format
							$ip = explode(
								':',
								str_replace(
									'::',
									str_repeat(':', 9 - substr_count($this->ipAddress, ':')),
									$this->ipAddress
								)
							);

							for ($j = 0; $j < 8; $j++) {
								$ip[$j] = intval($ip[$j], 16);
							}

							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						} else {
							$ip = explode('.', $this->ipAddress);
							$sprintf = '%08b%08b%08b%08b';
						}

						$ip = vsprintf($sprintf, $ip);
					}

					// Split the netmask length off the network address
					sscanf($proxy_ips[$i], '%[^/]/%d', $netaddr, $masklen);

					// Again, an IPv6 address is most likely in a compressed form
					if ($separator === ':') {
						$netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));
						for ($j = 0; $j < 8; $j++) {
							$netaddr[$j] = intval($netaddr[$j], 16);
						}
					} else {
						$netaddr = explode('.', $netaddr);
					}

					// Convert to binary and finally compare
					if (strncmp($ip, vsprintf($sprintf, $netaddr), $masklen) === 0) {
						$this->ipAddress = $spoof;
						break;
					}
				}
			}
		}

		if (!$this->isValidIP($this->ipAddress)) {
			return $this->ipAddress = '0.0.0.0';
		}

		return $this->ipAddress;
	}

	/**
	 * @param string $ip
	 * @param string $which
	 * @return bool
	 */
	public function isValidIP(string $ip, string $which = ''): bool
	{
		switch (strtolower($which)) {
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}

	/**
	 * @param string|null $name
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function env(string $name = null, $default = null) /* mixed */
	{
		return $this->fetchFromArray($this->env, $name, $default);
	}

	/**
	 * @param array $array
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	protected function fetchFromArray(array $array, string $key = null, $default = null) /* mixed */
	{
		if ($key == null) {
			$return = $array;
		} else {
			$return = $array[$this->changeKeyCase($key)] ?? $default;
		}

		return $return;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function changeKeyCase(string $key): string
	{
		switch ($this->arrayChangeKeyCase) {
			case \CASE_UPPER:
				$key = \strtoupper($key);
				break;
			case \CASE_LOWER;
				$key = \strtolower($key);
				break;
		}

		return $key;
	}
} /* end class */

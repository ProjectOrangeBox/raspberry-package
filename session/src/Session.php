<?php

namespace projectorangebox\session;

use projectorangebox\session\Flashdata;
use projectorangebox\session\SessionInterface;
use projectorangebox\session\FlashdataInterface;
use SessionHandlerInterface; /* php interface */

class Session implements SessionInterface
{
	/* pass thru */
	public $flashData = null;

	protected $config = [];

	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/DefaultConfig.php', $config);

		$this->configure();

		$handler = $this->config['handler'];

		$sessionHandler = new $handler($this->config);

		mustBe($sessionHandler, SessionHandlerInterface::class);

		session_set_save_handler(
			array($sessionHandler, 'open'),
			array($sessionHandler, 'close'),
			array($sessionHandler, 'read'),
			array($sessionHandler, 'write'),
			array($sessionHandler, 'destroy'),
			array($sessionHandler, 'gc')
		);

		register_shutdown_function('session_write_close');

		if (session_status() != PHP_SESSION_ACTIVE) {
			session_start();
		}

		$this->regenerate();

		$this->flashData = new Flashdata($this, $this->config);

		mustBe($this->flashData, FlashdataInterface::class);

		/* passed in for testing */
		if (isset($this->config['session']) && is_array($this->config['session'])) {
			$_SESSION = $this->config['session'];
		}
	}

	public function destroy(): bool
	{
		$_SESSION = [];

		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();

			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}

		return session_destroy();
	}

	public function all(): array
	{
		return $_SESSION;
	}

	public function get(string $key = NULL, $default = NULL) /* mixed */
	{
		return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default;
	}

	public function set(string $key, $value = NULL): SessionInterface
	{
		if ($value === NULL) {
			unset($_SESSION[$key]);
		} else {
			$_SESSION[$key] = $value;
		}

		return $this;
	}

	public function remove(string $key): SessionInterface
	{
		unset($_SESSION[$key]);

		return $this;
	}

	/* protected */

	protected function configure()
	{
		// Additional Security
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_strict_mode', 1);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);

		$current = session_get_cookie_params();

		$lifetime = (int)($this->config['lifetime'] ?: $current['lifetime']);
		$path     = $this->config['path'] ?: $current['path'];
		$domain   = $this->config['domain'] ?: $current['domain'];
		$secure   = (bool)$this->config['secure'];
		$httponly = (bool)$this->config['httponly'];

		session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

		$name = $this->config['name'] ?: 'a' . md5(__FILE__);

		session_name($name);

		session_cache_limiter($this->config['cache_limiter']);
	}

	protected function regenerate()
	{
		/*
		 * Should we try to regenerate a new id?
		 * WARNING! don't do this on a ajax request!
		 */
		if (!$this->config['isAjax']) {
			if (mt_rand(1, 100) >= $this->config['regenerate percent']) {
				session_regenerate_id(true);
			}
		}
	}
} /* end session class */
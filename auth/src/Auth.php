<?php

namespace projectorangebox\auth;

use projectorangebox\auth\User;
use projectorangebox\log\LoggerTrait;
use projectorangebox\events\EventsTrait;
use projectorangebox\session\SessionInterface;

class Auth implements AuthInterface
{
	use LoggerTrait;
	use EventsTrait;

	const ADMINROLEID = 1;
	const EVERYONEROLEID = 2;
	const NOBODYUSERID = 1;

	protected $config = [];
	protected $session = null;
	protected $sessionKey = 'user::data';
	protected $error = null;
	protected $userModel = null;
	protected $isCli = false;

	public function __construct(array $config)
	{
		$this->config = $config;

		$this->isCli = $config['isCli'] ?? $this->isCli;

		$this->session = $config['sessionService'];

		mustBe($this->session, SessionInterface::class);

		$this->userModel = new User([]);

		/* We all start off as nobody in life... */
		$this->switch2nobody();

		/* Are we in GUI mode? */
		if (!$this->isCli) {
			/* yes - is there a user id in the session? */
			$userPrimaryKey = $this->session->get($this->sessionKey);

			if (!empty($userPrimaryKey)) {
				/**
				 * refresh the user based on the user identifier
				 * but don't save to the session
				 * because we already loaded it from the session
				 */
				$this->refreshUserdata($userPrimaryKey, false);
			}
		}

		$this->log('debug', 'Auth Class Initialized');
	}
	/**
	 *
	 * Switch the current user to nobody
	 *
	 * @access public
	 *
	 * @return Auth
	 *
	 */
	public function switch2nobody(): Auth
	{
		$this->refreshUserdata(self::NOBODYUSERID, false);

		return $this;
	}

	/**
	 *
	 * Perform a login using email and password
	 *
	 * @access public
	 *
	 * @param string $userPrimaryKey
	 * @param string $password
	 *
	 * @return Bool
	 *
	 */
	public function login(string $userPrimaryKey, string $password): Bool
	{
		$success = $this->_login($userPrimaryKey, $password);

		$this->triggerEvent('auth.login', $userPrimaryKey, $success);

		$this->log('debug', 'Auth Class login');

		return $success; /* boolean */
	}

	/**
	 *
	 * Perform a logout
	 *
	 * @access public
	 *
	 * @return Bool
	 *
	 */
	public function logout(): Bool
	{
		$this->log('debug', 'Auth Class logout');

		$success = true;

		$this->triggerEvent('auth.logout', $success);

		if ($success) {
			$this->switch2nobody();
			$this->session->set_userdata([$this->sessionKey => '']);
		}

		return $success;
	}

	/**
	 *
	 * Refresh the current user profile based on a user id
	 * you can optionally save it to the current session
	 *
	 * @access public
	 *
	 * @param String $userPrimaryKey
	 * @param Bool $save_session true
	 *
	 * @return String
	 *
	 */
	public function refreshUserdata(String $userPrimaryKey, Bool $save_session): Void
	{
		$this->log('debug', 'Auth::refreshUserdata::' . $userPrimaryKey);

		if (empty($userPrimaryKey)) {
			throw new \Exception('Auth session refresh user identifier empty.');
		}

		$profile = $this->userModel->get_by_primary_ignore_read_role($userPrimaryKey);

		if ((int)$profile->is_active === 1 && $profile instanceof O_user_entity) {
			/* no real need to have this floating around */
			unset($profile->password);

			/* Attach profile object as user "service" */
			ci('user', $profile);

			/* should we save this profile id in the session? */
			if ($save_session) {
				$this->session->set_userdata([$this->sessionKey => $profile->id]);
			}
		}

		$this->log('debug', 'Auth Class Refreshed');
	}

	/**
	 *
	 * Do actual login with multiple levels of validation
	 *
	 * @access protected
	 *
	 * @param String $login
	 * @param String $password
	 *
	 * @return Bool
	 *
	 */
	protected function _login(String $login, String $password): Bool
	{
		/* Does login and password contain anything empty values are NOT permitted for any reason */
		if ((strlen(trim($login)) == 0) or (strlen(trim($password)) == 0)) {
			$this->error = $this->config['empty fields error'];
			$this->log('debug', 'auth->user ' . $this->config['empty fields error']);
			return false;
		}

		/* Run trigger */
		$this->triggerEvent('user.login.init', $login);

		/* Try to locate a user by there email */
		if (!$user = $this->userModel->get_user_by_email($login)) {
			$this->log('debug', 'Auth Get User by email returned NULL');
			$this->error = $this->config['general failure error'];
			return false;
		}

		/* Did we get a instance of orange user entity? */
		if (!($user instanceof O_user_entity)) {
			$this->log('debug', 'Auth $user not an object');
			$this->error = $this->config['general failure error'];
			return false;
		}

		/* Is the user id 0? There is not user 0 */
		if ((int) $user->id === 0) {
			$this->log('debug', 'Auth $user->id is 0 (no users id is 0)');
			$this->error = $this->config['general failure error'];
			return false;
		}

		/* Verify the Password entered with what's in the user object */
		if (password_verify($password, $user->password) !== true) {
			$this->triggerEvent('user.login.fail', $login);
			$this->log('debug', 'auth->user Incorrect Login and/or Password');
			$this->error = $this->config['general failure error'];
			return false;
		}

		/* Is this user activated? */
		if ((int) $user->is_active == 0) {
			$this->triggerEvent('user.login.in active', $login);
			$this->log('debug', 'auth->user Incorrect Login and/or Password');
			$this->error = $this->config['general failure error'];
			return false;
		}

		/* ok they are good refresh the user and save to the session */
		$this->refreshUserdata($user->id, true);

		return true;
	}
} /* end class */

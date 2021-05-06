<?php

namespace projectorangebox\auth;

use projectorangebox\auth\Roles;
use projectorangebox\auth\UserMeta;
use projectorangebox\auth\Resources;
use Delight\Auth\Auth as DelightAuth;
use projectorangebox\auth\exceptions\MissingConfig;

/*
 * https://github.com/delight-im/PHP-Auth
 */

class User implements UserInterface
{
	public $auth = null;
	public $roles = null;
	public $resources = null;
	public $userMeta = null;

	protected $acl = [];
	protected $config = [];
	protected $lastError = '';
	protected $userId = 0;
	protected $loadedACL = [];

	public function __construct(array $config)
	{
		$this->config = mergeConfig(__DIR__ . '/DefaultConfig.php', $config);

		if (!isset($config['connections']['db']) && !isset($config['connections']['db']['auth'])) {
			throw new MissingConfig('Auth connection is not set in connections config.');
		}

		$authConnection = $config['connections']['db']['auth'];

		$this->auth = new DelightAuth(new \PDO('mysql:dbname=' . $authConnection['database_name'] . ';host=' . $authConnection['server'] . ';charset=utf8mb4', $authConnection['username'], $authConnection['password']));

		$this->userId = ($this->loggedIn()) ? $this->id() : 0;

		$this->config['userService'] = $this;
		$config['userId'] = &$this->userId;

		$this->roles = new Roles($config);
		$this->resources = new Resources($config);
		$this->userMeta = new UserMeta($config);

		$this->refresh($this->userId);
	}

	public function getLasterror(): string
	{
		return $this->lastError;
	}

	public function login(string $email, string $password): bool
	{
		$success = false;

		$this->lastError = '';

		try {
			$this->auth->login($email, $password);

			$this->userId = $this->id();

			$this->refresh($this->userId);

			$success = true;
		} catch (\Delight\Auth\InvalidEmailException $e) {
			$this->lastError = 'Username or Password Incorrect.';
		} catch (\Delight\Auth\InvalidPasswordException $e) {
			$this->lastError = 'Username or Password Incorrect.';
		} catch (\Delight\Auth\EmailNotVerifiedException $e) {
			$this->lastError = 'Please verify your email address first.';
		} catch (\Delight\Auth\TooManyRequestsException $e) {
			$this->lastError = 'Too many requests please try again in a few minutes.';
		}

		return $success;
	}

	public function logout(): bool
	{
		$this->auth->logOut();

		$this->refresh(0);

		return true;
	}

	public function id(): int
	{
		return ($this->loggedIn()) ? $this->auth->getUserId() : 0;
	}

	public function email(): string
	{
		return ($this->loggedIn()) ? $this->auth->getEmail() : '';
	}

	public function username(): string
	{
		return ($this->loggedIn()) ? $this->auth->getUsername() : $this->config['auth']['nobody username'];
	}

	public function userMeta(): array
	{
		return $this->userMeta->read($this->userId);
	}

	public function loggedIn(): bool
	{
		return $this->auth->isLoggedIn();
	}

	public function refresh(int $userId): bool
	{
		$this->acl = $this->getAcl($userId);

		$this->userMeta->refresh($userId);

		return true;
	}

	public function hasRole(string $role): bool
	{
		return $this->_has('roles', $role);
	}

	public function hasRoles(array $roles): bool
	{
		foreach ($roles as $role) {
			if (!$this->_has('roles', $role)) {
				return false;
			}
		}

		return true;
	}

	public function hasOneRoleOf(array $roles): bool
	{
		foreach ($roles as $role) {
			if ($this->_has('roles', $role)) {
				return true;
			}
		}

		return false;
	}

	public function hasResource(string $resource): bool
	{
		return $this->_has('resources', $resource);
	}

	public function hasResources(array $resources): bool
	{
		foreach ($resources as $resource) {
			if (!$this->_has('resources', $resource)) {
				return false;
			}
		}

		return true;
	}

	public function hasOneResourceOf(array $resources): bool
	{
		foreach ($resources as $resource) {
			if ($this->_has('resources', $resource)) {
				return true;
			}
		}

		return false;
	}

	/* wrappers */

	public function can(string $resource): bool
	{
		return $this->hasResource($resource);
	}

	public function cannot(string $resource): bool
	{
		return !$this->hasResource($resource);
	}

	/* get a list of roles or resources */

	public function roles(): array
	{
		return $this->acl['roles'];
	}

	public function resources(): array
	{
		return $this->acl['resources'];
	}

	/* internal */
	protected function _has(string $arrayKey, string $match): bool
	{
		foreach ($this->acl[$arrayKey] as $value) {
			if ($value == $match) {
				return true;
			}
		}

		return false;
	}

	protected function getAcl(int $userId, string $arrayKey = null): array
	{
		if (!isset($this->loadedACL[$userId])) {
			$this->loadedACL[$userId] = ['roles' => [], 'resources' => []];

			$sql = "select
				`" . $this->config['auth']['role table'] . "`.`id` `role_id`,
				`" . $this->config['auth']['role table'] . "`.`name` `role_name`,
				`" . $this->config['auth']['resource table'] . "`.`id` `resource_id`,
				`" . $this->config['auth']['resource table'] . "`.`key` `resource`
				from " . $this->config['auth']['user role table'] . "
				left join " . $this->config['auth']['role table'] . " on " . $this->config['auth']['role table'] . ".id = " . $this->config['auth']['user role table'] . ".role_id
				left join " . $this->config['auth']['role resource table'] . " on " . $this->config['auth']['role resource table'] . ".role_id = " . $this->config['auth']['role table'] . ".id
				left join " . $this->config['auth']['resource table'] . " on " . $this->config['auth']['resource table'] . ".id = " . $this->config['auth']['role resource table'] . ".resource_id
				where " . $this->config['auth']['user role table'] . ".user_id = " . $userId;

			foreach ($this->config['db']->query($sql)->fetchAll() as $dbr) {
				if (isset($dbr['role_name']) && !empty($dbr['role_name'])) {
					$this->loadedACL[$userId]['roles'][(int) $dbr['role_id']] = $dbr['role_name'];
				}
				if (isset($dbr['resource']) && !empty($dbr['resource'])) {
					$this->loadedACL[$userId]['resources'][(int) $dbr['resource_id']] = $dbr['resource'];
				}
			}
		}

		return ($arrayKey && isset($this->loadedACL[$userId][$arrayKey])) ? $this->loadedACL[$userId][$arrayKey] : $this->loadedACL[$userId];
	}
} /* end class */

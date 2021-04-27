<?php

namespace projectorangebox\auth;

class User implements UserInterface
{
	protected $config = [];

	public function __construct(array $config)
	{
		$this->config = array_replace(require __DIR__ . '/DefaultConfig.php', $config);
	}

	public function isLoggedIn(): bool
	{
	}

	public function isNobody(): bool
	{
	}

	public function roles(): array
	{
	}

	public function hasRole(int $roleId): bool
	{
	}

	public function hasRoles(array $roles): bool
	{
	}

	public function hasOneRoleOf(array $roles): bool
	{
	}

	public function hasPermissions(array $permissions): bool
	{
	}

	public function hasOnePermissionOf(array $permissions): bool
	{
	}

	public function hasPermission(string $permission): bool
	{
	}

	public function can(string $resource): bool
	{
	}

	public function cannot(string $permission): bool
	{
	}

	public function isAdmin(): bool
	{
	}


	public function permissions(): array
	{
	}

	public function logout(): bool
	{
	}

	public function username(): string
	{
	}

	public function email(): string
	{
	}

	public function id(): string
	{
	}

	public function remember(): UserInterface
	{
	}

	public function forget(): UserInterface
	{
	}

	public function changeEmail($email)
	{
	}
} /* end class */

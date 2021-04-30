<?php

namespace projectorangebox\auth;

interface UserInterface
{
	public function __construct(array $config);
	public function getLasterror(): string;
	public function login(string $email, string $password): bool;
	public function logout(): bool;
	public function id(): int;
	public function email(): string;
	public function username(): string;
	public function userMeta(): array;
	public function loggedIn(): bool;
	public function refresh(int $userId): bool;
	public function hasRole(string $role): bool;
	public function hasRoles(array $roles): bool;
	public function hasOneRoleOf(array $roles): bool;
	public function hasResource(string $resource): bool;
	public function hasResources(array $resources): bool;
	public function hasOneResourceOf(array $resources): bool;
	public function can(string $resource): bool;
	public function cannot(string $resource): bool;
}

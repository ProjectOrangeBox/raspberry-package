<?php

namespace projectorangebox\session;

interface SessionInterface
{
	public function __construct(array $config);
	public function destroy(): bool;
	public function all(): array;
	public function get(string $key = NULL, $default = NULL); /* mixed */
	public function set(string $key, $value = NULL): SessionInterface;
	public function remove(string $key): SessionInterface;
}

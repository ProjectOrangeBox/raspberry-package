<?php

namespace projectorangebox\session;

interface FlashdataInterface
{
	public function __construct(SessionInterface $session, array $config);
	public function get(string $key = NULL, $default = NULL); /* mixed */
	public function keep(string $key = NULL, $default = NULL); /* mixed */
	public function set(string $key, $value = NULL, int $seconds = null): SessionInterface; /* return parent */
	public function all(): array;
}

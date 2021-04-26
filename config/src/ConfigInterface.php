<?php

namespace projectorangebox\config;

interface ConfigInterface
{
	public function get(string $name,/* mixed */ $default = null, bool $required = false); /* mixed */
	public function set(string $name,/* mixed */ $value = null): ConfigInterface;
	public function collect(): array; /* return all loaded configuration */
}

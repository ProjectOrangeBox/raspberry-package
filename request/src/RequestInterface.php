<?php

namespace projectorangebox\request;

interface RequestInterface
{
	public function __construct(array $config);
	public function set(string $name, $value): RequestInterface;
	public function isCli(): bool;
	public function isAjax(): bool;
	public function isHttps(): bool;
	public function baseUrl(): string;
	public function requestMethod(): string;
	public function uri(): string;
	public function segments(): array;
	public function segment(int $index, $default = null); /* mixed */
	public function server(string $name = null, $default = null); /* mixed */
	public function request(string $name = null, $default = null); /* mixed */
	public function post(string $name = null, $default = null); /* mixed */
	public function get(string $name = null, $default = null); /* mixed */
	public function file(string $name = null): array;
	public function cookie(string $name = null, $default = null); /* mixed */
	public function env(string $name = null, $default = null); /* mixed */
	public function ipAddress(): string;
	public function isValidIP(string $ip, string $which = ''): bool;
	public function requestType(): string;
}

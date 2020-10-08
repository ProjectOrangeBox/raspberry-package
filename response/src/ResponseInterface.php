<?php

namespace projectorangebox\response;

interface ResponseInterface
{
	public function get(): string;
	public function set(string $output): ResponseInterface;
	public function append(string $output): ResponseInterface;
	public function display(string $output = null): void;

	public function header(string $string, bool $replace = true): ResponseInterface;
	public function responseCode($code): ResponseInterface; /* text or integer */
	public function contentType(string $mimeType, string $charset = null): ResponseInterface; /* mime type or extension */

	public function exit(int $status = 0): void;
	public function redirect(string $url = '/'): void;

	public function setCookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL): ResponseInterface;
	public function deleteCookie(string $name, string $domain = '', string $path = '/', string $prefix = ''): ResponseInterface;
}

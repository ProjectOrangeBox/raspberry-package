<?php

namespace projectorangebox\middleware;

use projectorangebox\middleware\MiddlewareRequest;
use projectorangebox\middleware\MiddlewareResponse;

interface MiddlewareHandlerInterface
{
	public function __construct(array $config);
	public function request(MiddlewareRequest &$parameters): void;
	public function response(MiddlewareResponse &$parameters): void;
}

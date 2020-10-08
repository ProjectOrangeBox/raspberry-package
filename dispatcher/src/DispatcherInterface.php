<?php

namespace projectorangebox\dispatcher;

use projectorangebox\request\RequestInterface;
use projectorangebox\response\ResponseInterface;

interface DispatcherInterface
{
	public function __construct(array $config);
	public function dispatch(RequestInterface $request): ResponseInterface;
}

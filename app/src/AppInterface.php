<?php

namespace projectorangebox\app;

use projectorangebox\request\RequestInterface;
use projectorangebox\response\ResponseInterface;
use projectorangebox\container\ContainerInterface;

interface AppInterface
{
	/**
	 * __construct
	 *
	 * @param string[] $config configuration array
	 * @return void
	 */
	public function __construct(ContainerInterface $container);

	/**
	 * dispatch
	 *
	 * @param string $uri Uniform Resource Identifier.
	 * @param string $httpMethod Http Method ie. get, put, post, header, delete.
	 * @return void
	 */
	public function dispatch(RequestInterface $request = null): ResponseInterface;

	/**
	 * container
	 */
	static public function container();
}

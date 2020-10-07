<?php

namespace projectorangebox\router;

interface RouterInterface
{
	public function __construct(array $routes);
	public function handle(string $uri, string $httpMethod); /* mixed */
	public function captured(): array;
}

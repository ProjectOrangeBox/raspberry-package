<?php

namespace projectorangebox\middleware;

use projectorangebox\container\ContainerInterface;

interface MiddlewareInterface
{
  public function __construct(ContainerInterface &$container);
  public function __get(string $name);

  /* return false to stop further processing */
  public function request(MiddlewareRequest &$payload);
  public function response(MiddlewareResponse &$payload);
}

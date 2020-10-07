<?php

namespace projectorangebox\middleware;

use projectorangebox\container\ContainerInterface;
use projectorangebox\middleware\MiddlewareRequest;
use projectorangebox\middleware\MiddlewareResponse;

abstract class Middleware implements MiddlewareInterface
{
  protected $container;

  public function __construct(ContainerInterface &$container)
  {
    $this->container = &$container;
  }

  public function __get(string $name)
  {
    return ($this->container->has($name)) ? $this->container->$name : null;
  }

  public function request(MiddlewareRequest &$payload)
  {
  }

  public function response(MiddlewareResponse &$payload)
  {
  }
} /* end class */

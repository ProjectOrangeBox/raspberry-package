<?php

namespace projectorangebox\middleware;

use projectorangebox\request\RequestInterface;
use projectorangebox\response\ResponseInterface;
use projectorangebox\container\ContainerInterface;

abstract class MiddlewarePayload
{
  public $container = null;
  public $request = null;
  public $response = null;

  public function __construct(ContainerInterface $container,  RequestInterface $request, ResponseInterface $response)
  {
    $this->container = &$container;
    $this->request = &$request;
    $this->response = &$response;
  }
}

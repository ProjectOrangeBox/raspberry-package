<?php

namespace projectorangebox\dispatcher;

use projectorangebox\container\ContainerInterface;
use projectorangebox\dispatcher\ControllerInterface;

abstract class Controller implements ControllerInterface
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function __get(string $name)
	{
		return ($this->container->has($name)) ? $this->container->$name : null;
	}
} /* end class */

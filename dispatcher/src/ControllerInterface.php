<?php

namespace projectorangebox\dispatcher;

use projectorangebox\container\ContainerInterface;

interface ControllerInterface
{
	public function __construct(ContainerInterface $container);
} /* end interface */

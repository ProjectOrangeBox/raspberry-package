<?php

namespace projectorangebox\assets\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearAsset extends PearAbstract implements PearInterface
{
	public function render(string $name = null)
	{
		return service('assets')->get($name);
	}
} /* end plugin */

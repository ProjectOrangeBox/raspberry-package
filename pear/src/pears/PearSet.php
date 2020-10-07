<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearSet extends PearAbstract implements PearInterface
{
	public function render(string $key = null, $value = null)
	{
		if ($key) {
			$this->view->data($key, $value);
		}
	}
} /* end plugin */

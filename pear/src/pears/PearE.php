<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearE extends PearAbstract implements PearInterface
{
	public function render($echo, bool $raw = false)
	{
		if (substr($echo, 0, 1) == '@') {
			$echo = $this->view->getData(substr($echo, 1));
		}

		return ($raw) ? $echo : htmlspecialchars($echo, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
	}
} /* end plugin */

<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearHtml extends PearAbstract implements PearInterface
{
	public function render($echo)
	{
		if (substr($echo, 0, 1) == '@') {
			$echo = $this->view->getData(substr($echo, 1));
		}

		return $echo;
	}
} /* end plugin */

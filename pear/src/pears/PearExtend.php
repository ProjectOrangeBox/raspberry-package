<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearExtend extends PearAbstract implements PearInterface
{
	public function render()
	{
		$extended = $this->view->getData('_extended');

		if (empty($extended)) {
			throw new \Exception('You are not extending anything.');
		}

		return $this->view->render($extended);
	}
} /* end plugin */

<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearExtending extends PearAbstract implements PearInterface
{
	public function render(string $view = null)
	{
		if ($view) {
			$extending = $this->view->getData('_extended');

			if (!empty($extending)) {
				throw new \Exception('Your are already extending "' . $extending . '".');
			}

			$this->view->data('_extended', $view);
		}
	}
} /* end plugin */

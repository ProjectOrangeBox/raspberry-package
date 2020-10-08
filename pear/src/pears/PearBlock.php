<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearBlock extends PearAbstract implements PearInterface
{
	public function render(string $key = null, string $mode = 'append')
	{
		if ($key) {
			$fragments = $this->view->getData('_fragments');

			$fragments[] = [$key, $mode];

			$this->view->data('_fragments', $fragments);

			ob_start();
		}
	}
} /* end plugin */

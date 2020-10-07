<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearInclude extends PearAbstract implements PearInterface
{
	public function render(string $view = null, array $data = [], string $key = null)
	{
		$output = '';

		if ($view) {
			$output = $this->view->render($view, $data);

			if (is_string($key)) {
				$this->view->data($key, $output);
			}
		}

		return $output;
	}
} /* end plugin */

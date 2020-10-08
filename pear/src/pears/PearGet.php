<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearGet extends PearAbstract implements PearInterface
{
	public function render(string $key = null, $default = '')
	{
		$data = $this->view->getData($key);

		return (empty($data)) ? $default : $data;
	}
} /* end plugin */

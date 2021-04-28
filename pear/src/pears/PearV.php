<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearV extends PearAbstract implements PearInterface
{
	public function render($variableName, $ifEmpty = '')
	{
		$value = $this->view->getData($variableName);

		return (empty($value)) ? $ifEmpty : $value;
	}
} /* end plugin */

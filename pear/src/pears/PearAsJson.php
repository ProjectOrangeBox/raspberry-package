<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearAsJson extends PearAbstract implements PearInterface
{
	public function render($variableName, $escapeQuotes = false)
	{
		$value = $this->view->getData($variableName);

		$json = ($escapeQuotes) ? str_replace('"', '\"', json_encode($value)) : json_encode($value);

		return (empty($value)) ? '""' : $json;
	}
} /* end plugin */

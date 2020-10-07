<?php

namespace projectorangebox\pear;

use projectorangebox\views\ViewsInterface;

abstract class PearAbstract
{
	public $view = null;

	/**
	 * __construct
	 *
	 * @param ViewsInterface $view
	 */
	public function __construct(ViewsInterface $view)
	{
		$this->view = $view;
	}
} /* end class */

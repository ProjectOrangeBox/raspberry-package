<?php

use projectorangebox\pear\PearInterface;

/**
 * View Plugins
 *
 * "pear" is a view plugin manager
 * to keep masive amounts of PHP out of the views
 * and to make reuseable pieces of code.
 *
 */

class Pear
{
	static public function __callStatic(string $name, array $arguments = [])
	{
		/* using call_user_func_array because arguments is undetermined */
		return call_user_func_array([service('pear')->getPlugin($name), 'render'], $arguments);
	}
} /* end class */

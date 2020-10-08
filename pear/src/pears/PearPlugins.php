<?php

namespace projectorangebox\pear\pears;

use Pear;
use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearPlugins extends PearAbstract implements PearInterface
{
	public function render(array $plugins = [])
	{
		/* load the plug in and throw a error if it's not found */
		foreach ($plugins as $plugin) {
			/* setup default of no parameters */
			$parameters = [];

			/**
			 * do we have parameters if so split them out
			 *
			 * foobar(123,cats)
			 * foobar
			 *
			 */
			if (preg_match('/^(?<plugin>.*?)\((?<parameters>.*?)\)$/', $plugin, $matches)) {
				$plugin = $matches['plugin'];
				$parameters = explode(',', $matches['parameters']);
			}

			pear::__callStatic($plugin, $parameters);
		}
	}
} /* end plugin */

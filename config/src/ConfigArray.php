<?php

namespace projectorangebox\config;

use ConfigAbstract;
use projectorangebox\config\ConfigInterface;

class ConfigArray extends ConfigAbstract implements ConfigInterface
{
	/**
	 * Get a value with default based on dot notation
	 *
	 * @param string $notation
	 * @param mixed $default default if not found
	 * @return mixed
	 */
	public function _get(string $notation,/* mixed */ $default = null) /* mixed */
	{
		$value = $default;

		/* single level */
		if (array_key_exists($notation, $this->config)) {
			$value = $this->config[$notation];
		} else {
			/* multiple levels */
			$segments = explode('.', $notation);

			/* now traverse the array to find the keys */
			$array = $this->config;

			foreach ($segments as $segment) {
				$segment = strtolower($segment);

				if (array_key_exists($segment, $array)) {
					$value = $array = $array[$segment];
				} else {
					$value = $default;
					break;
				}
			}
		}

		return $value;
	}
} /* end class */
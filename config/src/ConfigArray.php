<?php

namespace projectorangebox\config;

use projectorangebox\config\ConfigInterface;

class ConfigFile implements ConfigInterface
{
	protected $config = [];

	/**
	 * __construct
	 *
	 * @param array $config
	 * @return projectorangebox\config\Config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * Return entire configuration array
	 *
	 * @return array
	 */
	public function collect(): array
	{
		return $this->config;
	}

	/**
	 * Get a value with default based on dot notation
	 *
	 * @param string $notation
	 * @param mixed $default default if not found
	 * @return mixed
	 */
	public function get(string $notation,/* mixed */ $default = null) /* mixed */
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

	/**
	 * Set a value based on dot notation
	 *
	 * @param string $notation
	 * @param mixed $value
	 * @return ConfigInterface
	 */
	public function set(string $notation, $value = null): ConfigInterface
	{
		$array = &$this->config;

		foreach (explode('.', $notation) as $step) {
			$step = strtolower($step);

			if (!isset($array[$step])) {
				$array[$step] = [];
			}

			$array = &$array[$step];
		}

		$array = $value;

		return $this;
	}
} /* end class */
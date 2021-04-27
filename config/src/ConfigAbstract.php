<?php

use projectorangebox\config\ConfigInterface;
use projectorangebox\config\exceptions\MissingConfig;

abstract class ConfigAbstract implements ConfigInterface
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
	public function get(string $name, $default = null, bool $required = false)
	{
		$value = $this->_get($name, $default);

		/* get the server config */
		if ($default === null && $value === null && $required == true) {
			throw new MissingConfig($name);
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

	/* place holder for extened classes */
	protected function _get(string $notation,/* mixed */ $default = null) /* mixed */
	{
		return null;
	}
}

<?php

namespace projectorangebox\config;

use FS;
use projectorangebox\config\ConfigInterface;
use projectorangebox\config\exceptions\DirectoryNotFound;

class ConfigFile implements ConfigInterface
{
	protected $config = [];

	/* storage if they are using a config folder */
	protected $configFolder = '';

	/**
	 * __construct
	 *
	 * @param array $config
	 * @return projectorangebox\config\Config
	 */
	public function __construct(array $config)
	{
		$this->config['config'] =  array_replace(require __DIR__ . '/Config.php', $config);

		/* Yes - Lets resolve it once and test it */
		$this->configFolder = FS::resolve(trim($this->config['config']['folder'], '/'));

		if (!is_dir($this->configFolder)) {
			throw new DirectoryNotFound();
		}

		/* seems to be there let's add the trailing slash to make getting the files easier */
		$this->configFolder .= '/';
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

			/* if the config array key is empty maybe they are trying to load a config file? */
			if (!isset($this->config[$segments[0]])) {
				$this->loadFile($segments[0]);
			}

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

	/**
	 * Try to load a config file
	 * use the filename as the root level key and it's contents as the value
	 *
	 * @param string $filename
	 * @return void
	 */
	protected function loadFile(string $filename)
	{
		/* config folder Stored locally already resolved. Done in __construct */
		$file = $this->configFolder . $filename . '.php';

		if (file_exists($file)) {
			$this->config[strtolower($filename)] = require $file;
		}
	}
} /* end class */
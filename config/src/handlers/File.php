<?php

namespace projectorangebox\config\handlers;

use FS;
use projectorangebox\config\ConfigAbstract;
use projectorangebox\config\ConfigInterface;
use projectorangebox\config\exceptions\DirectoryNotFound;

class File extends ConfigAbstract implements ConfigInterface
{
	/* storage if they are using a config folder */
	protected $configFolder = '';

	protected $fileconfigFileLoadedLoaded = [];

	/**
	 * __construct
	 *
	 * @param array $config
	 * @return projectorangebox\config\Config
	 */
	public function __construct(array $config)
	{
		$this->config['config'] =  array_replace(require __DIR__ . '/../config.php', $config);

		/* Yes - Lets resolve it once and test it */
		$this->configFolder = FS::resolve(trim($this->config['config']['folder'], '/'));

		if (!is_dir($this->configFolder)) {
			throw new DirectoryNotFound();
		}

		/* seems to be there let's add the trailing slash to make getting the files easier */
		$this->configFolder .= '/';
	}

	public function get(string $notation,/* mixed */ $default = null) /* mixed */
	{
		list($tablename) = explode('.', strtolower($notation));

		$this->loadFile($tablename);

		return parent::get($notation, $default);
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
		if (!isset($this->configFileLoaded[$filename])) {
			/* config folder Stored locally already resolved. Done in __construct */
			$file = $this->configFolder . $filename . '.php';

			if (file_exists($file)) {
				$this->config[strtolower($filename)] = require $file;
			}

			$this->configFileLoaded[$filename] = true;
		}
	}
} /* end class */
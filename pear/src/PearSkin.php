<?php

namespace projectorangebox\pear;

use projectorangebox\pear\PearInterface;
use projectorangebox\cache\CacheInterface;
use projectorangebox\views\ViewsInterface;
use projectorangebox\pear\PearSkinInterface;
use projectorangebox\pear\exceptions\PearNotFound;
use projectorangebox\dispatcher\exceptions\ClassNotFound;
use projectorangebox\dispatcher\exceptions\MethodNotFound;

class PearSkin implements PearSkinInterface
{
	/**
	 * $config
	 *
	 * @var array[] configuration array
	 */
	protected $config = [];

	/**
	 * $loadPlugins
	 *
	 * @var \projectorangebox\pear\PearInterface[] cache
	 */
	protected $loadPlugins = [];

	/**
	 * Make an array of plugins for easier plugin access
	 *
	 * @var array[] plugins
	 */
	protected $plugins = [];

	/**
	 * __construct
	 *
	 * @param array[] $config
	 * @param ViewsInterface $viewService
	 * @param CacheInterface $cacheService
	 */
	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/config.php', $config);

		$this->plugins = $config['plugins'];

		mustBe($this->config['viewService'], ViewsInterface::class);
	}

	/**
	 * @param string $name
	 * @return PearInterface
	 * @throws Exception
	 */
	public function getPlugin(string $name): PearInterface
	{
		$name = $this->cleanName($name);

		if (!isset($this->loadPlugins[$name])) {
			$this->loadPlugins[$name] = $this->loadPlugin($name);
		}

		return $this->loadPlugins[$name];
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPlugin(string $name): bool
	{
		return isset($this->plugins[$this->cleanName($name)]);
	}

	/** @return array  */
	public function plugins(): array
	{
		return $this->plugins;
	}

	/*
	 * 'PearPlugins' => '\\projectorangebox\\pear\\pear\\PearPlugins'
	 */
	public function addPlugin(string $name, string $namespace): void
	{
		$this->plugins[$this->cleanName($name)] = $namespace;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function cleanName(string $name): string
	{
		return 'Pear' . ucfirst(preg_replace('/^pear/i', '', strtolower($name)));
	}

	/**
	 * @param string $name
	 * @return PearInterface
	 * @throws Exception
	 */
	protected function loadPlugin(string $name): PearInterface
	{
		if (!$this->hasPlugin($name)) {
			throw new PearNotFound($name);
		}

		$namespacedClass = $this->plugins[$name];

		if (!class_exists($namespacedClass, true)) {
			throw new ClassNotFound($namespacedClass);
		}

		$plugin = new $namespacedClass($this->config['viewService']);

		mustBe($plugin, PearInterface::class);

		if (!method_exists($plugin, 'render')) {
			throw new MethodNotFound($namespacedClass . '::render');
		}

		return $plugin;
	}
} /* end class */

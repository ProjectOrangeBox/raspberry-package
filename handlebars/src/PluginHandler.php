<?php

namespace projectorangebox\handlebars;

use FS;
use Closure;
use projectorangebox\handlebars\HandlebarsCache;

class PluginHandler
{
	protected $config = [];
	protected $plugins = [];
	protected $forceCompile = false;

	protected $cache = null;

	public function __construct(array $config)
	{
		$this->config = $config;

		$this->forceCompile = $config['force compile'] ?? false;

		$this->cache = $config['cache'] ?? new HandlebarsCache($this->config);

		$this->plugins = $this->compilePlugins($this->config['plugins']);
	}

	public function getPlugins(): array
	{
		return $this->plugins;
	}

	public function addPlugin(string $name, Closure $closure): void
	{
		$this->plugins[strtolower($name)] = $closure;
	}

	public function addPlugins(array $plugins): void
	{
		foreach ($plugins as $name => $closure) {
			$this->addPlugin($name, $closure);
		}
	}

	protected function compilePlugins(array $pluginFiles): array
	{
		$key = 'plugins';

		$value = $this->cache->get($key);

		if (!$value || $this->forceCompile) {
			$this->cache->save($key, $this->source($pluginFiles));

			$value = $this->cache->get($key);
		}

		return $value;
	}

	protected function source(array $pluginFiles): string
	{
		$combined  = '<?php' . PHP_EOL;
		$combined .= '/* DO NOT MODIFY THIS FILE - Written: ' . date('Y-m-d H:i:s T') . '*/' . PHP_EOL;
		$combined .= PHP_EOL;

		foreach ($pluginFiles as $file) {
			$pluginSource  = php_strip_whitespace(FS::resolve($file, false));
			$pluginSource  = trim(str_replace(['<?php', '<?', '?>'], '', $pluginSource));
			$pluginSource  = trim('/* ' . $file . ' */' . PHP_EOL . $pluginSource) . PHP_EOL . PHP_EOL;

			$combined .= $pluginSource;
		}

		$combined .= 'return $helpers;' . PHP_EOL;

		return $combined;
	}

	public function flushCache(): void
	{
		$this->cache->clean();
	}
} /* end class */

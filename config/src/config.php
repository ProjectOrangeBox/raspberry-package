<?php

namespace projectorangebox\config;

use projectorangebox\config\exceptions\HandlerNotFound;

class Config implements ConfigInterface
{
	protected $handler = null;

	public function __construct(array $config)
	{
		$config = buildConfig($config, ['handler', 'folder'], __DIR__ . '/DefaultConfig.php');

		$handler = '\projectorangebox\config\handlers\\' . $config['handler'];

		if (class_exists($handler, false)) {
			throw new HandlerNotFound($handler);
		}

		$this->handler = new $handler($config);

		mustBe($this->handler, ConfigInterface::class);
	}

	public function get(string $name,/* mixed */ $default = null) /* mixed */
	{
		return $this->handler->get($name, $default);
	}

	public function set(string $name,/* mixed */ $value = null): ConfigInterface
	{
		return $this->handler->set($name, $value);
	}

	public function collect(): array /* return all loaded configuration */
	{
		return $this->handler->collect();
	}
} /* end class */
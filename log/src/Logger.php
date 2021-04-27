<?php

namespace projectorangebox\log;

use projectorangebox\log\LoggerInterface;
use projectorangebox\log\exceptions\HandlerNotFound;

class Logger implements LoggerInterface
{
	public function __construct(array $config)
	{
		$config = buildConfig($config, ['handler'], ['handler' => 'File']);

		$handler = '\projectorangebox\log\handlers\\' . $config['handler'];

		if (class_exists($handler, false)) {
			throw new HandlerNotFound($handler);
		}

		$this->handler = new $handler($config);

		mustBe($this->handler, LoggerInterface::class);
	}

	public function emergency(string $message, array $context = []): bool
	{
		return $this->handler->emergency($message, $context);
	}

	public function alert(string $message, array $context = []): bool
	{
		return $this->handler->alert($message, $context);
	}

	public function critical(string $message, array $context = []): bool
	{
		return $this->handler->critical($message, $context);
	}

	public function error(string $message, array $context = []): bool
	{
		return $this->handler->error($message, $context);
	}

	public function warning(string $message, array $context = []): bool
	{
		return $this->handler->warning($message, $context);
	}

	public function notice(string $message, array $context = []): bool
	{
		return $this->handler->notice($message, $context);
	}

	public function info(string $message, array $context = []): bool
	{
		return $this->handler->info($message, $context);
	}

	public function debug(string $message, array $context = []): bool
	{
		return $this->handler->debug($message, $context);
	}

	public function log(string $level, string $message, array $context = []): bool
	{
		return $this->handler->log($level, $message, $context);
	}
} /* end class */

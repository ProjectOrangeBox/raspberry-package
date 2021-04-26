<?php

namespace projectorangebox\log\handlers;

use projectorangebox\log\LoggerAbstract;
use projectorangebox\log\LoggerInterface;

class Screen extends LoggerAbstract implements LoggerInterface
{
	public function __construct(array $config)
	{
		parent::__construct(array_replace(['threshold' => 0], $config));
	}

	public function log(string $level, string $message, array $context = []): bool
	{
		if ($this->enabled) {
			if ($this->testLevel($level)) {
				$message = $this->format($level, $message);
				$exit = false;

				switch ($level) {
					case 'emergency': // EMERGENCY
					case 'alert':
					case 'critical':
					case 'error': //error
						$color = "\033[31m";
						$exit = true;
						break;
					case 'warning': //warning
						$color = "\033[33m";
						break;
					case 'notice': //notice
						$color = "\033[32m";
						break;
					case 'info': //info
						$color = "\033[36m";
					case 'debug':
						// default console color
						break;
				}

				echo $color;
				echo $message;
				echo "\033[0m";

				if ($exit) {
					exit(1);
				}
			}
		}

		return true;
	}
} /* End of Class */
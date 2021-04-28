<?php

namespace projectorangebox\log;

use projectorangebox\log\LoggerAbstract;
use projectorangebox\log\LoggerInterface;

class LoggerScreen extends LoggerAbstract implements LoggerInterface
{
	public function __construct(array $config)
	{
		parent::__construct(array_replace(['threshold' => 0], $config));
	}

	public function log(string $level, string $message, array $context = []): bool
	{
		if ($this->enabled) {
			if ($this->testLevel($level)) {
				$exit = false;

				switch ($level) {
					case 'emergency': // EMERGENCY
						$color = "\033[35m";
						$exit = true;
						break;
					case 'alert':
						$color = "\033[91m";
						$exit = true;
						break;
					case 'critical':
						$color = "\033[95m";
						$exit = true;
						break;
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

				echo $color; /* turn on color */
				echo $this->format($level, $message);
				echo "\033[0m"; /* turn off color */

				if ($exit) {
					exit(1);
				}
			}
		}

		return true;
	}
} /* End of Class */
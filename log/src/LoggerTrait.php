<?php

namespace projectorangebox\log;

use projectorangebox\log\LoggerInterface;

trait LoggerTrait
{
	protected $_logService = null;

	/* This way we can inject a mock */
	public function setLogService(LoggerInterface $logService): void
	{
		$this->_logService = $logService;
	}

	public function getLogService()
	{
		/* Is log even attached to the container yet? */
		if (!$this->_logService) {
			$container = service();

			/* Is container even attached yet? */
			if ($container !== null) {
				/* ok now look for log */
				if ($container->has('log')) {
					$this->_logService = service('log');
				}
			}
		}

		return ($this->_logService) ? $this->_logService : false;
	}

	public function log(string $logLevel, string $logMsg): void
	{
		if ($this->getLogService()) {
			$this->_logService->log($logLevel, $logMsg);
		}
	}
} /* end class */
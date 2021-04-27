<?php

namespace projectorangebox\log;

use projectorangebox\log\LoggerInterface;

trait LoggerTrait
{
	protected $_logService = null;

	/**
	 * Inject Service
	 * This way we can inject a mock
	 *
	 * @param \projectorangebox\log\LoggerInterface $logService
	 *
	 * @return void
	 */
	public function setLogService(LoggerInterface $logService): void
	{
		$this->_logService = $logService;
	}

	/**
	 * Load the Service
	 *
	 * @return mixed
	 */
	public function getLogService() /* mixed */
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

	/**
	 * Log function
	 *
	 * @param string $logLevel
	 * @param string $logMsg
	 *
	 * @return void
	 */
	public function log(string $logLevel, string $logMsg): void
	{
		if ($service = $this->getLogService()) {
			$service->log($logLevel, $logMsg);
		}
	}
} /* end class */
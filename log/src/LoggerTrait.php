<?php

namespace projectorangebox\log;

use projectorangebox\log\LoggerInterface;

trait LoggerTrait
{
	/* override in parent class using protected $logEnabled = true */
	protected $_logEnabled = false;
	protected $_logEnabledOverridden = false;

	/* override in parent class using protected $logCaptureLevel = 255 */
	protected $_logCaptureLevel = 0;
	protected $_logCaptureLevelOverridden = false;

	/* internal use */
	protected $_logPsrLevels = [
		'EMERGENCY' => 1,
		'ALERT'     => 2,
		'CRITICAL'  => 4,
		'ERROR'     => 8,
		'WARNING'   => 16,
		'NOTICE'    => 32,
		'INFO'      => 64,
		'DEBUG'     => 128,
	];

	protected $_logService = null;

	/* This way we can inject a mock */
	public function setLoggerService(LoggerInterface $logService): void
	{
		$this->_logService = $logService;
	}

	public function setLogCaptureLevel(int $level): void
	{
		$this->_logCaptureLevel = $level;
		$this->_logCaptureLevelOverridden = true;
	}

	public function enableLogging(): void
	{
		$this->_logEnabled = true;
		$this->_logEnabledOverridden = true;
	}

	public function disableLogging(): void
	{
		$this->_logEnabled = false;
		$this->_logEnabledOverridden = true;
	}

	public function log(string $logLevel, string $logMsg): void
	{
		if ($this->_logEnabled()) {
			$logLevel = strtoupper($logLevel);
			$logLevelInt = (array_key_exists($logLevel, $this->_logPsrLevels)) ? $this->_logPsrLevels[$logLevel] : 0;

			if ($this->_logCaptureLevel() & $logLevelInt) {
				$this->_logService->log($logLevel, $logMsg);
			}
		}
	}

	/* protected */
	protected function _logEnabled(): bool
	{
		$log = false;

		if ($this->_hasLoggerService()) {
			/* either in config or on the class */
			$log = ($this->_logEnabledOverridden) ? $this->_logEnabled : ($this->config['log enabled'] === true || $this->logEnabled === true);
		}

		return $log;
	}

	protected function _logCaptureLevel(): int
	{
		if (!$this->_logCaptureLevelOverridden) {
			/* either in config or on the class */
			$this->_logCaptureLevel = $this->config['log capture level'] ?? $this->logCaptureLevel;
		}

		return $this->_logCaptureLevel;
	}

	protected function _hasLoggerService(): bool
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

		return ($this->_logService !== null);
	}
} /* end class */
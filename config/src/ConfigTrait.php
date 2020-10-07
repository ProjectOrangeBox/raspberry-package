<?php

namespace projectorangebox\config;

use projectorangebox\config\ConfigInterface;

trait ConfigTrait
{
	protected $_configService = null;

	/* This way we can inject a mock */
	public function setConfigService(ConfigInterface $configService): void
	{
		$this->_configService = $configService;
	}

	public function getConfigService()
	{
		if (!$this->_configService) {
			$this->_configService = service('config');
		}
	}
	public function getConfig(string $dotNotation, $default = null)
	{
		$this->getConfigService();

		return $this->_configService->get($dotNotation, $default);
	}
}

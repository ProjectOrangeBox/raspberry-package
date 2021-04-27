<?php

namespace projectorangebox\config;

use projectorangebox\config\ConfigInterface;

trait ConfigTrait
{
	protected $_configService = null;

	/**
	 * Inject Service
	 * This way we can inject a mock
	 *
	 * @param \projectorangebox\config\ConfigInterface $configService
	 *
	 * @return void
	 */
	public function setConfigService(ConfigInterface $configService): void
	{
		$this->_configService = $configService;
	}

	/**
	 * Load the Service
	 *
	 * @return mixed
	 */
	public function getConfigService() /* mixed */
	{
		if (!$this->_configService) {
			$this->_configService = service('config');
		}

		return ($this->_configService) ? $this->_configService : false;
	}

	/**
	 * Get a configuration value
	 *
	 * @param string $dotNotation
	 * @param [mixed] $default
	 * @param bool $required
	 *
	 * @return [mixed]
	 */
	public function getConfig(string $dotNotation, $default = null)
	{
		$value = null;

		if ($service = $this->getConfigService()) {
			$value = $service->get($dotNotation, $default);
		}

		return $value;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $dotNotation
	 * @param [type] $value
	 *
	 * @return void
	 */
	public function setConfig(string $dotNotation, $value): void
	{
		if ($service = $this->getConfigService()) {
			$service->set($dotNotation, $value);
		}
	}
} /* end trait */

<?php

namespace projectorangebox\container;

use Closure;
use projectorangebox\container\exceptions\ServiceNotRegistered;

class Container implements ContainerInterface
{
	/**
	 * Registered Services
	 *
	 * @var array
	 */
	protected $registeredServices = [];

	/**
	 * __construct
	 *
	 * @param array $serviceArray array of services
	 */
	public function __construct(array $serviceArray = null)
	{
		if (is_array($serviceArray)) {
			foreach ($serviceArray as $serviceName => $record) {
				$this->register($serviceName, $record[0], ($record[1] ?? true));
			}
		}
	}

	/**
	 * __get
	 *
	 * see get(...)
	 *
	 * @param string $serviceName requested service name
	 * @return mixed
	 */
	public function __get($serviceName)
	{
		return $this->get($serviceName);
	}

	/**
	 * __isset
	 *
	 * see has(...)
	 *
	 * @param string $serviceName does the requested service name exist
	 * @return bool
	 */
	public function __isset($serviceName): bool
	{
		return $this->has($serviceName);
	}

	/**
	 * __set
	 *
	 * see regsiter(...)
	 *
	 * @param string $serviceName Service name to register
	 * @param array $value Closure and boolean to indicate if it's a singleton (true) or factory (false)
	 * @return void
	 */
	public function __set($serviceName, $value): void
	{
		$this->register($serviceName, $value[0], $value[1]);
	}

	/**
	 * __unset
	 *
	 * see remove(...)
	 *
	 * @param string $serviceName service name to remove
	 * @return void
	 */
	public function __unset($serviceName): void
	{
		$this->remove($serviceName);
	}

	/**
	 * Get a PHP object by service name
	 *
	 * @param string $serviceName requested service name
	 * @return mixed
	 */
	public function get(string $serviceName)
	{
		$this->buildServiceName($serviceName);

		/* Is this service even registered? */
		if (!$this->has($serviceName)) {
			/* fatal */
			throw new ServiceNotRegistered($serviceName);
		}

		/* is this a variable a singleton or factory */
		if (isset($this->registeredServices[$serviceName]['value'])) {
			$return = $this->registeredServices[$serviceName]['value'];
		} elseif ($this->registeredServices[$serviceName]['singleton']) { /* Is this a singleton or factory? */
			$return = $this->singleton($serviceName);
		} else {
			$return = $this->factory($serviceName);
		}

		return $return;
	}

	/**
	 * Check whether the Service been registered
	 *
	 * @param string $serviceName does the requested service name exist
	 * @return bool
	 */
	public function has(string $serviceName): bool
	{
		$this->buildServiceName($serviceName);

		return isset($this->registeredServices[$serviceName]);
	}

	/**
	 * Register a new service as a singleton or factory
	 *
	 * @param string $serviceName Service Name
	 * @param mixed $value closure to call in order to instancate it or value to store
	 * @param bool $singleton should this be a singleton or factory
	 * @return void
	 */
	public function register(string $serviceName, $value, bool $singleton = false): void
	{
		$this->buildServiceName($serviceName);

		if ($value instanceof Closure) {
			$this->registeredServices[$serviceName] = ['closure' => $value, 'singleton' => $singleton, 'reference' => null];
		} else {
			$this->registeredServices[$serviceName] = ['value' => $value];
		}
	}

	/**
	 * Remove a Registered Service
	 *
	 * @param string $serviceName service name to remove
	 * @return void
	 */
	public function remove(string $serviceName): void
	{
		$this->buildServiceName($serviceName);

		unset($this->registeredServices[$serviceName]);
	}

	protected function buildServiceName(string &$serviceName): void
	{
		$serviceName = strtolower($serviceName);
	}

	/**
	 * Get the same instance of a service
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	protected function singleton(string $serviceName)
	{
		return $this->registeredServices[$serviceName]['reference'] ?? $this->registeredServices[$serviceName]['reference'] = $this->factory($serviceName);
	}

	/**
	 * Get new instance of a service
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	protected function factory(string $serviceName)
	{
		return $this->registeredServices[$serviceName]['closure']($this);
	}
} /* end class */

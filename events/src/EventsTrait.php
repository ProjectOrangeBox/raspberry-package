<?php

namespace projectorangebox\events;

use projectorangebox\events\EventsInterface;

trait EventsTrait
{
	protected $_eventService = null;

	/**
	 * Inject Service
	 * This way we can inject a mock
	 *
	 * @param \projectorangebox\events\EventsInterface $eventService
	 *
	 * @return void
	 */
	public function setEventService(EventsInterface $eventService): void
	{
		$this->_eventService = $eventService;
	}

	/**
	 * Load the Service
	 *
	 * @return mixed
	 */
	public function getEventService() /* mixed */
	{
		if (!$this->_eventService) {
			$this->_eventService = service('event');
		}

		return ($this->_eventService) ? $this->_eventService : false;
	}

	/**
	 * Register a Event
	 *
	 * @param string $name
	 * @param [type] $callable
	 * @param int $priority
	 *
	 * @return void
	 */
	public function registerEvent(string $name, $callable, int $priority = EventsInterface::PRIORITY_NORMAL)
	{
		if ($service = $this->getEventService()) {
			$service->register($name, $callable, $priority);
		}
	}

	/**
	 * Trigger an Event
	 *
	 * @param string $name
	 * @param [type] ...$arguments
	 *
	 * @return void
	 */
	public function triggerEvent(string $name, &...$arguments)
	{
		if ($service = $this->getEventService()) {
			$service->trigger($name, ...$arguments);
		}
	}
} /* end class */

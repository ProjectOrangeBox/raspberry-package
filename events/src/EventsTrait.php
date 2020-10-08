<?php

namespace projectorangebox\events;

use projectorangebox\events\EventsInterface;

trait EventsTrait
{
	protected $_eventService = null;

	/* This way we can inject a mock */
	public function setEventService(EventsInterface $eventService): void
	{
		$this->_eventService = $eventService;
	}

	public function registerEvent(string $name, $callable, int $priority = EventsInterface::PRIORITY_NORMAL)
	{
		$this->_getEventService()->register($name, $callable, $priority);
	}

	public function triggerEvent(string $name, &...$arguments)
	{
		$this->_getEventService()->trigger($name, ...$arguments);
	}

	/* protected */

	protected function _getEventService(): EventsInterface
	{
		if (!$this->_eventService) {
			$this->_eventService = service('event');
		}

		return $this->_eventService;
	}
} /* end class */

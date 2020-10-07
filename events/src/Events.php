<?php

namespace projectorangebox\events;

use Closure;
use projectorangebox\log\LoggerTrait;
use projectorangebox\events\EventsInterface;

class Events implements EventsInterface
{
	use LoggerTrait;

	/**
	 * storage for events
	 *
	 * @var array
	 */
	protected $currentIndex = 0;
	protected $listeners = [];
	protected $sorted = [];

	/**
	 * Register a listener
	 *
	 * #### Example
	 * ```php
	 * register('open.page',function(&$var1) { echo "hello $var1"; },EVENT::PRIORITY_HIGH);
	 * ```
	 * @access public
	 *
	 * @param string $name name of the event we want to listen for
	 * @param callable $callable function to call if the event if triggered
	 * @param int $priority the priority this listener has against other listeners
	 *
	 * @return Event
	 *
	 */
	public function register(string $name, Closure $callable, int $priority = EVENTS::PRIORITY_NORMAL): EventsInterface
	{
		/* if they pass in a array treat it as a name=>closure pair */
		if (is_array($name)) {
			foreach ($name as $n) {
				$this->register($n, $callable, $priority);
			}
			return $this;
		}

		/* clean up the name */
		$this->normalizeName($name);

		/* log a debug event */
		$this->log('debug', 'event::register::' . $name);

		$this->sorted[$name] = false;

		$priority = ($priority == SELF::PRIORITY_FIRST) ? - ($this->currentIndex++) : $priority . str_pad($this->currentIndex++, 6, '0', \STR_PAD_LEFT);

		$this->listeners[$name][(int)$priority] = $callable;

		/* allow chaining */
		return $this;
	}

	/**
	 * Trigger an event
	 *
	 * #### Example
	 * ```php
	 * trigger('open.page',$var1);
	 * ```
	 * @param string $name event to trigger
	 * @param mixed ...$arguments pass by reference
	 *
	 * @return Event
	 *
	 * @access public
	 *
	 */
	public function trigger(string $name, &...$arguments): EventsInterface
	{
		/* clean up the name */
		$this->normalizeName($name);

		/* log a debug event */
		$this->log('debug', 'event::trigger::' . $name);

		/* do we even have any events with this name? */
		if (isset($this->listeners[$name])) {
			foreach ($this->getSorted($name) as $listener) {
				if ($listener(...$arguments) === false) {
					break;
				}
			}
		}

		/* allow chaining */
		return $this;
	}

	/**
	 *
	 * Is there any listeners for a certain event?
	 *
	 * #### Example
	 * ```php
	 * $bool = ci('event')->has('page.load');
	 * ```
	 * @access public
	 *
	 * @param string $name event to search for
	 *
	 * @return bool
	 *
	 */
	public function has(string $name): bool
	{
		/* clean up the name */
		$this->normalizeName($name);

		return isset($this->listeners[$name]);
	}

	/**
	 *
	 * Return an array of all of the event names
	 *
	 * #### Example
	 * ```php
	 * $triggers = ci('event')->events();
	 * ```
	 * @access public
	 *
	 * @return array
	 *
	 */
	public function events(): array
	{
		return array_keys($this->listeners);
	}

	/**
	 *
	 * Return the number of events for a certain name
	 *
	 * #### Example
	 * ```php
	 * $listeners = ci('event')->count('database.user_model');
	 * ```
	 * @access public
	 *
	 * @param string $name
	 *
	 * @return int
	 *
	 */
	public function count(string $name): int
	{
		/* clean up the name */
		$this->normalizeName($name);

		return (isset($this->listeners[$name])) ? count($this->listeners[$name][1]) : 0;
	}

	/**
	 *
	 * Removes a single listener from an event.
	 * this doesn't work for closures!
	 *
	 * @access public
	 *
	 * @param string $name
	 * @param $listener
	 *
	 * @return bool
	 *
	 */
	public function unregister(string $name, Closure $listener): bool
	{
		/* clean up the name */
		$this->normalizeName($name);

		$removed = false;

		if (isset($this->listeners[$name])) {
			foreach ($this->listeners[$name] as $index => $closure) {
				if ($closure === $listener) {
					unset($this->listeners[$name][$index]);

					$removed = true;
				}
			}
		}

		return $removed;
	}

	/**
	 *
	 * Removes all listeners.
	 *
	 * If the event_name is specified, only listeners for that event will be
	 * removed, otherwise all listeners for all events are removed.
	 *
	 * @access public
	 *
	 * @param string $name
	 *
	 * @return \Event
	 *
	 */
	public function unregisterAll(string $name = ''): EventsInterface
	{
		/* clean up the name */
		$this->normalizeName($name);

		if (!empty($name)) {
			unset($this->listeners[$name]);
		} else {
			$this->listeners = [];
		}

		/* allow chaining */
		return $this;
	}

	/**
	 * debug
	 *
	 * @return array
	 */
	public function debug(): array
	{
		return [
			'currentIndex' => $this->currentIndex,
			'listeners' => $this->listeners,
			'sorted' => $this->sorted,
		];
	}

	/**
	 *
	 * Normalize the event name
	 *
	 * @access protected
	 *
	 * @param string $name
	 *
	 * @return string
	 *
	 */
	protected function normalizeName(string &$name): void
	{
		$name = trim(preg_replace('/[^a-z0-9]+/', '.', strtolower($name)), '.');
	}

	/**
	 *
	 * Do the actual sorting
	 *
	 * @access protected
	 *
	 * @param string $name
	 *
	 * @return array
	 *
	 */
	protected function getSorted(string $name): array
	{
		$this->normalizeName($name);

		$listeners = [];

		if (isset($this->listeners[$name])) {
			$listeners = $this->listeners[$name];

			if (!$this->sorted[$name]) {
				/* sort on the integer key */
				ksort($listeners);

				$this->sorted[$name] = true;
			}
		}

		return $listeners;
	}
} /* end class */
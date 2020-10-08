<?php

namespace projectorangebox\session;

use projectorangebox\session\SessionInterface;
use projectorangebox\session\FlashdataInterface;

class Flashdata implements FlashdataInterface
{
	protected $session = null;
	protected $config = [];

	protected $flashKey = '[[FLASHDATA]]';
	protected $flashRecord = [];

	public function __construct(SessionInterface $session, array $config)
	{
		$this->session = $session;
		$this->config = $config;

		/* load */
		$this->flashRecord = $this->session->get($this->flashKey, []);

		$this->sweep();
	}

	public function all(): array
	{
		$dataArray = [];

		foreach ($this->flashRecord as $key => $record) {
			$dataArray[$key] = $this->flashRecord[$key]['data'];
		}

		return $dataArray;
	}

	/**
	 * Read the flash data
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get(string $key = NULL, $default = NULL) /* mixed */
	{
		return (isset($this->flashRecord[$key], $this->flashRecord[$key]['data'])) ? $this->flashRecord[$key]['data'] : $default;
	}

	/**
	 * Read the flash data but keep around for next request
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function keep(string $key = NULL, $default = NULL) /* mixed */
	{
		/* tag as new again */
		$this->flashRecord[$key]['is'] = 'new';

		$this->save();

		return $this->get($key, $default);
	}

	/**
	 * Set Flash Data
	 *
	 * @param string $key
	 * @param mixed|null $value
	 * @return SessionInterface
	 */
	public function set(string $key, $value = NULL, int $seconds = null): SessionInterface
	{
		$this->flashRecord[$key]['data'] = $value;
		$this->flashRecord[$key]['is'] = 'new';

		if ($seconds) {
			$this->flashRecord[$key]['until'] = time() + $seconds;
		}

		$this->save();

		return $this->session;
	}

	/* protected */

	/* remove the old */
	protected function sweep()
	{
		foreach ($this->flashRecord as $key => $record) {
			if ($record['is'] == 'old') {
				/* remove old */
				unset($this->flashRecord[$key]);
			} elseif ($record['is'] == 'new' && !isset($record['until'])) {
				/* tag new as old unless it has until */
				$this->flashRecord[$key]['is'] = 'old';
			} elseif (isset($record['until']) && $record['until'] < time()) {
				/* remove until */
				unset($this->flashRecord[$key]);
			}
		}

		$this->save();
	}

	/* save to parent session */
	protected function save(): void
	{
		$this->session->set($this->flashKey, $this->flashRecord);
	}
} /* end class */
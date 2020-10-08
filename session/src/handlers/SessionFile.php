<?php

namespace projectorangebox\session\handlers;

use FS;
use SessionHandlerInterface; /* php interface */

class SessionFile implements SessionHandlerInterface
{
	protected $config = [];
	protected $savePrefix = '';

	public function __construct(array $config)
	{
		$this->config = $config;

		/* resolve full path once */
		$path = FS::resolve($config['file']['path']);

		if (!is_dir($path)) {
			mkdir($path, 0777);
		}

		/* concat once */
		$this->savePrefix = $path . '/sessionFile_';
	}

	public function open($savePath, $sessionName)
	{
		return true;
	}

	public function close()
	{
		return true;
	}

	public function read($id)
	{
		return (\file_exists($this->savePrefix . $id)) ? \file_get_contents($this->savePrefix . $id) : '';
	}

	public function write($id, $data)
	{
		return \file_put_contents($this->savePrefix . $id, $data) === false ? false : true;
	}

	public function destroy($id)
	{
		$file = $this->savePrefix . $id;

		if (\file_exists($file)) {
			\unlink($file);
		}

		return true;
	}

	public function gc($maxlifetime)
	{
		foreach (\glob($this->savePrefix . '*') as $file) {
			if (\filemtime($file) + $maxlifetime < \time() && \file_exists($file)) {
				\unlink($file);
			}
		}

		return true;
	}
} /* end class */

<?php

namespace projectorangebox\views;

use FS;
use projectorangebox\log\LoggerTrait;
use projectorangebox\views\exceptions\ViewNotFound;
use projectorangebox\views\exceptions\ViewFileNotFound;

class Views implements ViewsInterface
{
	use LoggerTrait;

	protected $data = [];
	protected $views = [];

	/**
	 * __construct
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->log('info', 'Views::__construct');

		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/Config.php', $config);

		$this->views = $this->config['views'] ?? [];

		if (is_array($this->config['data'])) {
			$this->data = $this->config['data'];
		}
	}

	/**
	 * addView
	 *
	 * @param string $key
	 * @param string $path
	 * @return ViewsInterface
	 */
	public function addView(string $key, string $path): ViewsInterface
	{
		$this->views[trim(strtolower($key), '/')] = '/' . trim($path, '/');

		return $this;
	}

	/**
	 * getViews
	 *
	 * @return string[]
	 */
	public function getViews(): array
	{
		return $this->views;
	}

	public function setViews(array $views): ViewsInterface
	{
		$this->views = &$views;

		return $this;
	}

	/**
	 * render
	 *
	 * @param string $key view name
	 * @param mixed[] $data array of variables to make avaiable to view
	 * @return string
	 */
	public function render(string $key, array $data = null): string
	{
		$this->log('info', 'Views::render::' . $key);

		if (is_array($data)) {
			$this->data = array_replace($this->data, $data);
		}

		return $this->_render($this->findView($key), $this->data);
	}

	public function findView(string $key): string
	{
		$key = \strtolower($key);

		/* does the view exist */
		if (!isset($this->views[$key])) {
			throw new ViewNotFound($key);
		}

		/* get the view's path */
		$file = $this->views[$key];

		if (!\FS::file_exists($file)) {
			throw new ViewFileNotFound($file);
		}

		return $file;
	}

	/**
	 * _render
	 *
	 * @param string $_view_file
	 * @param mixed[] $_view_data
	 * @return string
	 */
	protected function _render(string $_view_file, array $_view_data): string
	{
		/* extract out view data and make it in scope */
		\extract($_view_data);

		/* start output cache */
		\ob_start();

		/* resolve our file path */
		$_view_file = FS::resolve($_view_file);

		/* load in view (which now has access to the in scope view data */
		require $_view_file;

		/* capture cache and return */
		return \ob_get_clean();
	}

	/**
	 * data
	 *
	 * @param string|mixed[] $key
	 * @param mixed $value
	 * @return ViewsInterface
	 */
	public function data($key, $value = null): ViewsInterface
	{
		if (\is_array($key)) {
			$this->data = $key;
		} elseif (\is_string($key)) {
			$this->data[$key] = $value;
		}

		return $this;
	}

	/**
	 * getData
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getData(string $key = null)
	{
		$data = null;

		if ($key) {
			if (isset($this->data[$key])) {
				$data = $this->data[$key];
			}
		} else {
			$data = $this->data;
		}

		return $data;
	}

	/**
	 * clearData
	 *
	 * @return ViewsInterface
	 */
	public function clearData(): ViewsInterface
	{
		$this->data = [];

		return $this;
	}
} /* end class */

<?php

namespace projectorangebox\views;

interface ViewsInterface
{

	/**
	 * __construct
	 *
	 * @param string[] $config
	 */
	public function __construct(array $config);

	/**
	 * addView
	 *
	 * @param string $key
	 * @param string $path
	 * @return ViewsInterface
	 */
	public function addView(string $key, string $path): ViewsInterface;

	/**
	 * getViews
	 *
	 * @return string[]
	 */
	public function getViews(): array;

	/**
	 * set all views at once replacing anything currently assigned
	 *
	 * @param array $views
	 * @return ViewsInterface
	 */
	public function setViews(array $views): ViewsInterface;

	/**
	 * render
	 *
	 * @param string $key
	 * @param mixed[] $data
	 * @return string
	 */
	public function render(string $key, array $data = null): string;

	/**
	 * data
	 *
	 * @param mixed $var
	 * @param mixed $value
	 * @return ViewsInterface
	 */
	public function data($var, $value = null): ViewsInterface;

	/**
	 * getData
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getData(string $key = null);

	/**
	 * clearData
	 *
	 * @return ViewsInterface
	 */
	public function clearData(): ViewsInterface;
} /* end class */

<?php

namespace projectorangebox\viewparser;

use Closure;

interface ViewParserInterface
{
	/**
	 * constructor
	 *
	 * @param array $config
	 * @return mixed
	 */
	public function __construct(array $config);

	/**
	 * Add plugins to a view parser
	 *
	 * @param array $plugins
	 * @return void
	 */
	public function addPlugins(array $plugins): void;

	/**
	 * Add plugin to a view parser
	 *
	 * @param string $name
	 * @param Closure $closure
	 * @return void
	 */
	public function addPlugin(string $name, Closure $closure): void;

	/**
	 * change the view parser tag delimiters
	 *
	 * @param string $l
	 * @param string $r
	 * @return void
	 */
	public function setDelimiters($l = '{{', string $r = '}}'): void;
} /* end interface */
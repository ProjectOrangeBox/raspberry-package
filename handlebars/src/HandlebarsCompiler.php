<?php

namespace projectorangebox\handlebars;

use FS;
use Exception;
use LightnCandy\LightnCandy;
use projectorangebox\handlebars\PluginHandler;
use projectorangebox\handlebars\HandlebarsCache;
use projectorangebox\views\exceptions\ViewNotFound;
use projectorangebox\views\exceptions\ViewFileNotFound;
use projectorangebox\viewparser\exceptions\CannotExecuteView;

class HandlebarsCompiler
{
	protected $config = [];
	protected $pluginCompiler = null;
	protected $cache = null;
	protected $flags = 0;
	protected $delimiters = ['{{', '}}'];
	protected $views = [];
	protected $forceCompile = false;

	public function __construct(array $config, PluginHandler &$pluginHandler)
	{
		$this->config = $config;

		$this->forceCompile = $config['force compile'] ?? false;

		/* lightncandy handlebars compiler flags https://github.com/zordius/lightncandy#compile-options */
		$this->flags = $config['flags'] ?? LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_BESTPERFORMANCE | LightnCandy::FLAG_RUNTIMEPARTIAL;

		$this->delimiters = $config['delimiters'] ?? $this->delimiters;

		$this->pluginCompiler = &$pluginHandler;

		$this->cache = $config['cache'] ?? new HandlebarsCache($this->config);
	}

	/**
	 * set the template delimiters
	 *
	 * @param string/array
	 * @param string
	 * @return object (this)
	 */
	public function setDelimiters($l = '{{', string $r = '}}'): void
	{
		/* set delimiters */
		$this->delimiters = (is_array($l)) ? $l : [$l, $r];
	}

	public function setViews(array &$views): void
	{
		/* we need this to resolve partials */
		$this->views = &$views;
	}

	public function compile(string $fullpath)
	{
		$fullpath = $this->findView($fullpath);

		$key = md5($fullpath);

		$templatePHP = $this->cache->get($key);

		if (!$templatePHP || $this->forceCompile) {
			/* file location validated in the parent class handlebars */
			$this->cache->save($key, $this->_compile($fullpath));

			$templatePHP = $this->cache->get($key);
		}

		/* is what we got back even executable? */
		if (!is_callable($templatePHP)) {
			throw new CannotExecuteView($key);
		}

		return $templatePHP;
	}

	/**
	 * heavy lifter - wrapper for lightncandy https://github.com/zordius/lightncandy handlebars compiler
	 *
	 * returns a executable php function
	 *
	 */
	public function _compile(string $fullpath): string
	{
		/* Compile it into php magic! Thank you zordius https://github.com/zordius/lightncandy */
		$source = LightnCandy::compile(FS::file_get_contents($fullpath), [
			'flags' => $this->flags, /* compiler flags */
			'helpers' => $this->pluginCompiler->getPlugins(),
			'renderex' => '/* ' . $fullpath . ' compiled @ ' . date('Y-m-d h:i:s e') . ' */',
			'delimiters' => $this->delimiters,
			'prepartial' => function ($context, $template, $name) {
				return $template;
			},
			'partialresolver' => function ($context, $name) { /* partial & template handling */
				try {
					/* raw template source not compiled */
					$template = FS::file_get_contents($this->findView($name));
				} catch (ViewNotFound $e) {
					$template = '<!-- view named "' . $name . '" could not found --!>';
				}

				return $template;
			},
		]);

		return '<?php' . PHP_EOL . $source . PHP_EOL . '?>';
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

	public function flushCache(): void
	{
		$this->cache->clean();
	}
} /* end class */
<?php

namespace projectorangebox\handlebars;

use Closure;
use projectorangebox\views\Views;
use projectorangebox\views\ViewsInterface;
use projectorangebox\handlebars\PluginHandler;
use projectorangebox\handlebars\HandlebarsCompiler;
use projectorangebox\viewparser\ViewParserInterface;

/**
 * Handlebars Parser
 *
 * This content is released under the MIT License (MIT)
 *
 * @package	CodeIgniter / Orange
 * @author	Don Myers
 * @author Zordius, Taipei, Taiwan
 * @license http://opensource.org/licenses/MIT MIT License
 * @link	https://github.com/ProjectOrangeBox
 * @link https://github.com/zordius/lightncandy
 *
 *
 *
 * Helpers:
 *
 * $helpers['foobar'] = function($options) {};
 *
 * $options =>
 * 	[name] => lex_lowercase # helper name
 * 	[hash] => Array # key value pair
 * 		[size] => 123
 * 		[fullname] => Don Myers
 * 	[contexts] => ... # full context as object
 * 	[_this] => Array # current loop context
 * 		[name] => John
 * 		[phone] => 933.1232
 * 		[age] => 21
 * 	['fn']($options['_this']) # if ??? - don't forget to send in the context
 * 	['inverse']($options['_this']) # else ???- don't forget to send in the context
 *
 * external functions used
 * path() - combined a config file key/value with some {magic} find and replace
 * env()
 * atomic_file_put_contents() - atomic version of file_put_contents()
 * ci('config')->item(...)
 * ci('servicelocator')->find(...)
 *
 */

class Handlebars extends Views implements ViewsInterface, ViewParserInterface
{
	public $config = [];
	public $pluginCompiler;
	public $viewCompiler;

	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/config.php', $config);

		/**
		 * This sets up:
		 * $this->views
		 * $this->data
		 */
		parent::__construct($this->config);

		$this->pluginHandler = new PluginHandler($this->config);

		$this->viewCompiler = new HandlebarsCompiler($this->config, $this->pluginHandler);
	}

	/*
	Inherit From Parent "Views"

	public function addView(string $key, string $path): ViewsInterface;
	public function getViews(): array;
	public function setViews(array &$views): ViewsInterface;

	public function data($var, $value = null): ViewsInterface;
	public function getData(string $key = null);
	public function clearData(): ViewsInterface;
	*/

	/* view interface */
	public function render(string $filepath, array $data = null): string
	{
		if (!\is_array($data)) {
			$data = [];
		}

		/* tell the compiler about all of the views we know about */
		$this->viewCompiler->setViews($this->views);

		$phpFunction = $this->viewCompiler->compile($filepath);

		return $phpFunction($data);
	}

	/* view parser interface */
	/* pass thru */
	public function addPlugin(string $name, Closure $closure): void
	{
		$this->pluginHandler->addPlugin($name, $closure);
	}

	public function addPlugins(array $plugins): void
	{
		foreach ($plugins as $name => $closure) {
			$this->addPlugin($name, $closure);
		}
	}

	public function setDelimiters($l = '{{', string $r = '}}'): void
	{
		$this->viewCompiler->setDelimiters($l, $r);
	}

	/* extras */

	public function flushCaches(): bool
	{
		$this->viewCompiler->flushCache();
		$this->pluginHandler->flushCache();

		return true;
	}

	public function preCache(array $templates = null): array
	{
		$compiled = [];

		$templates = (!$templates) ? array_keys($this->views) : $templates;

		/* tell the compiler about all of the views we know about */

		$this->viewCompiler->setViews($this->views);

		foreach ($templates as $templateName) {
			$compiled[] = $templateName;

			$this->viewCompiler->compile($templateName);
		}

		return $compiled;
	}
} /* end class */

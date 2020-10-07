<?php

namespace projectorangebox\assets;

use Closure;
use projectorangebox\log\LoggerTrait;
use projectorangebox\collection\Collection;
use projectorangebox\assets\AssetsInterface;

class Assets implements AssetsInterface
{
	use LoggerTrait;

	protected $config = [];
	protected $priority = SELF::PRIORITY_NORMAL;
	protected $formatters = [];
	protected $collection = null;

	/**
	 * @param array $config
	 * @return Assets
	 */
	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/Config.php', $config);

		/* actual asset holder collection names as variables */
		$this->collection = new Collection(['make lowercase' => true, 'prevent duplicates' => true]);

		/* add from config file */
		foreach ($this->config['add'] as $name => $parameters) {
			if (is_array($parameters)) {
				$this->addMany($name, $parameters);
			} else {
				$this->add($name, $parameters);
			}
		}

		/* our defaults */
		$this->changeFormatter('title', function ($asArray) {
			return implode(' ', $asArray);
		});

		$this->changeFormatter('link', function ($asArray) {
			$link = ($this->config['link']) ?? '<link href="%%" type="text/css" rel="stylesheet"/>';
			$html = '';

			foreach ($asArray as $element) {
				if (substr($element, 0, 1) != '<') {
					$html .= str_replace('%%', $element, $link);
				} else {
					$html .= $element;
				}
			}

			return $html;
		});

		$this->changeFormatter('script', function ($asArray) {
			$script = ($this->config['script']) ?? '<script src="%%" type="text/javascript" charset="utf-8"></script>';
			$html = '';

			foreach ($asArray as $element) {
				if (substr($element, 0, 1) != '<') {
					$html .= str_replace('%%', $element, $script);
				} else {
					$html .= $element;
				}
			}

			return $html;
		});

		$this->changeFormatter('bodyclass', function ($asArray) {
			$keys = explode(' ', trim(implode(' ', $asArray)));

			/* remove dups */
			return implode(' ', \array_combine($keys, $keys));
		});

		$this->changeFormatter('jsvariable', function ($asArray) {
			$html = '';

			foreach ($asArray as $record) {
				list($name, $value, $raw) = $record;

				$html .= ($raw) ? 'var ' . $name . '=' . $value . ';' : (((is_scalar($value)) ? 'var ' . $name . '="' . str_replace('"', '\"', $value) . '";' : 'var ' . $name . '=' . json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ';'));
			}

			return $html;
		});

		$this->changeFormatter('metatag', function ($asArray) {
			$html = '';

			foreach ($asArray as $record) {
				list($attr, $name, $content) = $record;

				$html .= '<meta ' . $attr . '="' . $name . '"' . (($content) ? ' content="' . $content . '"' : '') . '>';
			}

			return $html;
		});

		$this->changeFormatter('domready', function ($asArray) {
			$domReady = ($this->config['domReady']) ?? '<script>document.addEventListener("DOMContentLoaded",function(e){%%});</script>';

			return str_replace('%%', trim(implode('', $asArray)), $domReady);
		});

		/* add formatters */
		foreach ($this->config['formatter'] as $name => $formatter) {
			$this->changeFormatter($name, $formatter);
		}
	}

	/*
	script
	link
	domReady
	title
	etc...

	Plural to Singular
	scriptFiles => scriptFile
	linkFiles => linkFile,
	*/
	public function __call($name, $arguments)
	{
		/* if argument 1 is an array than we loop over it. if it's not than treat it as a single element */
		if (is_array($arguments[0])) {
			$this->addMany($name, $arguments[0]);
		} else {
			$arg = (count($arguments) == 1) ? $arguments[0] : $arguments;

			$this->add($name, $arg);
		}

		/* allow chaining */
		return $this;
	}

	public function priority(int $priority): AssetsInterface
	{
		$this->priority = $priority;

		return $this;
	}

	public function resetPriority(): AssetsInterface
	{
		$this->priority = SELF::PRIORITY_NORMAL;

		return $this;
	}

	public function variables(): array
	{
		return $this->collection->groups();
	}

	/*
	in a php view you can use
	<?=service('assets')->get($name) ?>
	*/
	public function get(string $group): string
	{
		$this->log('DEBUG', $group . ' asset requested');

		$asArray = $this->collection->get($group, true);

		$asString = (isset($this->formatters[$group])) ? $this->formatters[$group]($asArray) : implode('', $asArray);

		return trim($asString);
	}

	public function has(string $group): bool
	{
		return $this->collection->has($group);
	}

	public function add(string $group, $record): AssetsInterface
	{
		$this->log('DEBUG', 'add ' . $group);

		$this->collection->add($group, $record, $this->priority);

		$this->resetPriority();

		return $this;
	}

	public function addMany(string $group, array $records): AssetsInterface
	{
		foreach ($records as $record) {
			if (\is_array($record)) {
				$arg = (count($record) == 1) ? $record[0] : $record;
			} else {
				$arg = $record;
			}

			$this->collection->add($group, $arg, $this->priority);
		}

		$this->resetPriority();

		return $this;
	}

	public function changeFormatter(string $group, Closure $closure): AssetsInterface
	{
		$this->formatters[strtolower($group)] = $closure;

		return $this;
	}

	public function stringifyAttributes(array $attributesArray): string
	{
		$attributes = [];

		foreach ($attributesArray as $key => $val) {
			$attributes[] = $key . '="' . htmlspecialchars($val, ENT_QUOTES) . '"';
		}

		return ' ' . implode(' ', $attributes);
	}

	public function htmlElement(string $element, array $attributes, string $content = ''): string
	{
		/* HTML Void Element or normal? */
		return (in_array($element, ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'])) ?
			'<' . $element . $this->stringifyAttributes($attributes) . '/>' :
			'<' . $element . $this->stringifyAttributes($attributes) . '>' . $content . '</' . $element . '>';
	}

	public function debug(): array
	{
		return [
			'formatters' => $this->formatters,
			'config' => $this->config,
			'collection' => $this->collection,
		];
	}
} /* end class */
<?php

class x
{

	public function scriptFile(string $file, int $priority = SELF::PRIORITY_NORMAL): AssetsInterface
	{
		return $this->script($this->scriptHtml($file), $priority);
	}

	public function linkFile(string $file, int $priority = SELF::PRIORITY_NORMAL): AssetsInterface
	{
		return $this->link($this->linkHtml($file), $priority);
	}

	public function metaTags(array $tags, int $priority = SELF::PRIORITY_NORMAL): AssetsInterface
	{
		foreach ($tags as $tag) {
			switch (count($tag)) {
				case 2:
					$this->metaTag($tag[0], $tag[1], null, $priority);
					break;
				case 3:
					$this->metaTag($tag[0], $tag[1], $tag[2], $priority);
					break;
				default:
					throw new InvalidArgumentException();
			}
		}

		return $this;
	}

	public function metaTag(string $attr, string $name, string $content = null, int $priority = SELF::PRIORITY_NORMAL): AssetsInterface
	{
		$this->add('meta', '<meta ' . $attr . '="' . $name . '"' . (($content) ? ' content="' . $content . '"' : '') . '>', $priority);

		return $this;
	}

	public function jsVariables(array $variables, int $priority = SELF::PRIORITY_NORMAL): AssetsInterface
	{
		foreach ($variables as $name => $value) {
			$this->jsVariable($name, $value, $priority);
		}

		return $this;
	}

	public function jsVariable(string $name, $value, int $priority = SELF::PRIORITY_NORMAL, bool $raw = false): AssetsInterface
	{
		$value = ($raw) ? 'var ' . $name . '=' . $value . ';' : (((is_scalar($value)) ? 'var ' . $name . '="' . str_replace('"', '\"', $value) . '";' : 'var ' . $name . '=' . json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ';'));

		$this->add('jsVariables', $value, $priority);

		return $this;
	}

	public function bodyClasses(array $classes, int $priority = SELF::PRIORITY_NORMAL): AssetsInterface
	{
		foreach ($classes as $class) {
			$this->bodyClass($class, $priority);
		}

		return $this;
	}

	public function bodyClass(string $class, int $priority = SELF::PRIORITY_NORMAL): AssetsInterface
	{
		foreach (explode(' ', trim($class)) as $c) {
			$this->add('bodyClass', trim($c), $priority);
		}

		return $this;
	}

	public function linkHtml(string $file): string
	{
		return $this->ary2element('link', array_merge($this->config['link attributes'], ['href' => $file]));
	}

	public function scriptHtml(string $file): string
	{
		return $this->ary2element('script', array_merge($this->config['script attributes'], ['src' => $file]));
	}
}

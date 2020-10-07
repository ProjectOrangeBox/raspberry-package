<?php

$this->assets->changeFormatter('example', function ($asArray, $config) {
	return implode(' ', $asArray);
});

$this->assets->changeFormatter('charset', function ($asArray, $config) {
	$value = (count($asArray)) ? end($asArray) : 'utf-8';

	return '<meta charset="' . $value . '">';
});

$this->assets->changeFormatter('description', function ($asArray, $config) {
	$value = (count($asArray)) ? end($asArray) : '';

	return '<meta name="description" content="' . $value . '">';
});

$this->assets->changeFormatter('og', function ($asArray, $config) {
	$values = (count($asArray)) ? end($asArray) : [];

	$html = '';

	foreach ($values as $name => $content) {
		$html .= '<meta property="og:' . $name . '" content="' . $content . '">';
	}

	return $html;
});

$this->assets->changeFormatter('manifest', function ($asArray, $config) {
	$value = (count($asArray)) ? end($asArray) : 'site.webmanifest';

	return '<link rel="manifest" href="' . $value . '">';
});


$this->assets->changeFormatter('manifest', function ($asArray, $config) {
	$value = (count($asArray)) ? end($asArray) : 'site.webmanifest';

	return '<link rel="manifest" href="' . $value . '">';
});

$this->assets->changeFormatter('touch', function ($asArray, $config) {
	$value = (count($asArray)) ? end($asArray) : 'touch.png';

	return '<link rel="apple-touch-icon" href="' . $value . '">';
});

$this->assets->changeFormatter('themecolor', function ($asArray, $config) {
	$value = (count($asArray)) ? end($asArray) : '#fffffff';

	return '<meta name="theme-color" content="' . $value . '">';
});

$this->assets->changeFormatter('bar', function ($asArray, $config) {
	return '[' . trim(strtoupper(implode(' ', $asArray))) . ']';
});

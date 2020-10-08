<?php

if (!function_exists('stringifyAttributes')) {
  function stringifyAttributes(array $attributesArray): string
  {
    $attributes = [];

    foreach ($attributesArray as $key => $val) {
      $attributes[] = $key . '="' . htmlspecialchars($val, ENT_QUOTES) . '"';
    }

    return ' ' . implode(' ', $attributes);
  }
}

if (!function_exists('htmlElement')) {
  function htmlElement(string $element, array $attributes, string $content = ''): string
  {
    /* HTML Void Element or normal? */
    return (in_array($element, ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'])) ?
      '<' . $element . stringifyAttributes($attributes) . '/>' :
      '<' . $element . stringifyAttributes($attributes) . '>' . $content . '</' . $element . '>';
  }
}

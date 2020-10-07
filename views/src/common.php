<?php

/**
 * Show output in Browser Console
 *
 * @param mixed $var converted to json
 * @param string $type - browser console log types [log]
 *
 */
if (!function_exists('console')) {
  function console($var, string $type = 'log'): void
  {
    echo '<script type="text/javascript">console.' . $type . '(' . json_encode($var) . ')</script>';
  }
}

/**
 * Escape html special characters
 *
 * @param $string
 *
 * @return string
 *
 */
if (!function_exists('e')) {
  function e($input): string
  {
    return (empty($input)) ? '' : html_escape($input);
  }
}

/**
 * Returns HTML escaped variable.
 *
 * @param	mixed	$var		The input string or array of strings to be escaped.
 * @param	bool	$double_encode	$double_encode set to FALSE prevents escaping twice.
 * @return	mixed			The escaped string or array of strings as a result.
 */
if (!function_exists('html_escape')) {
  function html_escape($var, $double_encode = TRUE)
  {
    if (empty($var)) {
      return $var;
    }

    if (is_array($var)) {
      foreach (array_keys($var) as $key) {
        $var[$key] = html_escape($var[$key], $double_encode);
      }

      return $var;
    }

    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8', $double_encode);
  }
}

<?php

namespace projectorangebox\validate;

use projectorangebox\log\LoggerTrait;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Authorization class.
 *
 * Handles login, logout, refresh user data
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 */

abstract class RuleAbstract
{
	use LoggerTrait;

	/**
	 * Array of values considered true
	 *
	 * @var array
	 */
	protected $true_array = [1, '1', 'y', 'on', 'yes', 't', 'true', true];

	/**
	 * Array of values considered false
	 *
	 * @var array
	 */
	protected $false_array = [0, '0', 'n', 'off', 'no', 'f', 'false', false];

	/**
	 * Current Error String used in sprintf()
	 *
	 * @var string
	 */
	protected $error_string = '';

	/**
	 * All of the current field data in a multi validation
	 *
	 * @var array
	 */
	protected $field_data;

	/**
	 * Contains the currently validated field value
	 *
	 * @var mixed
	 */
	protected $field;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $field_data All of the current field data in a multi validation []
	 * @param string $error_string Current Error String used in sprintf()
	 *
	 */
	public function __construct(array &$field_data = [], string &$error_string = '')
	{
		$this->field_data = &$field_data;
		$this->error_string = &$error_string;

		$this->log('info', 'Validate Class Initialized');
	}

	/**
	 *
	 * Storage a reference to the current field for further processing
	 *
	 * @access public
	 *
	 * @param &$field
	 *
	 * @return Validate
	 *
	 */
	public function field(&$field): RuleAbstract
	{
		$this->field = &$field;

		return $this;
	}

	/**
	 *
	 * validate method place holder
	 *
	 * @access public
	 *
	 * @param &$field
	 * @param string $options
	 *
	 * @return bool
	 *
	 */
	public function validate(&$field, string $options = ''): bool
	{
		return false;
	}

	/**
	 *
	 * Trim the current field reference by a certain number of characters
	 *
	 * @access public
	 *
	 * @param $length null
	 *
	 * @return Validate
	 *
	 * #### Example
	 * ```php
	 * $this->field($value)->length(8);
	 * ```
	 */
	public function length($length = null): RuleAbstract
	{
		if (is_numeric($length)) {
			if ((int) $length > 0) {
				$this->field = substr($this->field, 0, $length);
			}
		}

		return $this;
	}

	/**
	 *
	 * Preform a PHP trim on the current field reference
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @return Validate
	 *
	 * #### Example
	 * ```php
	 * $this->field($value)->trim();
	 * ```
	 */
	public function trim(): RuleAbstract
	{
		$this->field = trim($this->field);

		return $this;
	}

	/**
	 *
	 * Preform a non human character replace on the current field reference
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @return Validate
	 *
	 * #### Example
	 * ```php
	 * $this->field($value)->human();
	 * ```
	 */
	public function human(): RuleAbstract
	{
		$this->field = preg_replace("/[^\\x20-\\x7E]/mi", '', $this->field);

		return $this;
	}

	/**
	 *
	 * Preform a non human character replace on the current field reference
	 * but also allow line-feed, tabs, and returns
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @return Validate
	 *
	 * #### Example
	 * ```php
	 * $this->field($value)->human_plus();
	 * ```
	 */
	public function human_plus(): RuleAbstract
	{
		$this->field = preg_replace("/[^\\x20-\\x7E\\n\\t\\r]/mi", '', $this->field);

		return $this;
	}

	/**
	 *
	 * remove any character in the provided string on the current field reference
	 *
	 * @access public
	 *
	 * @param $strip
	 *
	 * @throws
	 * @return Validate
	 *
	 * #### Example
	 * ```php
	 * $this->field($value)->strip('!@#$%^&*()');
	 * ```
	 */
	public function strip($strip): RuleAbstract
	{
		$this->field = str_replace(str_split($strip), '', $this->field);

		return $this;
	}

	/**
	 *
	 * Test the current field reference to see if it falls into any of the boolean compatible options
	 *
	 * @access public
	 *
	 * @param $field
	 *
	 * @return bool
	 *
	 * #### Example
	 * ```php
	 * $this->field($value)->is_bol();
	 * ```
	 */
	public function is_bol($field): bool
	{
		return (in_array(strtolower($field), array_merge($this->true_array, $this->false_array), true)) ? true : false;
	}
} /* end class */

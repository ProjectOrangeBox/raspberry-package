<?php

namespace projectorangebox\validate\rules;

use projectorangebox\validate\RuleAbstract;

/**
 * Validate_integer
 * Insert description here
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2018
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 *
 * required
 * core:
 * libraries:
 * models:
 * helpers:
 * functions:
 *
 * @help contains anything other than an integer.
 *
 */
class Integer extends RuleAbstract
{
	public function validate(&$field, string $options = ''): bool
	{
		$this->error_string = '%s is not a integer.';

		return (bool) preg_match('/^[\-+]?[0-9]+$/', $field);
	}
}

<?php

namespace projectorangebox\validate\rules;

use projectorangebox\validate\RuleAbstract;

/**
 * Validate_in_list
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
 * @help is within a predetermined list.
 *
 */
class In_list extends RuleAbstract
{
	public function validate(&$field, string $options = ''): bool
	{
		$this->error_string = '%s must contain one of the available selections.';

		$types = ($options) ? $options : '';

		return (bool)(in_array($field, explode(',', $types)));
	}
}

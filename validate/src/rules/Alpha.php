<?php

namespace projectorangebox\validate\rules;

use projectorangebox\validate\RuleAbstract;

/**
 * Validate_alpha
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
 * @help contains anything other than alphabetical characters.
 *
 */
class Alpha extends RuleAbstract
{
	public function validate(&$field, string $options = ''): bool
	{
		$this->error_string = '%s may only contain alphabetical characters.';

		return (bool) ctype_alpha($field);
	}
}

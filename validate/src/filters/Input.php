<?php

namespace projectorangebox\validate\filters;

use projectorangebox\validate\FilterAbstract;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Validation Filter
 *
 * @help filter input for human visible characters with optional length
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
class Input extends FilterAbstract
{
	public function filter(&$field, string $options = ''): void
	{
		$this->field($field)->human()->length($options);
	}
}

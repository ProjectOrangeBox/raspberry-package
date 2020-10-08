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
 * @help filter for human with linefeeds and optional length
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 *
 */

class Textarea extends FilterAbstract
{
	public function filter(&$field, string $options = ''): void
	{
		$this->field($field)->human_plus()->length($options);
	}
}

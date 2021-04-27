<?php

if (!function_exists('model')) {
	function model(string $name)
	{
		return service($name . 'Model');
	}
}

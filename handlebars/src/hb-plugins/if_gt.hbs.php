<?php

/*
Example

{{#if_gt page_title "Current Projects"}}
	True Do This
{{else}}
	False Do This
{{/iif}}
*/
$helpers['if_gt'] = function ($value1, $value2, $options) {
	if ($value1 > $value2) {
		$return = $options['fn']();
	} elseif ($options['inverse'] instanceof \Closure) {
		$return = $options['inverse']();
	}

	return $return;
};

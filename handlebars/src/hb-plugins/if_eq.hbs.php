<?php

/*
Example

{{#if_eq page_title "Current Projects"}}
	True Do This
{{else}}
	False Do This
{{/iif}}

*/
$helpers['if_eq'] = function ($value1, $value2, $options) {
	if ($value1 == $value2) {
		$return = $options['fn']();
	} elseif ($options['inverse'] instanceof \Closure) {
		$return = $options['inverse']();
	}

	return $return;
};

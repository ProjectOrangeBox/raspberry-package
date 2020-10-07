<?php

/*
Example

<p>{{#exp:lowercase}}{{page_title}}{{/exp:lowercase}}</p>

*/

$helpers['exp:lowercase'] = function ($options) {
	return strtolower($options['fn']($options['_this']));
};

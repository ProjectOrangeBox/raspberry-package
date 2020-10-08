<?php

/*
Example

<p>{{#exp:uppercase}}{{page_title}}{{/exp:uppercase}}</p>

*/
$helpers['exp:uppercase'] = function ($options) {
	return strtoupper($options['fn']($options['_this']));
};

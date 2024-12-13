#!/usr/bin/env php

<?php
const PLX_CORE = '../../../core/';
include PLX_CORE . 'lib/class.plx.utils.php';

foreach(array('plucss', 'theme',) as $f) {
	echo $f . PHP_EOL;
	$content = file_get_contents($f . '.css');
	file_put_contents($f . '.min.css', plxUtils::minify($content));
}
echo 'Done !' . PHP_EOL;


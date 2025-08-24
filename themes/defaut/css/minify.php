#!/usr/bin/env php

<?php
const PLX_CORE = '../../../core/';
include PLX_CORE . 'lib/class.plx.utils.php';

foreach(array('plucss', 'theme', 'print',) as $f) {
	echo $f . ' ';
	$src = $f . '.css';
	if(!file_exists($src)) {
		echo 'missing' . PHP_EOL;
		continue;
	}

	$target = $f . '.min.css';
	if(!file_exists($target) or filemtime($target) < filemtime($src)) {
		$content = file_get_contents($src);
		file_put_contents($target, plxUtils::minify($content));
		echo 'updated' . PHP_EOL;
	} else {
		echo 'skipped' . PHP_EOL;
	}
}
echo 'Done !' . PHP_EOL;


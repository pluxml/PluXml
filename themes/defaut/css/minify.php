#!/usr/bin/env php
<?php
# Phpstan requires absolute path, not relative like "../../"
const PLX_ROOT = __DIR__ . '/../../../';
const PLX_CORE = PLX_ROOT . 'core/';

require PLX_CORE . 'lib/class.plx.utils.php';

foreach(array('plucss', 'theme', 'print',) as $f) {
	echo $f . ' ';
	$src = __DIR__ . '/' . $f . '.css';
	if(!file_exists($src)) {
		echo 'missing' . PHP_EOL;
		continue;
	}

	$target = preg_replace('#(\.css)$#', '.min$1', $src);
	if(!file_exists($target) or filemtime($target) < filemtime($src)) {
		$content = file_get_contents($src);
		file_put_contents($target, plxUtils::minify($content));
		echo 'updated' . PHP_EOL;
	} else {
		echo 'skipped' . PHP_EOL;
	}
}
echo 'Done !' . PHP_EOL;

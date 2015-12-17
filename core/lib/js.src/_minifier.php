<?php
/*
 * based on https://github.com/tedious/JShrink/blob/master/src/JShrink/Minifier.php
 *
 */
include(dirname(__FILE__).'/_JShrink.php');

function formatFilesize($bytes) {
	if ($bytes < 1024) return $bytes.' B';
	elseif ($bytes < 1048576) return round($bytes / 1024, 2).' Kb';
	elseif ($bytes < 1073741824) return round($bytes / 1048576, 2).' Mb';
}

$js = '';
$filesize = 0;

header('Content-Type: text/html; charset=UTF-8');
echo '<pre>';

foreach(glob("*.js") as $filename) {
    $js .= file_get_contents($filename);
	$filesize+=filesize($filename);
	echo $filename.' : '.formatFilesize(filesize($filename)).'<br />'; 
}

// Basic (default) usage.
//$minifiedCode = \JShrink\Minifier::minify($js);

// Disable YUI style comment preservation.
$minifiedCode = \JShrink\Minifier::minify($js, array('flaggedComments' => false));

file_put_contents(dirname(__FILE__).'/../pluxml.min.js', $minifiedCode);

echo '=====<br />';
echo 'Minifier : '.formatFilesize($filesize).' to '.formatFilesize(strlen($minifiedCode));
echo '</pre>';
?>
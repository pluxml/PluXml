<?php
if (!defined('PLX_ROOT')) {
	exit;
}

/*
 * In the folder of files to download, you can add an .htaccess file with line for each file as follows :
 * AddDescription "Short description for the myfile.txt" myfile.txt
 *
 * The content of a static page has to contain as least one html tag as follows :
 * <div data-download="some-folrder-with-files-for-downloading></div>
 * */

const PATTERN_STATIC_DOWNLOAD = '#<div[^>]*\s+data-download="([^"]+)".*?>#';

function byteConvert($bytes) {
	if ($bytes == 0) {
		return "0.00&nbsp;";
	}
	$s = array('&nbsp;', 'K', 'M', 'G', 'T', 'P');
	$e = floor(log($bytes, 1024));
	return round($bytes / pow(1024, $e), 2) . $s[$e];
}

include 'header.php';
?>
<!-- begin of static-download.php -->
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
					<article class="static article" id="static-page-<?= $plxShow->staticId(); ?>">
						<header class="static"><h2><?php $plxShow->staticTitle(); ?></h2></header>
<?php
// On capture le contenu de la page statique
ob_start();
$plxShow->staticContent();
$output = ob_get_clean();

// On vérifie que ce contenu matches avec le motif ci-dessous

if (preg_match(PATTERN_STATIC_DOWNLOAD, $output, $matches)) {
	$root = PLX_ROOT . $plxMotor->aConf['medias'];
	$dir1 = $root . rtrim($matches[1], '/');
	if (is_dir($dir1)) {
		$files = glob($dir1 . '/*');
		if (!empty($files)) {
			$start = strlen($root);
			$description = array();
			$htaccess = $dir1 . '/.htaccess';
			if (file_exists($htaccess)) {
				foreach (array_map('trim', file($htaccess)) as $line) {
					if (preg_match('#^AddDescription\s+"([^"]+)"\s+([\w-]+\.\w+)$#i', $line, $matches)) {
						$description[$matches[2]] = trim($matches[1]);
					}
				}
			}

			// On capture le tableau des fichiers
			ob_start();
?>
				<div class="scrollable-table">
					<table>
						<thead>
							<tr class="color1">
								<th>&nbsp;</th>
								<th><?php $plxShow->lang('FILENAME'); ?></th>
								<th><?php $plxShow->lang('FILEDATE'); ?></th>
								<th><?php $plxShow->lang('FILESIZE'); ?></th>
								<th><?php $plxShow->lang('FILEDESCRIPTION'); ?></th>
							</tr>
						</thead>
						<tbody>
<?php
			foreach ($files as $filename) {
				$href = $plxMotor->urlRewrite('index.php?download/' . plxEncrypt::encryptId('/' . substr($filename, $start)));
				$f = basename($filename);
				$descr = isset($description[$f]) ? $description[$f] : '';
?>
							<tr>
								<td class="<?= pathinfo($filename, PATHINFO_EXTENSION) ?>">&nbsp;</td>
								<td><a href="<?= $href ?>" download="<?= basename($filename) ?>"><?= basename($filename) ?></a></td>
								<td><?= date('Y-m-d H:i', filemtime($filename)) ?></td>
								<td><?= byteConvert(filesize($filename)) ?></td>
								<td><?= $descr ?></td>
							</tr>
				<?php
			}
?>
						</tbody>
					</table>
				</div>
<?php
			echo preg_replace(PATTERN_STATIC_DOWNLOAD, '$0' . ob_get_clean(), $output);
		} else {
			echo preg_replace(PATTERN_STATIC_DOWNLOAD, '$0' . $plxShow->getLang('NOTHING_FOR_DOWNLOADING'), $output);
		}
	} else {
		echo preg_replace(PATTERN_STATIC_DOWNLOAD, '$0' . $plxShow->getLang('NO_DIR') . preg_replace('#^' . PLX_ROOT . '#', ' :<br />', $dir1), $output);
	}
} else {
?>
				<p class="alert red"><?php $plxShow->lang('STATIC_TAG_INFO'); ?></p>
				<pre><code>&lt;div data-download="<?php $plxShow->lang('DOWNLOAD_FOLDER'); ?>"&gt;&lt;/div&gt;</code></pre>
<?php
	echo $output;
}
?>
				</article>
			</div>
<?php
if(!defined('FULL_WIDTH')) {
	include 'sidebar.php';
}
?>
		</div>
	</main>
<!-- end of static-download.php -->
<?php
include 'footer.php';

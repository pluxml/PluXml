<?php
if(!defined('PLX_ROOT')) {
	exit;
}

const THEME_SLIDESHOW = true;

include 'header.php';
?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
					<article class="article static" id="static-page-<?= $plxShow->staticId() ?>">
						<header>
								<h2><?php $plxShow->staticTitle(); ?></h2>
				  		</header>
<!-- begin of static-gallery.php -->
<?php
ob_start();
$plxShow->staticContent();
$content = ob_get_clean();

$pattern = '@(data-gallery="([^"]+)"[^>]*?>)[^<]*@';
if(preg_match($pattern, $content, $matches)) {
	$imgsList = glob(PLX_ROOT . $plxMotor->aConf['medias'] . rtrim($matches[2], '/') .  '/*.tb.{jpg,jpeg,png,gif}', GLOB_BRACE);
	if(!empty($imgsList)) {
		ob_start();
?>
 <!-- Auto-generation by <?= basename(__FILE__) ?> -->
<?php
		$offset = strlen(PLX_ROOT);
		foreach($imgsList as $filename) {
			$src = substr($filename, $offset);
			$imgSize = getimagesize($filename);
			$title = ucfirst(preg_replace('@(?:\.tb)?\.(?:jpe?g|png|gif)$@', '', basename($filename)));
?>
					<figure>
						<a href="<?= preg_replace("@\.tb\.(jpe?g|png|gif)$@", ".$1", $src) ?>" target="_blank"><img src="<?= $src ?>" <?= !empty($imgSize) ? $imgSize[3] : '' ?> alt="<?= $title ?>" /></a>
						<figcaption><?= $title ?></figcaption>
					</figure>
<?php
		}
		$gallery = ob_get_clean();
		echo preg_replace($pattern, "$1" .  $gallery, $content);
	} else {
?>
				<p class="alert">
					<?= nl2br($plxShow->getLang('GALLERY_WITHOUT_PICTURE')) ?>
				</p>
<?php
		echo $content;
	}
} else {
?>
				<p class="alert"><?php $plxShow->lang('STATIC_TAG_INFO'); ?></p>
				<pre><code>&lt;div data-gallery="<?php $plxShow->lang('GALLERY_FOLDER'); ?>"&gt;&lt;/div&gt;</code></pre>
<?php
	echo $content;
}
?>
<!-- end of static-gallery.php -->
					</article>
				</div>
<?php
if(!defined('FULL_WIDTH')) {
	include 'sidebar.php';
}
?>
			</div>
		</div>
	</main>
	<div id="slideshow" data-interval="5000">
		<div class="overlay"></div>
		<figure>
			<img id="slideshow-img" />
			<figcaption>
				<span id="slideshow-counter"></span>
				<span id="slideshow-caption"></span>
				<span id="slideshow-close">❌</span>
			</figcaption>
		</figure>
		<div class="gallery">
			<div id="slideshow-prev" class="button">◀</div>
			<div id="slideshow-gallery"></div>
			<div id="slideshow-next" class="button">▶</div>
		</div>
	</div>
<?php
include 'footer.php';

<?php
require 'header.php';

if(!isset($plxShow)) {
	$plxShow = plxShow::getInstance();
}

?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="content <?= colsClass() ?>">
<?php require 'posts.php'; ?>
					<?php $plxShow->artFeed('rss',$plxShow->catId(), '<span><a href="#feedUrl" title="#feedTitle">#feedName</a></span>'); ?>
				</div>
<?php if(!defined('FULL_WIDTH')) { require 'sidebar.php'; } ?>
			</div>
		</div>
	</main>
<?php
require 'footer.php';

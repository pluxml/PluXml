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
					<ul class="repertory menu breadcrumb">
						<li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
						<li><?php $plxShow->tagName(); ?></li>
					</ul>
<?php require 'posts.php'; ?>
					<?php $plxShow->tagFeed('', '', '<span><a href="#feedUrl" title="#feedTitle">#feedName</a></span>') ?>
				</div>
<?php if(!defined('FULL_WIDTH')) { include 'sidebar.php'; } ?>
			</div>
		</div>
	</main>
<?php
require 'footer.php';

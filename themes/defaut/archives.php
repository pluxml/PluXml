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
						<li><?php echo plxDate::formatDate($plxShow->plxMotor->cible, $plxShow->lang('ARCHIVES').' #month #num_year(4)') ?></li>
					</ul>
<?php require 'posts.php'; ?>
					<?php $plxShow->artFeed('rss',$plxShow->catId(), '<span><a href="#feedUrl" title="#feedTitle">#feedName</a></span>'); ?>
				</div>
<?php if(!defined('FULL_WIDTH')) { require 'sidebar.php'; } ?>
			</div>
		</div>
	</main>
<?php
require 'footer.php';

<?php include 'header.php'; ?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
					<ul class="repertory menu breadcrumb">
						<li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
						<li><?php $plxShow->tagName(); ?></li>
					</ul>
<?php include 'posts.php'; ?>
					<p>
						<?php $plxShow->artFeed('rss',$plxShow->plxMotor->cible); ?>
					</p>
				</div>
<?php include 'sidebar.php'; ?>
			</div>
		</div>
	</main>
<?php
include 'footer.php';

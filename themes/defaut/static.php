<?php include 'header.php'; ?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
					<article class="article static" id="static-page-<?= $plxShow->staticId(); ?>">
						<header>
							<h2><?php $plxShow->staticTitle(); ?></h2>
						</header>
<?php $plxShow->staticContent(); ?>
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
<?php
include 'footer.php';

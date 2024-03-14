<?php include 'header.php'; ?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
					<article class="article">
						<header>
							<h2><?php $plxShow->lang('ERROR'); ?></h2>
						</header>
						<div class="alert orange text-center"><?php $plxShow->erreurMessage(); ?></div>
					</article>
				</div>
<?php
if (!defined('FULL_WIDTH')) {
	include 'sidebar.php';
}
?>
			</div>
		</div>
	</main>
<?php
include 'footer.php';

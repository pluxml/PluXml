<?php include 'header.php'; ?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
					<article class="article">
						<header>
							<h2><?php $plxShow->lang('ERROR'); ?></h2>
						</header>
						<p><?php $plxShow->erreurMessage(); ?></p>
					</article>
				</div>
<?php include 'sidebar.php'; ?>
			</div>
		</div>
	</main>
<?php
include 'footer.php';

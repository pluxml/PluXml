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
					<article class="article static" id="static-page-<?php echo $plxShow->staticId(); ?>">
						<header>
							<h2><?php $plxShow->staticTitle(); ?></h2>
						</header>
						<?php $plxShow->staticContent(); ?>
					</article>
				</div>
<?php if(!defined('FULL_WIDTH')) { include 'sidebar.php'; } ?>
			</div>
		</div>
	</main>
<?php
require 'footer.php';

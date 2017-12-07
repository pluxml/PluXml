<?php include(dirname(__FILE__).'/header.php'); ?>

					<article class="article static" id="static-page-<?php echo $plxShow->staticId(); ?>">

						<header>
							<h2>
								<?php $plxShow->staticTitle(); ?>
							</h2>
						</header>

						<?php $plxShow->staticContent(); ?>

					</article>

<?php include(dirname(__FILE__).'/footer.php'); ?>
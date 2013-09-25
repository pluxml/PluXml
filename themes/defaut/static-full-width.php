<?php include(dirname(__FILE__) . '/header.php'); ?>

<section>

	<div id="container">

		<div class="full-width">

			<article role="article" id="static-<?php echo $plxShow->staticId(); ?>">

				<header>
					<h1>
						<?php $plxShow->staticTitle(); ?>
					</h1>
				</header>

				<section>
					<?php $plxShow->staticContent(); ?>
				</section>

			</article>

		</div>

	</div>

</section>

<?php include(dirname(__FILE__) . '/footer.php'); ?>

<?php include(dirname(__FILE__) . '/header.php'); ?>

<section>

	<div class="content">

		<div class="width-sidebar">

			<article role="article">

				<header role="banner">
					<h1>
						<?php $plxShow->staticTitle(); ?>
					</h1>
				</header>

				<section>
					<?php $plxShow->staticContent(); ?>
				</section>

			</article>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

</section>

<?php include(dirname(__FILE__) . '/footer.php'); ?>

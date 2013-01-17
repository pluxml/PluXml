<?php include(dirname(__FILE__) . '/header.php'); ?>

<section role="main">

	<div class="content">

		<div id="static-width-sidebar">

			<article role="article">

				<h1>
					<?php $plxShow->staticTitle(); ?>
				</h1>

				<?php $plxShow->staticContent(); ?>

			</article>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

</section>

<?php include(dirname(__FILE__) . '/footer.php'); ?>

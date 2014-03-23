<?php include(dirname(__FILE__).'/header.php'); ?>

<section>

	<div id="container">

		<div class="width-sidebar">

			<article role="article" id="static-page-<?php echo $plxShow->staticId(); ?>">

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

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

</section>

<?php include(dirname(__FILE__).'/footer.php'); ?>

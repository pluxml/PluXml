<?php include(dirname(__FILE__) . '/header.php'); ?>

<section role="main">

	<div class="content">

		<div id="static-width-sidebar">

			<article role="article">

				<h1>
					<?php $plxShow->lang('ERROR') ?>
				</h1>

				<p>
					<?php $plxShow->erreurMessage(); ?>
				</p>

			</article>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

</section>

<?php include(dirname(__FILE__) . '/footer.php'); ?>

<?php include(dirname(__FILE__).'/header.php'); ?>

<section>

	<div id="container">

		<div class="width-sidebar">

			<article role="article">

				<header>
					<h1>
						<?php $plxShow->lang('ERROR'); ?>
					</h1>
				</header>

				<section>
					<p>
						<?php $plxShow->erreurMessage(); ?>
					</p>
				</section>

			</article>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

</section>

<?php include(dirname(__FILE__).'/footer.php'); ?>

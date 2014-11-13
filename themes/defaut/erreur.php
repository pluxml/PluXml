<?php include(dirname(__FILE__).'/header.php'); ?>

	<main class="main grid" role="main">

		<section class="col sml-12 med-8">

			<article class="article" role="article">

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

		</section>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>


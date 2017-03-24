<?php include(dirname(__FILE__).'/header.php'); ?>

	<main class="main">

		<div class="container">

			<div class="grid">

				<div class="content col sml-12 med-8">

					<article class="article">

						<header>
							<h2>
								<?php $plxShow->lang('ERROR'); ?>
							</h2>
						</header>

						<p>
							<?php $plxShow->erreurMessage(); ?>
						</p>

					</article>

				</div>

				<?php include(dirname(__FILE__).'/sidebar.php'); ?>

			</div>

		</div>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>


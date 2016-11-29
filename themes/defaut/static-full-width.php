<?php include(dirname(__FILE__) . '/header.php'); ?>

	<main class="main" role="main">

		<div class="container">

			<div class="grid">

				<section class="col sml-12">

					<article class="article static" role="article" id="static-page-<?php echo $plxShow->staticId(); ?>">

						<header>
							<h1>
								<?php $plxShow->staticTitle(); ?>
							</h1>
						</header>

						<section>
							<?php $plxShow->staticContent(); ?>
						</section>

					</article>

				</section>

			</div>

		</div>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>


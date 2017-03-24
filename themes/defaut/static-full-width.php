<?php include(dirname(__FILE__) . '/header.php'); ?>

	<main class="main">

		<div class="container">

			<div class="grid">

				<div class="content col sml-12">

					<article class="article static" id="static-page-<?php echo $plxShow->staticId(); ?>">

						<header>
							<h2>
								<?php $plxShow->staticTitle(); ?>
							</h2>
						</header>

						<?php $plxShow->staticContent(); ?>

					</article>

				</div>

			</div>

		</div>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>


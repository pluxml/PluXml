<?php include(dirname(__FILE__).'/header.php'); ?>

	<main class="main">

		<div class="container">

			<div class="grid">

				<div class="col sml-12 med-8">

					<article class="article static" id="static-page-<?php echo $plxShow->staticId(); ?>">

						<header>
							<h2>
								<?php $plxShow->staticTitle(); ?>
							</h2>
						</header>

						<?php $plxShow->staticContent(); ?>
						
						<?php eval($plxShow->callHook('MyMultiLingue', 'staticlinks')) ?>

					</div>

				</section>

				<?php include(dirname(__FILE__).'/sidebar.php'); ?>

			</div>

		</div>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>

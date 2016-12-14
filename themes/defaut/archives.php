<?php include(dirname(__FILE__).'/header.php'); ?>

	<main class="main">

		<div class="container">

			<div class="grid">

				<div class="col sml-12 med-8">

					<ul class="repertory menu breadcrumb">
						<li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
						<li><?php echo plxDate::formatDate($plxShow->plxMotor->cible, $plxShow->lang('ARCHIVES').' #month #num_year(4)') ?></li>	
					</ul>

					<?php while($plxShow->plxMotor->plxRecord_arts->loop()): ?>

					<article class="article" id="post-<?php echo $plxShow->artId(); ?>">

						<header>
							<h2>
								<?php $plxShow->artTitle('link'); ?>
							</h2>
							<small>
								<?php $plxShow->lang('WRITTEN_BY'); ?> <?php $plxShow->artAuthor() ?> -
								<time datetime="<?php $plxShow->artDate('#num_year(4)-#num_month-#num_day'); ?>"><?php $plxShow->artDate('#num_day #month #num_year(4)'); ?></time> -
								<?php $plxShow->artNbCom(); ?>
							</small>
						</header>

						<?php $plxShow->artThumbnail(); ?>
						<?php $plxShow->artChapo(); ?>

						<footer>
							<small>
								<?php $plxShow->lang('CLASSIFIED_IN') ?> : <?php $plxShow->artCat() ?> - 
								<?php $plxShow->lang('TAGS') ?> : <?php $plxShow->artTags() ?>
							</small>
						</footer>

					</article>

					<?php endwhile; ?>

					<nav class="pagination text-center">
						<?php $plxShow->pagination(); ?>
					</nav>

					<span>
						<?php $plxShow->artFeed('rss',$plxShow->catId()); ?>
					</span>

				</div>

				<?php include(dirname(__FILE__).'/sidebar.php'); ?>

			</div>

		</div>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>

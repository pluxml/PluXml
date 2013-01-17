<?php include(dirname(__FILE__).'/header.php'); ?>

<section role="main">

	<div class="content">

		<div id="article-width-sidebar">

			<div class="cat-info">
				<p>
					<strong><?php $plxShow->catName(); ?></strong>
					<?php $plxShow->catDescription(' : #cat_description'); ?>
				</p>
			</div>

			<?php while($plxShow->plxMotor->plxRecord_arts->loop()): ?>

			<article role="article">

				<h1>
					<?php $plxShow->artTitle('link'); ?>
				</h1>

				<div class="article-info">
					<p>
						<?php $plxShow->artDate('#num_day #month #num_year(4)'); ?>
					</p>
				</div>

				<div class="article-content">
					<?php $plxShow->artChapo(); ?>
				</div>

				<div class="article-info">
					<p>
						<?php $plxShow->lang('WRITTEN_BY') ?> <?php $plxShow->artAuthor() ?> -
						<?php $plxShow->artNbCom(); ?>
					</p>
				</div>

				<div class="article-info">
					<p>
						<?php $plxShow->lang('CLASSIFIED_IN') ?> : <?php $plxShow->artCat(); ?>
					</p>
					<p>
						<?php $plxShow->lang('TAGS') ?> : <?php $plxShow->artTags(); ?>
					</p>
				</div>

			</article>

			<?php endwhile; ?>

			<div id="pagination">
				<?php $plxShow->pagination(); ?>
			</div>

			<div class="rss">
				<?php $plxShow->artFeed('rss',$plxShow->catId()); ?>
			</div>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

</section>

<?php include(dirname(__FILE__).'/footer.php'); ?>

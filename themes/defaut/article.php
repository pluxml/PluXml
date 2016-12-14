<?php include(dirname(__FILE__).'/header.php'); ?>

	<main class="main">

		<div class="container">

			<div class="grid">

				<div class="col sml-12 med-8">

					<article class="article" id="post-<?php echo $plxShow->artId(); ?>">

						<header>
							<h2>
								<?php $plxShow->artTitle(); ?>
							</h2>
							<small>
								<?php $plxShow->lang('WRITTEN_BY'); ?> <?php $plxShow->artAuthor() ?> -
								<time datetime="<?php $plxShow->artDate('#num_year(4)-#num_month-#num_day'); ?>"><?php $plxShow->artDate('#num_day #month #num_year(4)'); ?></time> -
								<a href="<?php $plxShow->artUrl(); ?>#comments" title="<?php $plxShow->artNbCom(); ?>"><?php $plxShow->artNbCom(); ?></a>
							</small>
						</header>

						<?php $plxShow->artThumbnail(); ?>
						<?php $plxShow->artContent(); ?>

						<footer>
							<small>
								<?php $plxShow->lang('CLASSIFIED_IN') ?> : <?php $plxShow->artCat() ?> -
								<?php $plxShow->lang('TAGS') ?> : <?php $plxShow->artTags() ?>
							</small>
						</footer>

					</article>

					<?php $plxShow->artAuthorInfos('<div class="author-infos">#art_authorinfos</div>'); ?>

					<?php include(dirname(__FILE__).'/commentaires.php'); ?>

				</div>

				<?php include(dirname(__FILE__).'/sidebar.php'); ?>

			</div>

		</div>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>

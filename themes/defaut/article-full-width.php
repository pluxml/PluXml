<?php include(dirname(__FILE__) . '/header.php'); ?>

	<main class="main grid" role="main">

		<section class="col sml-12">

			<article class="article" role="article" id="post-<?php echo $plxShow->artId(); ?>">

				<header>
					<h1>
						<?php $plxShow->artTitle(); ?>
					</h1>
					<small>
						<?php $plxShow->lang('WRITTEN_BY'); ?> <?php $plxShow->artAuthor() ?> -
						<time datetime="<?php $plxShow->artDate('#num_year(4)-#num_month-#num_day'); ?>"><?php $plxShow->artDate('#num_day #month #num_year(4)'); ?></time> -
						<a href="<?php $plxShow->artUrl(); ?>#comments" title="<?php $plxShow->artNbCom(); ?>"><?php $plxShow->artNbCom(); ?></a>
					</small>
				</header>

				<section>
					<?php $plxShow->artThumbnail(); ?>
					<?php $plxShow->artContent(); ?>
				</section>

				<footer>
					<small>
						<?php $plxShow->lang('CLASSIFIED_IN') ?> : <?php $plxShow->artCat() ?> -
						<?php $plxShow->lang('TAGS') ?> : <?php $plxShow->artTags() ?>
					</small>
				</footer>

			</article>

			<?php $plxShow->artAuthorInfos('<div class="author-infos">#art_authorinfos</div>'); ?>

			<?php include(dirname(__FILE__).'/commentaires.php'); ?>

		</section>

	</main>

<?php include(dirname(__FILE__).'/footer.php'); ?>

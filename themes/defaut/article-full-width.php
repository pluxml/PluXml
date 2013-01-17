<?php include(dirname(__FILE__) . '/header.php'); ?>

<section role="main">

	<div class="content">

		<div id="article-full-width">

			<article role="article">

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
					<?php $plxShow->artContent(); ?>
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

			<?php $plxShow->artAuthorInfos('<div class="author-infos">#art_authorinfos</div>'); ?>

			<?php include(dirname(__FILE__).'/commentaires.php'); ?>

			</article>

		</div>

	</div>

</section>

<?php include(dirname(__FILE__) . '/footer.php'); ?>

<?php include(dirname(__FILE__).'/header.php'); ?>

	<div id="section">

		<div id="article">

			<?php while($plxShow->plxMotor->plxRecord_arts->loop()): ?>
				<h2><?php $plxShow->artTitle('link'); ?></h2>
				<p class="art-topinfos"><?php $plxShow->lang('WRITTEN_BY') ?> <?php $plxShow->artAuthor() ?> le <?php $plxShow->artDate('#num_day #month #num_year(4)'); ?></p>
				<div class="art-chapo"><?php $plxShow->artChapo(); ?></div>
				<p class="art-infos"><?php $plxShow->lang('CLASSIFIED_IN') ?> : <?php $plxShow->artCat(); ?> - <?php $plxShow->lang('TAGS') ?> : <?php $plxShow->artTags(); ?> - <?php $plxShow->artNbCom(); ?></p>
			<?php endwhile; ?>

			<p id="pagination"><?php $plxShow->pagination(); ?></p>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

<?php include(dirname(__FILE__).'/footer.php'); ?>


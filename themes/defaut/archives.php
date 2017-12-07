<?php include(dirname(__FILE__).'/header.php'); ?>

					<ul class="repertory menu breadcrumb">
						<li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
						<li><?php echo plxDate::formatDate($plxShow->plxMotor->cible, $plxShow->lang('ARCHIVES').' #month #num_year(4)') ?></li>
					</ul>

<?php include('articles-loop.php'); ?>

					<span>
						<?php $plxShow->artFeed('rss',$plxShow->catId()); ?>
					</span>

<?php include(dirname(__FILE__).'/footer.php'); ?>
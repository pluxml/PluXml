<?php include(dirname(__FILE__).'/header.php'); ?>

					<ul class="repertory menu breadcrumb">
						<li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
						<li><?php $plxShow->catName(); ?></li>
					</ul>

					<p><?php $plxShow->catDescription('#cat_description'); ?></p>

<?php include('articles-loop.php'); ?>

					<span>
						<?php $plxShow->artFeed('rss',$plxShow->catId()); ?>
					</span>

<?php include(dirname(__FILE__).'/footer.php'); ?>
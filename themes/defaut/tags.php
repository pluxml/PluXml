<?php include(dirname(__FILE__).'/header.php'); ?>

					<ul class="repertory menu breadcrumb">
						<li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
						<li><?php $plxShow->tagName(); ?></li>
					</ul>

<?php include('articles-loop.php'); ?>

					<span>
						<?php $plxShow->tagFeed() ?>
					</span>

<?php include(dirname(__FILE__).'/footer.php'); ?>
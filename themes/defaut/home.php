<?php include(dirname(__FILE__).'/header.php'); ?>

<?php include('articles-loop.php'); ?>

					<span>
						<?php $plxShow->artFeed('rss',$plxShow->catId()); ?>
					</span>

<?php include(dirname(__FILE__).'/footer.php'); ?>
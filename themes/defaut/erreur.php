<?php include(dirname(__FILE__).'/header.php'); ?>

					<article class="article">
						<header>
							<h2>
								<?php $plxShow->lang('ERROR'); ?>
							</h2>
						</header>
						<p>
							<?php $plxShow->erreurMessage(); ?>
						</p>
					</article>

<?php include(dirname(__FILE__).'/footer.php'); ?>
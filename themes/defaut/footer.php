<?php if (!defined('PLX_ROOT')) exit; ?>

		<footer class="footer" role="contentinfo">

			<div class="container">

				<div class="grid">

					<div class="col sma-12 med-8 lrg-9">

						<ul class="unstyled-list">
							<li>© 2014 <?php $plxShow->mainTitle('link'); ?> - 
							<?php $plxShow->subTitle(); ?></li>
							<li><?php $plxShow->lang('POWERED_BY') ?> <a href="http://www.pluxml.org" title="<?php $plxShow->lang('PLUXML_DESCRIPTION') ?>">PluXml</a>
							<?php $plxShow->lang('IN') ?> <?php $plxShow->chrono(); ?>
							<?php $plxShow->httpEncoding() ?></li>
							<li class="admin" ><small><a rel="nofollow" href="<?php $plxShow->urlRewrite('core/admin/'); ?>" title="<?php $plxShow->lang('ADMINISTRATION') ?>"><?php $plxShow->lang('ADMINISTRATION') ?></a></small></li>
						</ul>

					</div>

					<div class="col sma-12 med-4 lrg-3">

						<ul class="unstyled-list">
							<li><a href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" title="<?php $plxShow->lang('ARTICLES_RSS_FEEDS'); ?>"><?php $plxShow->lang('ARTICLES'); ?></a></li>
							<li><a href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires'); ?>" title="<?php $plxShow->lang('COMMENTS_RSS_FEEDS') ?>"><?php $plxShow->lang('COMMENTS'); ?></a></li>
							<li><a href="<?php $plxShow->urlRewrite('#top') ?>" title="<?php $plxShow->lang('GOTO_TOP') ?>"><?php $plxShow->lang('TOP') ?></a></li>
						</ul>

					</div>

				</div>

			</div>

		</footer>

</body>

</html>

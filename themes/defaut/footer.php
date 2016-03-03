<?php if (!defined('PLX_ROOT')) exit; ?>

		<footer class="footer" role="contentinfo">

				<p>
					&copy; 2016 <?php $plxShow->mainTitle('link'); ?> - 
					<?php $plxShow->subTitle(); ?> - 
					<?php $plxShow->lang('POWERED_BY') ?>&nbsp;<a href="http://www.pluxml.org" title="<?php $plxShow->lang('PLUXML_DESCRIPTION') ?>">PluXml</a>
					<?php $plxShow->lang('IN') ?>&nbsp;<?php $plxShow->chrono(); ?>&nbsp;
					<?php $plxShow->httpEncoding() ?>
				</p>
				<ul class="menu">
					<li><a href="<?php $plxShow->racine() ?>"><?php $plxShow->lang('HOME'); ?></a></li>
					<li><a href="<?php $plxShow->urlRewrite('#top') ?>" title="<?php $plxShow->lang('GOTO_TOP') ?>"><?php $plxShow->lang('TOP') ?></a></li>
					<li><a rel="nofollow" href="<?php $plxShow->urlRewrite('core/admin/'); ?>" title="<?php $plxShow->lang('ADMINISTRATION') ?>"><?php $plxShow->lang('ADMINISTRATION') ?></a></li>
				</ul>

		</footer>

</div>

</body>

</html>

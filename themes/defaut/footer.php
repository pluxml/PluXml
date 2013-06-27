<?php if (!defined('PLX_ROOT')) exit; ?>

<footer role="contentinfo">

	<div class="content">

		<p>
			<?php $plxShow->mainTitle('link'); ?> @ 2013 - <?php $plxShow->subTitle(); ?>
		</p>
		<p>
			<?php $plxShow->lang('POWERED_BY') ?> <a href="http://www.pluxml.org" title="<?php $plxShow->lang('PLUXML_DESCRIPTION') ?>">PluXml</a>
			<?php $plxShow->lang('IN') ?> <?php $plxShow->chrono(); ?> 
			<a rel="nofollow" href="<?php $plxShow->urlRewrite('core/admin/'); ?>" title="<?php $plxShow->lang('ADMINISTRATION') ?>"><?php $plxShow->lang('ADMINISTRATION') ?></a> 
			<a href="<?php echo $plxShow->urlRewrite('#top') ?>" title="<?php $plxShow->lang('GOTO_TOP') ?>"><?php $plxShow->lang('TOP') ?></a> 
			<?php $plxShow->httpEncoding() ?>
		</p>

	</div>

</footer>

</body>

</html>

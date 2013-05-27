<?php if (!defined('PLX_ROOT')) exit; ?>

<footer role="contentinfo">

	<div class="content">

		<p>
			&copy; 2013 <?php $plxShow->mainTitle('link'); ?> - <?php $plxShow->subTitle(); ?>
		</p>
		<p>
			<?php $plxShow->lang('POWERED_BY') ?> <a href="http://www.pluxml.org" title="<?php $plxShow->lang('PLUXML_DESCRIPTION') ?>">PluXml</a>
			<?php $plxShow->lang('IN') ?> <?php $plxShow->chrono(); ?>&nbsp;
			<a rel="nofollow" href="<?php $plxShow->urlRewrite('core/admin/') ?>" title="<?php $plxShow->lang('ADMINISTRATION') ?>"><?php $plxShow->lang('ADMINISTRATION') ?></a>&nbsp;
			<a href="<?php echo $plxShow->urlRewrite('#top') ?>" title="<?php $plxShow->lang('GOTO_TOP') ?>"><?php $plxShow->lang('TOP') ?></a>&nbsp;
			<?php $plxShow->httpEncoding() ?>
		</p>

	</div>

</footer>

</body>

</html>

<?php if(!defined('PLX_ROOT')) exit; ?>

	<div id="footer">

		<p>&copy; <?php $plxShow->mainTitle('link'); ?> -
			<?php $plxShow->lang('POWERED_BY') ?> <a href="http://www.pluxml.org" title="<?php $plxShow->lang('PLUXML_DESCRIPTION') ?>">PluXml</a>
			<?php $plxShow->lang('IN') ?> <?php $plxShow->chrono(); ?>
			<?php $plxShow->httpEncoding() ?>
			<span><a class="admin" rel="nofollow" href="<?php $plxShow->urlRewrite('core/admin/') ?>" title="<?php $plxShow->lang('ADMINISTRATION') ?>"><?php $plxShow->lang('ADMINISTRATION') ?></a> -
			<a class="top" href="<?php echo $plxShow->urlRewrite('#top') ?>" title="<?php $plxShow->lang('GOTO_TOP') ?>"><?php $plxShow->lang('TOP') ?></a></span>
		</p>

	</div>

</body>
</html>

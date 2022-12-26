<?php if (!defined('PLX_ROOT')) exit; ?>

	<footer class="footer">
		<div class="container">
			<p>
				<?php $plxShow->mainTitle('link'); ?> - <?php $plxShow->subTitle(); ?> &copy; 2018
			</p>
			<p>
				<?php $plxShow->lang('POWERED_BY') ?>&nbsp;<a href="<?= PLX_URL_REPO?>" title="<?php $plxShow->lang('PLUXML_DESCRIPTION') ?>">PluXml</a>
				<?php $plxShow->lang('IN') ?>&nbsp;<?php $plxShow->chrono(); ?>&nbsp;
				<?php $plxShow->httpEncoding() ?>&nbsp;-
				<a rel="nofollow" href="<?php $plxShow->urlRewrite('core/admin/'); ?>" title="<?php $plxShow->lang('ADMINISTRATION') ?>"><?php $plxShow->lang('ADMINISTRATION') ?></a>
			</p>
			<ul class="menu">
<?php  if($plxShow->plxMotor->aConf['enable_rss']) { ?>
				<li><?php $plxShow->artFeed(); ?></li>
<?php } ?>
<?php if($plxShow->plxMotor->aConf['enable_rss_comment']) { ?>
					<li><?php $plxShow->comFeed() ?></li>
<?php  } ?>
				<li><a href="<?php $plxShow->urlRewrite('#top') ?>" title="<?php $plxShow->lang('GOTO_TOP') ?>"><?php $plxShow->lang('TOP') ?></a></li>
			</ul>
		</div>
	</footer>
</body>
</html>

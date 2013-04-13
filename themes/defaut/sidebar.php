<?php if(!defined('PLX_ROOT')) exit; ?>

	<aside role="complementary">

		<div class="aside-title">
			<?php $plxShow->lang('CATEGORIES') ?>
		</div>

		<div class="aside-content">
			<ul>
				<?php $plxShow->catList('','<li id="#cat_id"><a class="#cat_status" href="#cat_url" title="#cat_name">#cat_name</a> (#art_nb)</li>'); ?>
			</ul>
		</div>

		<div class="aside-title">
			<?php $plxShow->lang('LATEST_ARTICLES') ?>
		</div>

		<div class="aside-content">
			<ul>
				<?php $plxShow->lastArtList('<li><a class="#art_status" href="#art_url" title="#art_title">#art_title</a></li>'); ?>
			</ul>
		</div>

		<div class="aside-title">
			<?php $plxShow->lang('TAGS') ?>
		</div>

		<div class="aside-content">
			<?php $plxShow->tagList('<span class="tag #tag_size"><a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a></span>', 20); ?>
		</div>

		<div class="aside-title">
			<?php $plxShow->lang('LATEST_COMMENTS') ?>
		</div>

		<div class="aside-content">
			<ul>
				<?php $plxShow->lastComList('<li><a href="#com_url">#com_author '.$plxShow->getLang('SAID').' : #com_content(34)</a></li>'); ?>
			</ul>
		</div>

		<div class="aside-title">
			<?php $plxShow->lang('ARCHIVES') ?>
		</div>

		<div class="aside-content">
			<ul>
				<?php $plxShow->archList('<li id="#archives_id"><a class="#archives_status" href="#archives_url" title="#archives_name">#archives_name</a> (#archives_nbart)</li>'); ?>
			</ul>
		</div>

		<ul>
			<li class="rss"><a href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" title="<?php $plxShow->lang('ARTICLES_RSS_FEEDS') ?>">
				<?php $plxShow->lang('ARTICLES') ?></a>
			</li>
			<li class="rss"><a href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" title="<?php $plxShow->lang('COMMENTS_RSS_FEEDS') ?>">
				<?php $plxShow->lang('COMMENTS') ?></a>
			</li>
		</ul>

	</aside>

<?php if(!defined('PLX_ROOT')) exit; ?>

	<div id="aside">

		<h3><?php $plxShow->lang('CATEGORIES') ?></h3>
		<ul>
			<?php $plxShow->catList('','<li id="#cat_id" class="#cat_status"><a href="#cat_url" title="#cat_name">#cat_name</a> (#art_nb)</li>'); ?>
		</ul>

		<h3><?php $plxShow->lang('ARCHIVES') ?></h3>
        <ul>
            <?php $plxShow->archList('<li id="#archives_id" class="#archives_status"><a href="#archives_url" title="#archives_name">#archives_name</a> (#archives_nbart)</li>'); ?>
        </ul>

        <h3><?php $plxShow->lang('TAGS') ?></h3>
		<ul>
			<?php $plxShow->tagList('<li class="#tag_status"><a href="#tag_url" title="#tag_name">#tag_name</a></li>', 20); ?>
		</ul>

		<h3><?php $plxShow->lang('LAST_ARTICLES') ?></h3>
		<ul>
			<?php $plxShow->lastArtList('<li class="#art_status"><a href="#art_url" title="#art_title">#art_title</a></li>'); ?>
		</ul>

		<h3><?php $plxShow->lang('LAST_COMMENTS') ?></h3>
		<ul>
			<?php $plxShow->lastComList('<li><a href="#com_url">#com_author '.$plxShow->getLang('SAID').' : #com_content(20)</a></li>'); ?>
		</ul>

	</div>

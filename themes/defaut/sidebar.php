<?php
if(!isset($plxShow)) {
	$plxShow = plxShow::getInstance();
}

$lastDisplay = ! in_array($plxShow->mode(), array('categorie', 'tags', 'archives'));
?>
	<aside class="aside col sml-12 med-3">
		<details <?php if($plxShow->mode() == 'categorie') { echo 'open'; } ?>>
			<summary>
				<h3><?php $plxShow->lang('CATEGORIES'); ?></h3>
			</summary>
			<ul class="cat-list unstyled-list">
<?php $plxShow->catList('','<li id="#cat_id"><a class="#cat_status" href="#cat_url" title="#cat_name">#cat_name</a> (#art_nb)</li>'); ?>
			</ul>
		</details>
		<details <?php if($lastDisplay) { echo 'open'; } ?>>
			<summary>
				<h3><?php $plxShow->lang('LATEST_ARTICLES'); ?></h3>
			</summary>
			<ul class="lastart-list unstyled-list">
<?php $plxShow->lastArtList('<li><a class="#art_status" href="#art_url" title="#art_title">#art_title</a></li>'); ?>
			</ul>
		</details>
		<details <?php if($plxShow->mode() == 'tags') { echo 'open'; } ?>>
			<summary>
				<h3><?php $plxShow->lang('TAGS'); ?></h3>
			</summary>
			<ul class="tag-list">
<?php $plxShow->tagList('<li class="tag #tag_size"><a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a></li>', 20); ?>
			</ul>
		</details>
		<details <?php if($lastDisplay) { echo 'open'; } ?>>
			<summary>
				<h3><?php $plxShow->lang('LATEST_COMMENTS'); ?></h3>
			</summary>
			<ul class="lastcom-list unstyled-list">
<?php $plxShow->lastComList('<li><a href="#com_url">#com_author '.$plxShow->getLang('SAID').' : #com_content(34)</a></li>'); ?>
			</ul>
		</details>
		<details <?php if($plxShow->mode() == 'archives') { echo 'open'; } ?>>
			<summary>
				<h3><?php $plxShow->lang('ARCHIVES'); ?></h3>
			</summary>
			<ul class="arch-list unstyled-list">
<?php $plxShow->archList('<li id="#archives_id"><a class="#archives_status" href="#archives_url" title="#archives_name">#archives_name</a><span>(#archives_nbart)</span></li>'); ?>
			</ul>
		</details>
	</aside>

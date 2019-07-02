<?php ob_start(); ?>

<?php 
$adminTitle = (empty($_GET['a']))?L_MENU_NEW_ARTICLES:L_ARTICLE_EDITING;
?>

<script>
function refreshImg(dta) {
	if(dta.trim()==='') {
		document.getElementById('id_thumbnail_img').innerHTML = '';
	} else {
		var link = dta.match(/^(?:https?|data):/gi) ? dta : '<?php echo $plxAdmin->racine ?>'+dta;
		document.getElementById('id_thumbnail_img').innerHTML = '<img src="'+link+'" alt="" />';
	}
}
</script>

<form action="article.php" method="post" id="form_article">

	<div class="inline-form admin-title">

		<div class="grid">
			<div class="col sml-12 med-7">
				<input type="submit" name="preview" onclick="this.form.target='_blank';return true;" value="<?php echo L_ARTICLE_PREVIEW_BUTTON ?>"/>
				<?php
					if($_SESSION['profil']>PROFIL_MODERATOR AND $plxAdmin->aConf['mod_art']) {
						if(in_array('draft', $catId)) { # brouillon
							if($artId!='0000') # nouvel article
							echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/> ';
							echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="moderate" value="'.L_ARTICLE_MODERATE_BUTTON.'"/> ';
							echo '&nbsp;&nbsp;&nbsp;<input class="red" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" /> ';
						} else {
							if(isset($_GET['a']) AND preg_match('/^_[0-9]{4}$/',$_GET['a'])) { # en attente
								echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="update" value="' . L_ARTICLE_UPDATE_BUTTON . '"/> ';
								echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/> ';
								echo '&nbsp;&nbsp;&nbsp;<input class="red" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" /> ';
							} else {
								echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="'.L_ARTICLE_DRAFT_BUTTON.'"/> ';
								echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="moderate" value="'.L_ARTICLE_MODERATE_BUTTON.'"/> ';
							}
						}
					} else {
						if(in_array('draft', $catId)) {
							echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="' . L_ARTICLE_DRAFT_BUTTON . '"/> ';
							echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="publish" value="' . L_ARTICLE_PUBLISHING_BUTTON . '"/> ';
						} else {
							if(!isset($_GET['a']) OR preg_match('/^_[0-9]{4}$/',$_GET['a']))
								echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="publish" value="' . L_ARTICLE_PUBLISHING_BUTTON . '"/> ';
							else
								echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="update" value="' . L_ARTICLE_UPDATE_BUTTON . '"/> ';
								echo '<input onclick="this.form.target=\'_self\';return true;" type="submit" name="draft" value="' . L_ARTICLE_OFFLINE_BUTTON . '"/> ';
						}
						if($artId!='0000')
							echo '&nbsp;&nbsp;&nbsp;<input class="red" type="submit" name="delete" value="'.L_DELETE.'" onclick="Check=confirm(\''.L_ARTICLE_DELETE_CONFIRM.'\');if(Check==false) {return false;} else {this.form.target=\'_self\';return true;}" /> ';
					}
				?>
			</div>
		</div>

	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleTop')) # Hook Plugins ?>

	<div class="grid">

		<div class="col sml-12 med-7 lrg-8">

			<fieldset>
				<div class="grid">
					<div class="col sml-12">
						<?php plxUtils::printInput('artId',$artId,'hidden'); ?>
						<label for="id_title"><?php echo L_ARTICLE_TITLE ?>&nbsp;:</label>
						<?php plxUtils::printInput('title',plxUtils::strCheck($title),'text','42-255',false,'full-width'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12 small">
						<?php if($artId!='' AND $artId!='0000') : ?>
							<?php $link = $plxAdmin->urlRewrite('?article'.intval($artId).'/'.$url) ?>
					 			<small>
					 				<strong><?php echo L_LINK_FIELD ?>&nbsp;(<a href="#articleLink" style="text-transform: lowercase;"><?php echo L_ARTICLE_EDIT ?></a>)&nbsp;:&nbsp;</strong>
					 				<a onclick="this.target=\'_blank\';return true;" href="<?php echo $link ?>" title="<?php echo L_LINK_ACCESS ?> : <?php echo $link ?>"><?php echo $link ?></a>
					 			</small>
						<?php endif; ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<input class="toggler" type="checkbox" id="toggler_chapo"<?php echo (empty($_GET['a']) || ! empty(trim($chapo))) ? ' unchecked' : ''; ?> />
						<label for="toggler_chapo"><?php echo L_HEADLINE_FIELD;?> : <span><?php echo L_ARTICLE_CHAPO_HIDE;?></span><span><?php echo L_ARTICLE_CHAPO_DISPLAY;?></span></label>
						<div>
							<?php plxUtils::printArea('chapo',plxUtils::strCheck($chapo),0,8); ?>
						</div>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_content"><?php echo L_CONTENT_FIELD ?>&nbsp;:</label>
						<?php plxUtils::printArea('content',plxUtils::strCheck($content),0,20); ?>
					</div>
				</div>
				<?php if($artId!='' AND $artId!='0000') : ?>
				<div class="grid">
					<div id="articleLink" class="col sml-12">
						<?php $link = $plxAdmin->urlRewrite('?article'.intval($artId).'/'.$url) ?>
						<label for="id_link"><?php echo L_LINK_FIELD ?>&nbsp;:&nbsp;<?php echo '<a onclick="this.target=\'_blank\';return true;" href="'.$link.'" title="'.L_LINK_ACCESS.'">'.L_LINK_VIEW.'</a>'; ?></label>
						<?php echo '<input id="id_link" onclick="this.select()" readonly="readonly" type="text" value="'.$link.'" />' ?>
					</div>
				</div>
				<?php endif; ?>
			</fieldset>
			<div class="grid gridthumb">
				<div class="col sml-12">
					<label for="id_thumbnail">
						<?php echo L_THUMBNAIL ?>&nbsp;:&nbsp;
						<a title="<?php echo L_THUMBNAIL_SELECTION ?>" id="toggler_thumbnail" href="javascript:void(0)" onclick="mediasManager.openPopup('id_thumbnail', true)" style="outline:none; text-decoration: none">+</a>
					</label>
					<?php plxUtils::printInput('thumbnail',plxUtils::strCheck($thumbnail),'text','255',false,'full-width','','onkeyup="refreshImg(this.value)"'); ?>
					<div class="grid" style="padding-top:10px">
						<div class="col sml-12 lrg-6">
							<label for="id_thumbnail_alt"><?php echo L_THUMBNAIL_TITLE ?>&nbsp;:</label>
							<?php plxUtils::printInput('thumbnail_title',plxUtils::strCheck($thumbnail_title),'text','255-255',false,'full-width'); ?>
						</div>
						<div class="col sml-12 lrg-6">
							<label for="id_thumbnail_alt"><?php echo L_THUMBNAIL_ALT ?>&nbsp;:</label>
							<?php plxUtils::printInput('thumbnail_alt',plxUtils::strCheck($thumbnail_alt),'text','255-255',false,'full-width'); ?>
						</div>
					</div>
					<div id="id_thumbnail_img">
					<?php
					$src = false;
					if(preg_match('@^(?:https?|data):@', $thumbnail)) {
						$src = $thumbnail;
					} else {
						$src = PLX_ROOT.$thumbnail;
						$src = is_file($src) ? $src : false;
					}
					if($src) echo "<img src=\"$src\" title=\"$thumbnail\" />\n";
					?>
					</div>
				</div>
			</div>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleContent')) ?>
			<?php echo plxToken::getTokenPostMethod() ?>
		</div>

		<div class="sidebar col sml-12 med-5 lrg-4">

			<p><?php echo L_ARTICLE_STATUS ?>&nbsp;:&nbsp;
				<strong>
				<?php
				if(isset($_GET['a']) AND preg_match('/^_[0-9]{4}$/',$_GET['a']))
					echo L_AWAITING;
				elseif(in_array('draft', $catId)) {
					echo L_DRAFT;
					echo '<input type="hidden" name="catId[]" value="draft" />';
				}
				else
					echo L_PUBLISHED;
				?>
				</strong>
			</p>
			<fieldset>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_author"><?php echo L_ARTICLE_LIST_AUTHORS ?>&nbsp;:&nbsp;</label>
						<?php
						if($_SESSION['profil'] < PROFIL_WRITER)
							plxUtils::printSelect('author', $_users, $author);
						else {
							echo '<input type="hidden" id="id_author" name="author" value="'.$author.'" />';
							echo '<strong>'.plxUtils::strCheck($plxAdmin->aUsers[$author]['name']).'</strong>';
						}
						?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label><?php echo L_ARTICLE_DATE ?>&nbsp;:</label>
						<div class="inline-form publication">
							<?php plxUtils::printInput('date_publication_day',$date['day'],'text','2-2',false,'day'); ?>
							<?php plxUtils::printInput('date_publication_month',$date['month'],'text','2-2',false,'month'); ?>
							<?php plxUtils::printInput('date_publication_year',$date['year'],'text','2-4',false,'year'); ?>
							<?php plxUtils::printInput('date_publication_time',$date['time'],'text','2-5',false,'time'); ?>
							<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_publication', <?php echo date('Z') ?>); return false;" title="<?php L_NOW; ?>">
								<img src="theme/images/date.png" alt="calendar" />
							</a>
						</div>
					</div>
				</div>
			<div class="grid">
				<div class="col sml-12">
					<label><?php echo L_DATE_CREATION ?>&nbsp;:</label>
					<div class="inline-form creation">
						<?php plxUtils::printInput('date_creation_day',$date_creation['day'],'text','2-2',false,'day'); ?>
						<?php plxUtils::printInput('date_creation_month',$date_creation['month'],'text','2-2',false,'month'); ?>
						<?php plxUtils::printInput('date_creation_year',$date_creation['year'],'text','2-4',false,'year'); ?>
						<?php plxUtils::printInput('date_creation_time',$date_creation['time'],'text','2-5',false,'time'); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_creation', <?php echo date('Z') ?>); return false;" title="<?php L_NOW; ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12">
					<?php plxUtils::printInput('date_update_old', $date_update_old, 'hidden'); ?>
					<label><?php echo L_DATE_UPDATE ?>&nbsp;:</label>
					<div class="inline-form update">
						<?php plxUtils::printInput('date_update_day',$date_update['day'],'text','2-2',false,'day'); ?>
						<?php plxUtils::printInput('date_update_month',$date_update['month'],'text','2-2',false,'month'); ?>
						<?php plxUtils::printInput('date_update_year',$date_update['year'],'text','2-4',false,'year'); ?>
						<?php plxUtils::printInput('date_update_time',$date_update['time'],'text','2-5',false,'time'); ?>
						<a class="ico_cal" href="javascript:void(0)" onclick="dateNow('date_update', <?php echo date('Z') ?>); return false;" title="<?php L_NOW; ?>">
							<img src="theme/images/date.png" alt="calendar" />
						</a>
					</div>
				</div>
			</div>

				<div class="grid">
					<div class="col sml-12">
						<label><?php echo L_ARTICLE_CATEGORIES ?>&nbsp;:</label>
						<?php
							$selected = (is_array($catId) AND in_array('000', $catId)) ? ' checked="checked"' : '';
							echo '<label for="cat_unclassified"><input class="no-margin" disabled="disabled" type="checkbox" id="cat_unclassified" name="catId[]"'.$selected.' value="000" />&nbsp;'. L_UNCLASSIFIED .'</label>';
							$selected = (is_array($catId) AND in_array('home', $catId)) ? ' checked="checked"' : '';
							echo '<label for="cat_home"><input type="checkbox" class="no-margin" id="cat_home" name="catId[]"'.$selected.' value="home" />&nbsp;'. L_CATEGORY_HOME_PAGE .'</label>';
							foreach($plxAdmin->aCats as $cat_id => $cat_name) {
								$selected = (is_array($catId) AND in_array($cat_id, $catId)) ? ' checked="checked"' : '';
								if($plxAdmin->aCats[$cat_id]['active'])
									echo '<label for="cat_'.$cat_id.'">'.'<input type="checkbox" class="no-margin" id="cat_'.$cat_id.'" name="catId[]"'.$selected.' value="'.$cat_id.'" />&nbsp;'.plxUtils::strCheck($cat_name['name']).'</label>';
								else
									echo '<label for="cat_'.$cat_id.'">'.'<input type="checkbox" class="no-margin" id="cat_'.$cat_id.'" name="catId[]"'.$selected.' value="'.$cat_id.'" />&nbsp;'.plxUtils::strCheck($cat_name['name']).'</label>';
							}
						?>
					</div>
				</div>

				<?php if($_SESSION['profil'] < PROFIL_WRITER) : ?>

				<div class="grid">
					<div class="col sml-12">
						<label for="id_new_catname"><?php echo L_NEW_CATEGORY ?>&nbsp;:</label>
						<div class="inline-form">
							<?php plxUtils::printInput('new_catname','','text','17-50')	?>
							<input type="submit" name="new_category" value="<?php echo L_CATEGORY_ADD_BUTTON ?>" />
						</div>
					</div>
				</div>

				<?php endif; ?>

				<div class="grid">
					<div class="col sml-12">
						<label for="tags"><?php echo L_ARTICLE_TAGS_FIELD; ?>&nbsp;:&nbsp;<a class="hint"><span><?php echo L_ARTICLE_TAGS_FIELD_TITLE; ?></span></a></label>
						<?php plxUtils::printInput('tags',$tags,'text','25-255',false,false); ?>
                        <input class="toggler" type="checkbox" id="toggler_tags"<?php echo (empty($_GET['a']) || ! empty(trim($tags))) ? ' unchecked' : ''; ?> />
                        <label for="toggler_tags"><span>-</span><span>+</span></label>						
						<div style="margin-top: 1rem">
							<?php
							if($plxAdmin->aTags) {
								$array=array();
								foreach($plxAdmin->aTags as $tag) {
									if($tags = array_map('trim', explode(',', $tag['tags']))) {
										foreach($tags as $tag) {
											if($tag!='') {
												$t = plxUtils::title2url($tag);
												if(!isset($array[$tag]))
													$array[$tag]=array('url'=>$t,'count'=>1);
												else
													$array[$tag]['count']++;
											}
										}
									}
								}
								array_multisort($array);
								foreach($array as $tagname => $tag) {
									echo '<a href="javascript:void(0)" onclick="insTag(\'tags\',\''.addslashes($tagname).'\')" title="'.plxUtils::strCheck($tagname).' ('.$tag['count'].')">'.plxUtils::strCheck($tagname).'</a> ('.$tag['count'].')&nbsp;&nbsp;';
								}
							}
							else echo L_NO_TAG;
							?>
						</div>
					</div>
				</div>

				<div class="grid">
					<div class="col sml-12">
						<?php if($plxAdmin->aConf['allow_com']=='1') : ?>
						<label for="id_allow_com"><?php echo L_ALLOW_COMMENTS ?>&nbsp;:</label>
						<?php plxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO),$allow_com); ?>
						<?php else: ?>
						<?php plxUtils::printInput('allow_com','0','hidden'); ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_url">
							<?php echo L_ARTICLE_URL_FIELD ?>&nbsp;:&nbsp;<a class="hint"><span><?php echo L_ARTICLE_URL_FIELD_TITLE ?></span></a>
						</label>
						<?php plxUtils::printInput('url',$url,'text','27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_template"><?php echo L_ARTICLE_TEMPLATE_FIELD ?>&nbsp;:</label>
						<?php plxUtils::printSelect('template', $aTemplates, $template); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_title_htmltag"><?php echo L_ARTICLE_TITLE_HTMLTAG ?>&nbsp;:</label>
						<?php plxUtils::printInput('title_htmltag',plxUtils::strCheck($title_htmltag),'text','27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_meta_description"><?php echo L_ARTICLE_META_DESCRIPTION ?>&nbsp;:</label>
						<?php plxUtils::printInput('meta_description',plxUtils::strCheck($meta_description),'text','27-255'); ?>
					</div>
				</div>
				<div class="grid">
					<div class="col sml-12">
						<label for="id_meta_keywords"><?php echo L_ARTICLE_META_KEYWORDS ?>&nbsp;:</label>
						<?php plxUtils::printInput('meta_keywords',plxUtils::strCheck($meta_keywords),'text','27-255'); ?>
					</div>
				</div>

				<?php eval($plxAdmin->plxPlugins->callHook('AdminArticleSidebar')) # Hook Plugins ?>

				<?php if($artId != '0000') : ?>
				<ul class="unstyled-list">
					<li>
						<a href="comments.php?a=<?php echo $artId ?>&amp;page=1" title="<?php echo L_ARTICLE_MANAGE_COMMENTS_TITLE ?>"><?php echo L_ARTICLE_MANAGE_COMMENTS ?></a>
						<?php
						# récupération du nombre de commentaires
						$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$artId.'.(.*).xml$/','all');
						$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$artId.'.(.*).xml$/','all');
						?>
						<ul>
							<li><?php echo L_COMMENT_OFFLINE ?> : <a title="<?php echo L_NEW_COMMENTS_TITLE ?>" href="comments.php?sel=offline&amp;a=<?php echo $artId ?>&amp;page=1"><?php echo $nbComsToValidate ?></a></li>
							<li><?php echo L_COMMENT_ONLINE ?> : <a title="<?php echo L_VALIDATED_COMMENTS_TITLE ?>" href="comments.php?sel=online&amp;a=<?php echo $artId ?>&amp;page=1"><?php echo $nbComsValidated ?></a></li>
						</ul>
					</li>
					<li><a href="comment_new.php?a=<?php echo $artId ?>" title="<?php echo L_ARTICLE_NEW_COMMENT_TITLE ?>"><?php echo L_ARTICLE_NEW_COMMENT ?></a></li>
				</ul>
				<?php endif; ?>

			</fieldset>

		</div>

	</div>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminArticleFoot'));
?>

<?php $mainContent = ob_get_clean(); ?>

<?php
/**
 * Medias administration controller
 * @author  Stephane F, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxToken;
use Pluxml\PlxUtils;

// Header
include __DIR__ .'/../tags/top.php';
?>

<div class="adminheader">
	<h2 class="h3-like"><?= L_MEDIAS_TITLE ?></h2>
</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasTop')) # Hook Plugins ?>

<div class="admin mtm grid-6">
	<div class="col-1 mtl">
		<?= L_MEDIAS_FOLDER ?>&nbsp;:&nbsp;<?php $plxMedias->contentFolder() ?>
		<input type="submit" name="btn_changefolder" value="<?= L_OK ?>" /><span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
	</div>
	<div class="panel col-5">
		<form action="medias.php" method="post" id="form_medias">

			<!-- New Folder Dialog -->
			<div id="dlgNewFolder" class="dialog">
				<div class="dialog-content">
					<span class="dialog-close">&times;</span>
					<?= L_MEDIAS_NEW_FOLDER ?>&nbsp;:&nbsp;
					<input id="id_newfolder" type="text" name="newfolder" value="" maxlength="50" size="15" />
					<input type="submit" name="btn_newfolder" value="<?= L_MEDIAS_CREATE_FOLDER ?>" />
				</div>
			</div>

			<!-- Rename File Dialog -->
			<div id="dlgRenameFile" class="dialog">
				<div class="dialog-content">
					<span class="dialog-close">&times;</span>
					<?= L_MEDIAS_NEW_NAME ?>&nbsp;:&nbsp;
					<input id="id_newname" type="text" name="newname" value="" maxlength="50" size="15" />
					<input id="id_oldname" type="hidden" name="oldname" />
					<input type="submit" name="btn_renamefile" value="<?= L_MEDIAS_RENAME ?>" />
				</div>
			</div>
			
			<ul>
				<li><a href="medias.php?mode=list">list</a></li>
				<li><a href="medias.php?mode=grid">grid</a></li>
			</ul>

			<div class="inline-form" id="files_manager">

				<div class="inline-form action-bar">
					<p>
						<?php
						echo L_MEDIAS_DIRECTORY.' : <a href="javascript:void(0)" onclick="document.forms[0].folder.value=\'.\';document.forms[0].submit();return true;" title="'.L_PLXMEDIAS_ROOT.'">('.L_PLXMEDIAS_ROOT.')</a> / ';
						if($curFolders) {
							$path='';
							foreach($curFolders as $id => $folder) {
								if(!empty($folder) AND $id>1) {
									$path .= $folder.'/';
									echo '<a href="javascript:void(0)" onclick="document.forms[0].folder.value=\''.$path.'\';document.forms[0].submit();return true;" title="'.$folder.'">'.$folder.'</a> / ';
								}
							}
						}
						?>
					</p>
					<?php PlxUtils::printSelect('selection', $selectionList, '', false, 'no-margin', 'id_selection') ?>
					<input type="submit" name="btn_ok" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idFile[]', '<?= L_CONFIRM_DELETE ?>')" />
					<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
					<input type="submit" onclick="toggle_divs();return false" value="<?= L_MEDIAS_ADD_FILE ?>" />
					<button onclick="dialogBox('dlgNewFolder');return false;" id="btnNewFolder"><?= L_MEDIAS_NEW_FOLDER ?></button>
					<?php if(!empty($_SESSION['folder'])) { ?>
					<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span><input type="submit" name="btn_delete" class="red" value="<?= L_DELETE_FOLDER ?>" onclick="return confirm('<?php printf(L_MEDIAS_DELETE_FOLDER_CONFIRM, $curFolder) ?>')" />
					<?php } ?>
					<input type="hidden" name="sort" value="" />
					<?= plxToken::getTokenPostMethod() ?>
				</div>

				<div style="float:right">
					<input type="text" id="medias-search" onkeyup="plugFilter()" placeholder="<?= L_SEARCH ?>..." title="<?= L_SEARCH ?>" />
				</div>

				<?php if ($mode == 'list'): include __DIR__ .'/../tags/mediasListTag.php'; else: include __DIR__ .'/../tags/mediasGridTag.php'; endif;?>

			</div>
		</form>

		<form action="medias.php" method="post" id="form_uploader" class="form_uploader" enctype="multipart/form-data">

			<div id="files_uploader" style="display:none">

				<div class="inline-form action-bar">
					<h2 class="h4"><?= L_MEDIAS_TITLE ?></h2>
					<p>
						<?php
						echo L_MEDIAS_DIRECTORY.' : ('.L_PLXMEDIAS_ROOT.') / ';
						if($curFolders) {
							$path='';
							foreach($curFolders as $id => $folder) {
								if(!empty($folder) AND $id>1) {
									$path .= $folder.'/';
									echo $folder.' / ';
								}
							}
						}
						?>
					</p>
					<input type="submit" name="btn_upload" id="btn_upload" value="<?= L_MEDIAS_SUBMIT_FILE ?>" />
					<?= plxToken::getTokenPostMethod() ?>
				</div>

				<p><a class="back" href="javascript:void(0)" onclick="toggle_divs();return false"><?= L_MEDIAS_BACK ?></a></p>

				<p>
					<?= L_MEDIAS_MAX_UPLOAD_NBFILE ?> : <?= ini_get('max_file_uploads') ?>
		 		</p>
				<p>
					<?= L_MEDIAS_MAX_UPLOAD_FILE ?> : <?= $plxMedias->maxUpload['display'] ?>
					<?php if($plxMedias->maxPost['value'] > 0) echo " / ".L_MEDIAS_MAX_POST_SIZE." : ".$plxMedias->maxPost['display']; ?>
				</p>

				<div>
					<input id="selector_0" type="file" multiple="multiple" name="selector_0[]" />
					<div class="files_list" id="files_list" style="margin: 1rem 0 1rem 0;"></div>
				</div>

				<div class="grid">
					<div class="col sma-12 med-4">
						<ul class="unstyled-list">
							<li><?= L_MEDIAS_RESIZE ?>&nbsp;:&nbsp;</li>
							<li><input type="radio" checked="checked" name="resize" value="" />&nbsp;<?= L_MEDIAS_RESIZE_NO ?></li>
							<?php
								foreach($img_redim as $redim) {
									echo '<li><input type="radio" name="resize" value="'.$redim.'" />&nbsp;'.$redim.'</li>';
								}
							?>
							<li>
								<input type="radio" name="resize" value="<?= intval($plxAdmin->aConf['images_l' ]).'x'.intval($plxAdmin->aConf['images_h' ]) ?>" />&nbsp;<?= intval($plxAdmin->aConf['images_l' ]).'x'.intval($plxAdmin->aConf['images_h' ]) ?>
								&nbsp;&nbsp;(<a href="parametres_affichage.php"><?= L_MEDIAS_MODIFY ?>)</a>
							</li>
							<li>
								<input type="radio" name="resize" value="user" />&nbsp;
								<input type="text" size="2" maxlength="4" name="user_w" />&nbsp;x&nbsp;
								<input type="text" size="2" maxlength="4" name="user_h" />
							</li>
						</ul>
					</div>
					<div class="col sma-12 med-8">
						<ul class="unstyled-list">
							<li><?= L_MEDIAS_THUMBS ?>&nbsp;:&nbsp;</li>
							<li>
								<?php $sel = (!$plxAdmin->aConf['thumbs'] ? ' checked="checked"' : '') ?>
								<input<?= $sel ?> type="radio" name="thumb" value="" />&nbsp;<?= L_MEDIAS_THUMBS_NONE ?>
							</li>
							<?php
								foreach($img_thumb as $thumb) {
									echo '<li><input type="radio" name="thumb" value="'.$thumb.'" />&nbsp;'.$thumb.'</li>';
								}
							?>
							<li>
								<?php $sel = ($plxAdmin->aConf['thumbs'] ? ' checked="checked"' : '') ?>
								<input<?= $sel ?> type="radio" name="thumb" value="<?= intval($plxAdmin->aConf['miniatures_l' ]).'x'.intval($plxAdmin->aConf['miniatures_h' ]) ?>" />&nbsp;<?= intval($plxAdmin->aConf['miniatures_l' ]).'x'.intval($plxAdmin->aConf['miniatures_h' ]) ?>
								&nbsp;&nbsp;(<a href="parametres_affichage.php"><?= L_MEDIAS_MODIFY ?>)</a>
							</li>
							<li>
								<input type="radio" name="thumb" value="user" />&nbsp;
								<input type="text" size="2" maxlength="4" name="thumb_w" />&nbsp;x&nbsp;
								<input type="text" size="2" maxlength="4" name="thumb_h" />
							</li>
						</ul>
					</div>
				</div>
				<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasUpload')) # Hook Plugins ?>
			</div>

		</form>
	</div>
</div>

<div class="modal">
	<input id="modal" type="checkbox" name="modal" tabindex="1">
	<div id="modal__overlay" class="modal__overlay">
		<div id="modal__box" class="modal__box"></div>
	</div>
</div>

<script type="text/javascript" src="<?= PLX_CORE ?>lib/medias.js"></script>

<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasFoot')); ?>

<?php
// Footer
include __DIR__ .'/../tags/foot.php';
?>
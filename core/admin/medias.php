<?php

/**
 * Gestion des médias
 *
 * @package PLX
 * @author  Stephane F, Pedro "P3ter" CADETE
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Sécurisation du chemin du dossier
if(isset($_POST['folder']) AND $_POST['folder']!='.' AND !plxUtils::checkSource($_POST['folder'])) {
	$_POST['folder']='.';
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasPrepend'));

# Recherche du type de medias à afficher via la session
if(empty($_SESSION['medias'])) {
	$_SESSION['medias'] = $plxAdmin->aConf['medias'];
	$_SESSION['folder'] = '';
}
elseif(!empty($_POST['folder'])) {
	$_SESSION['currentfolder']= (isset($_SESSION['folder'])?$_SESSION['folder']:'');
	$_SESSION['folder'] = ($_POST['folder']=='.'?'':$_POST['folder']);
}

# Nouvel objet de type plxMedias
$plxMediasRoot = PLX_ROOT.$_SESSION['medias'];
$uf = $plxAdmin->aConf['userfolders'];
if(!empty($uf)) {
	$profil = ($uf == 1) ? PROFIL_WRITER : $uf - 1; // Rétro-compatibilité pour PluXml version < 5.9.0
	if($_SESSION['profil'] >= $profil) {
		# Gestion du dossier des medias pour certains utilisateurs
		$plxMediasRoot .= $_SESSION['user'].'/';
	}
}
$plxMedias = new plxMedias($plxMediasRoot, $_SESSION['folder'], $plxAdmin->aConf['default_lang']);

#----

$myHeader = 'Location: ' . $_SERVER['PHP_SELF'];

if(!empty($_POST['btn_newfolder']) AND !empty($_POST['newfolder'])) {
	if($plxMedias->newDir($_POST['newfolder'])) {
		$_SESSION['folder'] = $_SESSION['folder'].$_POST['newfolder'].'/';
		header($myHeader);
		exit;
	}
}
elseif(!empty($_POST['btn_renamefile']) AND !empty($_POST['newname'])) {
	if($plxMedias->renameFile($_POST['oldname'], $_POST['newname'])) {
		header($myHeader);
		exit;
	}
}
elseif(!empty($_POST['folder']) AND $_POST['folder']!='.' AND !empty($_POST['btn_delete'])) {
	if($plxMedias->deleteDir($_POST['folder'])) {
		$_SESSION['folder'] = '';
		header($myHeader);
		exit;
	}
}
elseif(!empty($_POST['btn_upload'])) {
	if($plxMedias->uploadFiles($_FILES, $_POST)) {
		header($myHeader);
		exit;
	}
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='delete')) AND isset($_POST['idFile'])) {
	if($plxMedias->deleteFiles($_POST['idFile'])) {
		header($myHeader);
		exit;
	}
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='move')) AND isset($_POST['idFile'])) {
	if($plxMedias->moveFiles($_POST['idFile'], $_SESSION['currentfolder'], $_POST['folder'])) {
		header($myHeader);
		exit;
	}
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='thumbs')) AND isset($_POST['idFile'])) {
	if($plxMedias->makeThumbs($_POST['idFile'], $plxAdmin->aConf['miniatures_l'], $plxAdmin->aConf['miniatures_h'])) {
		header($myHeader);
		exit;
	}
}

# Tri de l'affichage des fichiers
if(isset($_POST['sort']) AND !empty($_POST['sort'])) {
	$sort = $_POST['sort'];
} else {
	$sort = isset($_SESSION['sort_medias']) ? $_SESSION['sort_medias'] : 'title_asc';
}

$sort_title = 'title_desc';
$sort_date = 'date_desc';
switch ($sort) {
	case 'title_asc':
		$sort_title = 'title_desc';
		usort($plxMedias->aFiles, function($b, $a){return strcmp($a["name"], $b["name"]);});
		break;
	case 'title_desc':
		$sort_title = 'title_asc';
		usort($plxMedias->aFiles, function($a, $b){return strcmp($a["name"], $b["name"]);});
		break;
	case 'date_asc':
		$sort_date = 'date_desc';
		usort($plxMedias->aFiles, function($b, $a){return strcmp($a["date"], $b["date"]);});
		break;
	case 'date_desc':
		$sort_date = 'date_asc';
		usort($plxMedias->aFiles, function($b, $a){return strcmp($a["date"], $b["date"]);});
		break;
}
$_SESSION['sort_medias']=$sort;

# Contenu des 2 listes déroulantes
$selectionList = array(''=>L_FOR_SELECTION,'move'=>L_PLXMEDIAS_MOVE_FOLDER,'thumbs'=>L_MEDIAS_RECREATE_THUMB,'-'=>'-----','delete' =>L_DELETE_FILE);

# On inclut le header
include 'top.php';

$curFolder = '/'.plxUtils::strCheck(basename($_SESSION['medias']).'/'.$_SESSION['folder']);
$curFolders = explode('/', $curFolder);

?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasTop')) # Hook Plugins ?>

<form method="post">
	<!-- Rename File Dialog -->
	<div id="dlgRenameFile" class="dialog">
		<div class="dialog-content">
			<?= L_MEDIAS_NEW_NAME ?>&nbsp;:&nbsp;
			<input id="id_newname" type="text" name="newname" value="" maxlength="50" size="15" />
			<input id="id_oldname" type="hidden" name="oldname" />
			<?= plxToken::getTokenPostMethod() ?>
			<input type="submit" name="btn_renamefile" value="<?= L_MEDIAS_RENAME ?>" />
			<span class="dialog-close">&times;</span>
		</div>
	</div>
</form>

<form method="post" id="form_medias">

	<!-- New Folder Dialog -->
	<div id="dlgNewFolder" class="dialog">
		<div class="dialog-content">
			<span class="dialog-close">&times;</span>
			<?= L_MEDIAS_NEW_FOLDER ?>&nbsp;:&nbsp;
			<input id="id_newfolder" type="text" name="newfolder" value="" maxlength="50" size="15" />
			<input type="submit" name="btn_newfolder" value="<?= L_MEDIAS_CREATE_FOLDER ?>" />
		</div>
	</div>

	<div class="inline-form" id="files_manager">

		<div class="inline-form action-bar">
			<h2><?= L_MEDIAS_TITLE ?></h2>
			<p id="medias-breadcrumb">
				<?= L_MEDIAS_DIRECTORY ?> : <a href="javascript:void(0)" data-folder=".">(<?= L_PLXMEDIAS_ROOT ?>)</a> /
<?php
if($curFolders) {
	$path='';
	foreach($curFolders as $id => $folder) {
		if(!empty($folder) AND $id>1) {
			$path .= $folder . '/';
?>
<a href="javascript:void(0)" data-folder="<?= $path ?>"><?= $folder ?></a> /
<?php
		}
	}
}
?>
			</p>
			<?php plxUtils::printSelect('selection', $selectionList, '', false, 'no-margin', 'id_selection') ?>
			<input type="submit" name="btn_ok" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idFile[]', '<?= L_CONFIRM_DELETE ?>')" />
			<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
			<button onclick="toggle_divs(); return false"><?= L_MEDIAS_ADD_FILE ?></button>
			<button onclick="dialogBox('dlgNewFolder'); return false;" id="btnNewFolder"><?= L_MEDIAS_NEW_FOLDER ?></button>
<?php if(!empty($_SESSION['folder'])) { ?>
			<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
			<input type="submit" name="btn_delete" class="red" value="<?= L_DELETE_FOLDER ?>" onclick="return confirm('<?php printf(L_MEDIAS_DELETE_FOLDER_CONFIRM, $curFolder) ?>')" />
<?php } ?>
			<input type="hidden" name="sort" value="" />
			<?= plxToken::getTokenPostMethod() ?>
		</div>

		<div style="float:left">
			<?= L_MEDIAS_FOLDER ?>&nbsp;:&nbsp;<?php $plxMedias->contentFolder() ?>
			<input type="submit" name="btn_changefolder" value="<?= L_OK ?>" /><span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		</div>

		<div style="float:right">
			<input type="text" id="medias-search" onkeyup="plugFilter()" placeholder="<?= L_SEARCH ?>..." title="<?= L_SEARCH ?>" />
		</div>
<?php

/* ============== listing of medias ============== */

?>
		<div style="clear:both" class="scrollable-table">
			<table id="medias-table" class="full-width sort">
				<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idFile[]')" /></th>
					<th>&nbsp;</th>
					<th class="sort"><?= L_MEDIAS_FILENAME ?></th>
					<th class="sort"><?= L_MEDIAS_EXTENSION ?></th>
					<th class="sort integer"><?= L_MEDIAS_FILESIZE ?></th>
					<th class="sort integer"><?= L_MEDIAS_DIMENSIONS ?></th>
					<th class="sort integer active"><?= L_MEDIAS_DATE ?></th>
				</tr>
				</thead>
				<tbody id="medias-table-tbody">
<?php
# Si on a des fichiers
if($plxMedias->aFiles) {
	foreach($plxMedias->aFiles as $v) { # Pour chaque fichier
		$isImage = in_array(strtolower($v['extension']), plxMedias::IMG_SUPPORTED);
		$title = pathinfo($v['name'], PATHINFO_FILENAME);
?>
					<tr>
						<td><input type="checkbox" name="idFile[]" value="<? $v['name'] ?>" /></td>
						<td class="icon">
<?php

		if($v['extension'] == '.svg') {
			# Mimics getimagesize()
			$dimThumb = array(
				plxUtils::THUMB_WIDTH,
				plxUtils::THUMB_HEIGHT,
				false,
				'height="' . plxUtils::THUMB_HEIGHT . '" width="' . plxUtils::THUMB_WIDTH . '"',
				'image/svg+xml',
			);
		} else {
			$dimThumb = getimagesize($v['.thumb']);
		}
		ob_start();
?>
							<img alt="<?= $title ?>" src="<?= $v['.thumb'] ?>" class="thumb" <?= $dimThumb[3] ?> />
<?php
		$thumbImg = ob_get_clean();
		if($isImage AND is_file($v['path'])) {
?>
							<a class="overlay" title="<?= $title ?>" href="<?= $v['path'] ?>"><?= $thumbImg ?></a>
<?php
		} else {
			echo $thumbImg;
		}
		unset($thumbImg);
?>
						</td>
						<td data-sort="<?= $title . $v['extension'] ?>">
							<a class="imglink" target="_blank" title="<?= $title ?>" href="<?= $v['path'] ?>"><?= $title . $v['extension'] ?></a>
							<div data-copy="<?= str_replace(PLX_ROOT, '', $v['path']) ?>" title="<?= L_MEDIAS_LINK_COPYCLP ?>" class="ico">&#128203;
								<div><?= L_MEDIAS_LINK_COPYCLP_DONE ?></div>
							</div>
							<div data-rename="<?= $v['path'] ?>" title="<?= L_RENAME_FILE ?>" class="ico">&#9998;</div>
							<br />
<?php
		$href = plxUtils::thumbName($v['path']);
		$hasThumb = ($isImage and $href != $v['path'] and file_exists($href));
		if($hasThumb) {
?>
							<?= L_MEDIAS_THUMB ?> : <a target="_blank" title="<?= $title ?>" href="<?= $href ?>"><?= plxUtils::strCheck(basename($href)) ?></a>
							<div data-copy="<?= str_replace(PLX_ROOT, '', $href) ?>" title="<?= L_MEDIAS_LINK_COPYCLP ?>" class="ico">&#128203;
								<div><?= L_MEDIAS_LINK_COPYCLP_DONE ?></div>
							</div>
<?php
		}
?>
						</td>
						<td data-sort="<?= strtoupper($v['extension']) ?>"><?= strtoupper($v['extension']) ?></td>
						<td data-sort="<?= $v['filesize'] ?>">
							<?= plxUtils::formatFilesize($v['filesize']) ?>
<?php
		if($hasThumb and isset($v['thumb']['filesize'])) {
?>
							<br />
							<?= plxUtils::formatFilesize($v['thumb']['filesize']) ?>
<?php
		}
?>
						</td>
<?php
		if($isImage AND !empty($v['infos'])) {
			$dimensions = $v['infos'][0].' x '.$v['infos'][1];
			if(!empty($v['thumb']['infos'])) {
				$dimensions .= '<br />' . $v['thumb']['infos'][0] . ' x ' . $v['thumb']['infos'][1];
			}
?>
						<td data-sort="<?= $v['infos'] ? $v['infos'][0] * $v['infos'][1] : 0 ?>"><?= $dimensions ?></td>
<?php
		} else {
?>
						<td data-sort="">&nbsp;</td>
<?php
		}
?>
						<td data-sort="<?= $v['date'] ?>"><?= plxDate::formatDate(plxDate::timestamp2Date($v['date'])) ?></td>
					</tr>
<?php
					}
				} else  {
?>
					<tr>
						<td colspan="7" class="text-center"><?= L_MEDIAS_NO_FILE ?></td>
					</tr>
<?php
				}
?>
				</tbody>
			</table>
		</div>
	</div>
</form>
<?php

/* =============== uploading ================> */

?>
<form method="post" id="form_uploader" class="form_uploader" enctype="multipart/form-data">

	<div id="files_uploader" style="display:none">

		<div class="inline-form action-bar">
			<h2 class="h4"><?= L_MEDIAS_TITLE ?></h2>
			<p>
				<?= L_MEDIAS_DIRECTORY ?> : (<?= L_PLXMEDIAS_ROOT ?>) /
<?php
if($curFolders) {
	$path='';
	foreach($curFolders as $id => $folder) {
		if(!empty($folder) AND $id > 1) {
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
		<p><?= L_MEDIAS_MAX_UPLOAD_NBFILE ?> : <?= ini_get('max_file_uploads') ?></p>
		<p>
			<?= L_MEDIAS_MAX_UPLOAD_FILE ?> : <?= $plxMedias->maxUpload['display'] ?>
			<?php if($plxMedias->maxPost['value'] > 0) echo " / ".L_MEDIAS_MAX_POST_SIZE." : ".$plxMedias->maxPost['display']; ?>
		</p>

		<div>
			<input id="selector_0" type="file" multiple="multiple" name="selector_0[]" accept="image/*,audio/*,video/*,.pdf,.zip" />
			<div class="files_list" id="files_list" style="margin: 1rem 0 1rem 0;"></div>
		</div>

		<div class="grid">
			<div class="col sma-12 med-4">
				<ul class="unstyled-list">
					<li><?= L_MEDIAS_RESIZE ?>&nbsp;:&nbsp;</li>
					<li><input type="radio" checked="checked" name="resize" value="" />&nbsp;<?= L_MEDIAS_RESIZE_NO ?></li>
<?php
						foreach($img_redim as $redim) {
?>
					<li><input type="radio" name="resize" value="<?= $redim ?>" />&nbsp;<?= $redim ?></li>
<?php
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
			<div class="col sml-12 med-8">
				<ul class="unstyled-list">
					<li><?= L_MEDIAS_THUMBS ?>&nbsp;:&nbsp;</li>
					<li>
						<?php $sel = (!$plxAdmin->aConf['thumbs'] ? ' checked="checked"' : '') ?>
						<input<?= $sel ?> type="radio" name="thumb" value="" />&nbsp;<?= L_MEDIAS_THUMBS_NONE ?>
					</li>
<?php
						foreach($img_thumb as $thumb) {
?>
					<li><input type="radio" name="thumb" value="<?= $thumb ?>" />&nbsp;<?= $thumb ?></li>
<?php
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
<?php

/* ============= zoombox ============= */

?>
<div class="modal">
	<input id="modal" type="checkbox" name="modal" tabindex="1">
	<div id="modal__overlay" class="modal__overlay">
		<div id="modal__box" class="modal__box">
			<div id="loader">
				<span class="loader"></span>
			</div>
			<label for="modal">&#10006;</label>
			<img id="zoombox-img" />
		</div>
	</div>
</div>

<input id="clipboard" type="text" value="" style="display: none;" />

<script type="text/javascript" src="js/medias.js"></script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasFoot'));

# On inclut le footer
include 'foot.php';

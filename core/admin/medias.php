<?php

/**
 * Gestion des médias
 *
 * @package PLX
 * @author  Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

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
if($plxAdmin->aConf['userfolders'] AND $_SESSION['profil']==PROFIL_WRITER)
	$plxMediasRoot .= $_SESSION['user'].'/';
$plxMedias = new plxMedias($plxMediasRoot, $_SESSION['folder']);

#----

if(!empty($_POST['btn_newfolder']) AND !empty($_POST['newfolder'])) {
	$newdir = plxUtils::title2filename(trim($_POST['newfolder']));
	if($plxMedias->newDir($newdir)) {
		$_SESSION['folder'] = $_SESSION['folder'].$newdir.'/';
	}
	header('Location: medias.php');
	exit;
}
if(!empty($_POST['btn_renamefile']) AND !empty($_POST['newname'])) {
	$plxMedias->renameFile($_POST['oldname'], $_POST['newname']);
	header('Location: medias.php');
	exit;
}
elseif(!empty($_POST['folder']) AND $_POST['folder']!='.' AND !empty($_POST['btn_delete'])) {
	if($plxMedias->deleteDir($_POST['folder'])) {
		$_SESSION['folder'] = '';
	}
	header('Location: medias.php');
	exit;
}
elseif(!empty($_POST['btn_upload'])) {
	$plxMedias->uploadFiles($_FILES, $_POST);
	header('Location: medias.php');
	exit;
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='delete')) AND isset($_POST['idFile'])) {
	$plxMedias->deleteFiles($_POST['idFile']);
	header('Location: medias.php');
	exit;
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='move')) AND isset($_POST['idFile'])) {
	$plxMedias->moveFiles($_POST['idFile'], $_SESSION['currentfolder'], $_POST['folder']);
	header('Location: medias.php');
	exit;
}
elseif(isset($_POST['selection']) AND ((!empty($_POST['btn_ok']) AND $_POST['selection']=='thumbs')) AND isset($_POST['idFile'])) {
	$plxMedias->makeThumbs($_POST['idFile'], $plxAdmin->aConf['miniatures_l'], $plxAdmin->aConf['miniatures_h']);
	header('Location: medias.php');
	exit;
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
		usort($plxMedias->aFiles, create_function('$b, $a', 'return strcmp($a["name"], $b["name"]);'));
		break;
	case 'title_desc':
		$sort_title = 'title_asc';
		usort($plxMedias->aFiles, create_function('$a, $b', 'return strcmp($a["name"], $b["name"]);'));
		break;
	case 'date_asc':
		$sort_date = 'date_desc';
		usort($plxMedias->aFiles, create_function('$b, $a', 'return strcmp($a["date"], $b["date"]);'));
		break;
	case 'date_desc':
		$sort_date = 'date_asc';
		usort($plxMedias->aFiles, create_function('$a, $b', 'return strcmp($a["date"], $b["date"]);'));
		break;
}
$_SESSION['sort_medias']=$sort;

# Contenu des 2 listes déroulantes
$selectionList = array(''=>L_FOR_SELECTION,'move'=>L_PLXMEDIAS_MOVE_FOLDER,'thumbs'=>L_MEDIAS_RECREATE_THUMB,'-'=>'-----','delete' =>L_DELETE_FILE);

# On inclut le header
include(dirname(__FILE__).'/top.php');

$curFolder = '/'.plxUtils::strCheck(basename($_SESSION['medias']).'/'.$_SESSION['folder']);
$curFolders = explode('/', $curFolder);

?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasTop')) # Hook Plugins ?>

<form action="medias.php" method="post" id="form_medias">

	<!-- New Folder Dialog -->
	<div id="dlgNewFolder" class="dialog">
		<div class="dialog-content">
			<span class="dialog-close">&times;</span>
			<?php echo L_MEDIAS_NEW_FOLDER ?>&nbsp;:&nbsp;
			<input id="id_newfolder" type="text" name="newfolder" value="" maxlength="50" size="15" />
			<input type="submit" name="btn_newfolder" value="<?php echo L_MEDIAS_CREATE_FOLDER ?>" />
		</div>
	</div>

	<!-- Rename File Dialog -->
	<div id="dlgRenameFile" class="dialog">
		<div class="dialog-content">
			<span class="dialog-close">&times;</span>
			<?php echo L_MEDIAS_NEW_NAME ?>&nbsp;:&nbsp;
			<input id="id_newname" type="text" name="newname" value="" maxlength="50" size="15" />
			<input id="id_oldname" type="hidden" name="oldname" />
			<input type="submit" name="btn_renamefile" value="<?php echo L_MEDIAS_RENAME ?>" />
		</div>
	</div>

	<div class="inline-form" id="files_manager">

		<div class="inline-form action-bar">
			<h2><?php echo L_MEDIAS_TITLE ?></h2>
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
			<?php plxUtils::printSelect('selection', $selectionList, '', false, 'no-margin', 'id_selection') ?>
			<input type="submit" name="btn_ok" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idFile[]', '<?php echo L_CONFIRM_DELETE ?>')" />
			&nbsp;&nbsp;&nbsp;
			<input type="submit" onclick="toggle_divs();return false" value="<?php echo L_MEDIAS_ADD_FILE ?>" />
			<button onclick="dialogBox('dlgNewFolder');return false;" id="btnNewFolder"><?php echo L_MEDIAS_NEW_FOLDER ?></button>
			<?php if(!empty($_SESSION['folder'])) { ?>
			&nbsp;&nbsp;&nbsp;<input type="submit" name="btn_delete" class="red" value="<?php echo L_DELETE_FOLDER ?>" onclick="return confirm('<?php printf(L_MEDIAS_DELETE_FOLDER_CONFIRM, $curFolder) ?>')" />
			<?php } ?>
			<input type="hidden" name="sort" value="" />
			<?php echo plxToken::getTokenPostMethod() ?>
		</div>

		<div style="float:left">
			<?php echo L_MEDIAS_FOLDER ?>&nbsp;:&nbsp;
			<?php echo $plxMedias->contentFolder() ?>
			<input type="submit" name="btn_changefolder" value="<?php echo L_OK ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
		</div>

		<div style="float:right">
			<input type="text" id="medias-search" onkeyup="plugFilter()" placeholder="<?php echo L_SEARCH ?>..." title="<?php echo L_SEARCH ?>" />
		</div>

		<div style="clear:both" class="scrollable-table">
			<table id="medias-table" class="full-width">
				<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idFile[]')" /></th>
					<th>&nbsp;</th>
					<th><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[0].sort.value='<?php echo $sort_title ?>';document.forms[0].submit();return true;"><?php echo L_MEDIAS_FILENAME ?></a></th>
					<th><?php echo L_MEDIAS_EXTENSION ?></th>
					<th><?php echo L_MEDIAS_FILESIZE ?></th>
					<th><?php echo L_MEDIAS_DIMENSIONS ?></th>
					<th><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[0].sort.value='<?php echo $sort_date ?>';document.forms[0].submit();return true;"><?php echo L_MEDIAS_DATE ?></a></th>
				</tr>
				</thead>
				<tbody>
				<?php
				# Initialisation de l'ordre
				$num = 0;
				# Si on a des fichiers
				if($plxMedias->aFiles) {
					foreach($plxMedias->aFiles as $v) { # Pour chaque fichier
						$isImage = in_array(strtolower($v['extension']), array('.png', '.gif', '.jpg', '.jpeg'));
						$ordre = ++$num;
						echo '<tr>';
						echo '<td><input type="checkbox" name="idFile[]" value="'.$v['name'].'" /></td>';
						echo '<td class="icon">';
							if(is_file($v['path']) AND $isImage) {
								echo '<a onclick="overlay(\''.$v['path'].'\');return false;" title="'.plxUtils::strCheck($v['name']).'" href="'.$v['path'].'"><img alt="" src="'.$v['.thumb'].'" class="thumb" /></a>';
							}
						echo '</td>';
						echo '<td>';
							echo '<a class="imglink" onclick="'."this.target='_blank'".'" title="'.plxUtils::strCheck($v['name']).'" href="'.$v['path'].'">'.plxUtils::strCheck($v['name']).'</a>';
							echo '<div onclick="copy(this, \''.str_replace(PLX_ROOT, '', $v['path']).'\')" title="'.L_MEDIAS_LINK_COPYCLP.'" class="ico">&#8629;<div>'.L_MEDIAS_LINK_COPYCLP_DONE.'</div></div>';
							echo '<div id="btnRenameImg'.$num.'" onclick="ImageRename(\''.$v['path'].'\')" title="'.L_RENAME_FILE.'" class="ico">&perp;</div>';
							echo '<br />';
							$href = plxUtils::thumbName($v['path']);
							if($isImage AND is_file($href)) {
								echo L_MEDIAS_THUMB.' : '.'<a onclick="'."this.target='_blank'".'" title="'.L_MEDIAS_THUMB.' : '.plxUtils::strCheck(basename($href)).'" href="'.$href.'">'.plxUtils::strCheck(basename($href)).'</a>';
								echo '<div onclick="copy(this, \''.str_replace(PLX_ROOT, '', $href).'\')" title="'.L_MEDIAS_LINK_COPYCLP.'" class="ico">&#8629;<div>'.L_MEDIAS_LINK_COPYCLP_DONE.'</div></div>';
							}
						echo '</td>';
						echo '<td>'.strtoupper($v['extension']).'</td>';
						echo '<td>';
							echo plxUtils::formatFilesize($v['filesize']);
							if($isImage AND is_file($href)) {
								echo '<br />'.plxUtils::formatFilesize($v['thumb']['filesize']);
							}
						echo '</td>';
						$dimensions = '&nbsp;';
						if($isImage AND (isset($v['infos']) AND isset($v['infos'][0]) AND isset($v['infos'][1]))) {
							$dimensions = $v['infos'][0].' x '.$v['infos'][1];
						}
						if($isImage AND is_file($href)) {
							$dimensions .= '<br />'.$v['thumb']['infos'][0].' x '.$v['thumb']['infos'][1];
						}
						echo '<td>'.$dimensions.'</td>';
						echo '<td>'.plxDate::formatDate(plxDate::timestamp2Date($v['date'])).'</td>';
						echo '</tr>';
					}
				}
				else echo '<tr><td colspan="7" class="center">'.L_MEDIAS_NO_FILE.'</td></tr>';
				?>
				</tbody>
			</table>
		</div>
	</div>
</form>

<form action="medias.php" method="post" id="form_uploader" class="form_uploader" enctype="multipart/form-data">

	<div id="files_uploader" style="display:none">

		<div class="inline-form action-bar">
			<h2 class="h4"><?php echo L_MEDIAS_TITLE ?></h2>
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
			<input type="submit" name="btn_upload" id="btn_upload" value="<?php echo L_MEDIAS_SUBMIT_FILE ?>" />
			<?php echo plxToken::getTokenPostMethod() ?>
		</div>

		<p><a class="back" href="javascript:void(0)" onclick="toggle_divs();return false"><?php echo L_MEDIAS_BACK ?></a></p>

		<p>
			<?php echo L_MEDIAS_MAX_UPLOAD_NBFILE ?> : <?php echo ini_get('max_file_uploads') ?>
 		</p>
		<p>
			<?php echo L_MEDIAS_MAX_UPLOAD_FILE ?> : <?php echo $plxMedias->maxUpload['display'] ?>
			<?php if($plxMedias->maxPost['value'] > 0) echo " / ".L_MEDIAS_MAX_POST_SIZE." : ".$plxMedias->maxPost['display']; ?>
		</p>

		<div>
			<input id="selector_0" type="file" multiple="multiple" name="selector_0[]" />
			<div class="files_list" id="files_list" style="margin: 1rem 0 1rem 0;"></div>
		</div>

		<div class="grid">
			<div class="col sma-12 med-4">
				<ul class="unstyled-list">
					<li><?php echo L_MEDIAS_RESIZE ?>&nbsp;:&nbsp;</li>
					<li><input type="radio" checked="checked" name="resize" value="" />&nbsp;<?php echo L_MEDIAS_RESIZE_NO ?></li>
					<?php
						foreach($img_redim as $redim) {
							echo '<li><input type="radio" name="resize" value="'.$redim.'" />&nbsp;'.$redim.'</li>';
						}
					?>
					<li>
						<input type="radio" name="resize" value="<?php echo intval($plxAdmin->aConf['images_l' ]).'x'.intval($plxAdmin->aConf['images_h' ]) ?>" />&nbsp;<?php echo intval($plxAdmin->aConf['images_l' ]).'x'.intval($plxAdmin->aConf['images_h' ]) ?>
						&nbsp;&nbsp;(<a href="parametres_affichage.php"><?php echo L_MEDIAS_MODIFY ?>)</a>
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
					<li><?php echo L_MEDIAS_THUMBS ?>&nbsp;:&nbsp;</li>
					<li>
						<?php $sel = (!$plxAdmin->aConf['thumbs'] ? ' checked="checked"' : '') ?>
						<input<?php echo $sel ?> type="radio" name="thumb" value="" />&nbsp;<?php echo L_MEDIAS_THUMBS_NONE ?>
					</li>
					<?php
						foreach($img_thumb as $thumb) {
							echo '<li><input type="radio" name="thumb" value="'.$thumb.'" />&nbsp;'.$thumb.'</li>';
						}
					?>
					<li>
						<?php $sel = ($plxAdmin->aConf['thumbs'] ? ' checked="checked"' : '') ?>
						<input<?php echo $sel ?> type="radio" name="thumb" value="<?php echo intval($plxAdmin->aConf['miniatures_l' ]).'x'.intval($plxAdmin->aConf['miniatures_h' ]) ?>" />&nbsp;<?php echo intval($plxAdmin->aConf['miniatures_l' ]).'x'.intval($plxAdmin->aConf['miniatures_h' ]) ?>
						&nbsp;&nbsp;(<a href="parametres_affichage.php"><?php echo L_MEDIAS_MODIFY ?>)</a>
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

<div class="modal">
	<input id="modal" type="checkbox" name="modal" tabindex="1">
	<div class="modal__overlay">
		<label for="modal">&#10006;</label>
		<div id="modal__box" class="modal__box"></div>
	</div>
</div>

<script>
function toggle_divs(){
	var uploader = document.getElementById('files_uploader');
	var manager = document.getElementById('files_manager');
	if(uploader.style.display == 'none') {
		uploader.style.display = 'block';
		manager.style.display = 'none';
	} else {
		uploader.style.display = 'none';
		manager.style.display = 'block';
	}
}
function overlay(content) {
	e = document.getElementById("modal__box");
	e.innerHTML = '<img src="'+content+'" alt="" />';
	e = document.getElementById("modal");
	e.click();
}
function copy(elt, data) {
	try {
		var div = elt.querySelector("div");
		var aux = document.createElement("input");
		aux.setAttribute("value", data);
		document.body.appendChild(aux);
		aux.select();
		document.execCommand("copy");
		document.body.removeChild(aux);
		div.setAttribute("style", "display:inline-block");
		t = setTimeout(function(){
			div.setAttribute("style", "display:none");
			clearTimeout(t);
		}, 1000);
	} catch (err) {
		alert('<?php echo L_MEDIAS_LINK_COPYCLP_ERR ?>');
	}
}
function plugFilter() {
	var input, filter, table, tr, td, i;
	filter = document.getElementById("medias-search").value;
	table = document.getElementById("medias-table");
	tr = table.getElementsByTagName("tr");
	for (i = 0; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td")[2];
		if (td != undefined) {
			if (td.innerHTML.toLowerCase().indexOf(filter.toLowerCase()) > -1) {
				tr[i].style.display = "";
			} else {
				tr[i].style.display = "none";
			}
		}
	}
	if (typeof(Storage) !== "undefined" && filter !== "undefined") {
		localStorage.setItem("medias_search", filter);
	}
}
if (typeof(Storage) !== "undefined" && localStorage.getItem("medias_search") !== "undefined") {
	input = document.getElementById("medias-search");
	input.value = localStorage.getItem("medias_search");
	plugFilter();
}

function ImageRename(oldimg) {
	document.getElementById('id_oldname').value = oldimg;
	dialogBox("dlgRenameFile");
}
</script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
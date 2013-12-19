<?php

/**
 * Gestion des images et documents
 *
 * @package PLX
 * @author  Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');


# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Sécurisation du chemin du dossier de destination et de visualisation
if(isset($_POST['folder']) AND $_POST['folder']!='.' AND $_POST['folder']!='..' AND !plxUtils::checkSource($_POST['folder'])) {
	$_POST['folder']='.';
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasPrepend'));

# Recherche du type de medias à afficher via la session
if(empty($_SESSION['medias']) OR (isset($_POST['medias']) AND $_POST['medias']=='images' AND $_SESSION['medias']!=$plxAdmin->aConf['images'])) {
	$_SESSION['medias']=$plxAdmin->aConf['images'];
	$_SESSION['folder']='';
	$_SESSION['currentfolder']='';
}
elseif (isset($_POST['medias']) AND $_POST['medias']=='documents' AND $_SESSION['medias']!=$plxAdmin->aConf['documents']) {
	$_SESSION['medias']=$plxAdmin->aConf['documents'];
	$_SESSION['folder']='';
	$_SESSION['currentfolder']='';
}
elseif(isset($_POST['folder'])) {
	$_SESSION['currentfolder']=$_SESSION['folder'];
	if($_POST['folder']=='..')
		$_POST['folder']=preg_replace('#([^\/]*\/)$#', '', $_SESSION['folder']);
	if($_POST['folder']=='' OR $_POST['folder']=='.')
		$_SESSION['folder']=$_POST['folder']='';
	else
		$_SESSION['folder']=rtrim($_POST['folder'], '/\\').DIRECTORY_SEPARATOR;
}

# Nouvel objet de type plxMedias
if($plxAdmin->aConf['userfolders'] AND $_SESSION['profil']==PROFIL_WRITER)
	$plxMedias = new plxMedias(PLX_ROOT.$_SESSION['medias'].$_SESSION['user'].'/',$_SESSION['folder']);
else
	$plxMedias = new plxMedias(PLX_ROOT.$_SESSION['medias'],$_SESSION['folder']);


if(!empty($_POST['btn_newfolder']) AND !empty($_POST['newfolder'])) {
	$newdir = plxUtils::title2filename(trim($_POST['newfolder']));
	if($plxMedias->newDir($newdir)) {
		$_SESSION['folder'] = $_SESSION['folder'].$newdir.DIRECTORY_SEPARATOR;
	}
	header('Location: medias.php');
	exit;
}
elseif(!empty($_POST['btn_upload'])) {
	$plxMedias->uploadFiles($_FILES, $_POST);
	header('Location: medias.php');
	exit;
}
elseif (!empty($_POST['btn_ok_top']) AND isset($_POST['selection'])) {
	if($_POST['selection'][0]=='delete') {
		if (isset($_POST['idFile']))
			$plxMedias->deleteFiles($_POST['idFile']);
		if (isset($_POST['idDir']))
			$plxMedias->deleteDirs($_POST['idDir']);
		header('Location: medias.php');
		exit;
	}
	elseif ($_POST['selection'][0]=='move') {
		if (isset($_POST['idFile']))
			$plxMedias->moveFiles($_POST['idFile'], $_SESSION['currentfolder'], $_POST['folder']);
		if (isset($_POST['idDir']))
			$plxMedias->moveDirs($_POST['idDir'], $_SESSION['currentfolder'], $_POST['folder']);
		header('Location: medias.php');
		exit;
	}
	elseif ($_POST['selection'][0]=='thumbs') {
		$plxMedias->makeThumbs($_POST['idFile'], $plxAdmin->aConf['miniatures_l'], $plxAdmin->aConf['miniatures_h']);
		header('Location: medias.php');
		exit;
	}
}
elseif (!empty($_POST['btn_ok_bottom']) AND isset($_POST['selection'])) {
	if($_POST['selection'][1]=='delete') {
		if (isset($_POST['idFile']))
			$plxMedias->deleteFiles($_POST['idFile']);
		if (isset($_POST['idDir']))
			$plxMedias->deleteDirs($_POST['idDir']);
		header('Location: medias.php');
		exit;
	}
	elseif ($_POST['selection'][1]=='move') {
		if (isset($_POST['idFile']))
			$plxMedias->moveFiles($_POST['idFile'], $_SESSION['currentfolder'], $_POST['folder']);
		if (isset($_POST['idDir']))
			$plxMedias->moveDirs($_POST['idDir'], $_SESSION['currentfolder'], $_POST['folder']);
		header('Location: medias.php');
		exit;
	}
	elseif ($_POST['selection'][1]=='thumbs') {
		$plxMedias->makeThumbs($_POST['idFile'], $plxAdmin->aConf['miniatures_l'], $plxAdmin->aConf['miniatures_h']);
		header('Location: medias.php');
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
$selectionList = array('' =>L_FOR_SELECTION, 'move'=>L_PLXMEDIAS_MOVE_FOLDER, 'thumbs'=>L_MEDIAS_RECREATE_THUMB, '-'=>'-----', 'delete' =>L_DELETE);

# On inclut le header
include(dirname(__FILE__).'/top.php');

?>
<script type="text/javascript" src="<?php echo PLX_CORE ?>lib/multifiles.js"></script>
<script type="text/javascript">
function toggle_divs(){
	var uploader = document.getElementById('files_uploader'),
	    manager = document.getElementById('files_manager');
	if(uploader.style.display == 'none') {
		uploader.style.display = 'block';
		manager.style.display = 'none';
	} else {
		uploader.style.display = 'none';
		manager.style.display = 'block';
	}
}
function action_change(){
	var pFolder, pAction, sActionOther, iSubmit;
	pFolder = document.getElementById('p_folder');
	if(this.id == 'id_selection_top') {
		pAction = document.getElementById('action_top');
		sActionOther = document.getElementById('action_bottom').childNodes[1];
		iSubmit = document.getElementById('btn_ok_top');
	} else {
		pAction = document.getElementById('action_bottom');
		sActionOther = document.getElementById('action_top').childNodes[1];
		iSubmit = document.getElementById('btn_ok_bottom');
	}
	if(this.options[this.selectedIndex].value == 'move') {
		pAction.insertBefore(pFolder, iSubmit);
		pFolder.style.display = 'inline';

		if(sActionOther.selectedIndex == this.selectedIndex) {
			sActionOther.selectedIndex = 0;
		}
	} else {
		pFolder.display = 'none';
	}
}
</script>

<h2><?php echo L_MEDIAS_TITLE ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasTop')) # Hook Plugins ?>

<!-- Form upload file-->

<div id="files_uploader" style="display:none">

	<p style="margin-bottom:15px"><a href="javascript:void(0)" onclick="toggle_divs();return false"><?php echo L_MEDIAS_BACK ?></a></p>
	<p style="margin-bottom:15px" class="path"><?php echo L_MEDIAS_DIRECTORY.' : /'.plxUtils::strCheck(basename($_SESSION['medias']).'/'.$_SESSION['folder']) ?></p>
	<p style="margin-bottom:15px"><?php echo L_MEDIAS_MAX_UPOLAD_FILE ?> : <?php echo $plxMedias->maxUpload['display'] ?></p>
	<form action="medias.php" method="post" id="form_uploader" class="form_uploader" enctype="multipart/form-data">
		<div class="manager">
			<input id="selector" type="file" name="selector" />
			<div class="files_list" id="files_list"></div>
			<?php if($_SESSION['medias']==$plxAdmin->aConf['images']) : ?>
			<div class="options1">
				<?php echo L_MEDIAS_RESIZE ?>&nbsp;:&nbsp;
				<ul>
					<li><input type="radio" name="resize" value="" />&nbsp;<?php echo L_MEDIAS_RESIZE_NO ?></li>
					<?php
						foreach($img_redim as $redim) {
							echo '<li><input type="radio" name="resize" value="'.$redim.'" />&nbsp;'.$redim.'</li>';
						}
					?>
					<li>
						<input type="radio" checked="checked" name="resize" value="<?php echo intval($plxAdmin->aConf['images_l' ]).'x'.intval($plxAdmin->aConf['images_h' ]) ?>" />&nbsp;<?php echo intval($plxAdmin->aConf['images_l' ]).'x'.intval($plxAdmin->aConf['images_h' ]) ?>
						&nbsp;&nbsp;(<a href="parametres_affichage.php"><?php echo L_MEDIAS_MODIFY ?>)</a>
					</li>
					<li>
						<input type="radio" name="resize" value="user" />&nbsp;
						<input type="text" size="2" maxlength="4" name="user_w" />&nbsp;x&nbsp;
						<input type="text" size="2" maxlength="4" name="user_h" />
					</li>
				</ul>
			</div>
			<div class="options2" style="margin-bottom:15px">
				<?php echo L_MEDIAS_THUMBS ?>&nbsp;:&nbsp;
				<ul>
					<li>
						<?php $sel = (!$plxAdmin->aConf['thumbs'] ? 'checked="checked" ' : '') ?>
						<input <?php echo $sel ?>type="radio" name="thumb" value="" />&nbsp;<?php echo L_MEDIAS_THUMBS_NONE ?>
					</li>
					<?php
						foreach($img_thumb as $thumb) {
							echo '<li><input type="radio" name="thumb" value="'.$thumb.'" />&nbsp;'.$thumb.'</li>';
						}
					?>
					<li>
						<?php $sel = ($plxAdmin->aConf['thumbs'] ? 'checked="checked" ' : '') ?>
						<input <?php echo $sel ?>type="radio" name="thumb" value="<?php echo intval($plxAdmin->aConf['miniatures_l' ]).'x'.intval($plxAdmin->aConf['miniatures_h' ]) ?>" />&nbsp;<?php echo intval($plxAdmin->aConf['miniatures_l' ]).'x'.intval($plxAdmin->aConf['miniatures_h' ]) ?>
						&nbsp;&nbsp;(<a href="parametres_affichage.php"><?php echo L_MEDIAS_MODIFY ?>)</a>
					</li>
					<li>
						<input type="radio" name="thumb" value="user" />&nbsp;
						<input type="text" size="2" maxlength="4" name="thumb_w" />&nbsp;x&nbsp;
						<input type="text" size="2" maxlength="4" name="thumb_h" />
					</li>
				</ul>
			</div>
			<?php endif; ?>
			<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasUpload')) # Hook Plugins ?>
			<input class="button submit" type="submit" name="btn_upload" id="btn_upload" value="<?php echo L_MEDIAS_SUBMIT_FILE ?>" />
			<?php echo plxToken::getTokenPostMethod() ?>
		</div>
	</form>
	<script type="text/javascript">
		var multi_selector = new MultiSelector(document.getElementById('files_list'), -1, '<?php echo $plxAdmin->aConf['racine'] ?>');
		multi_selector.addElement(document.getElementById('selector'));
	</script>
	<div class="clearer"></div>
</div>

<!-- File manager-->

<div id="files_manager">

	<form action="medias.php" method="post" id="form_medias" class="form_medias">

		<p style="margin-bottom:15px" class="path"><?php echo L_MEDIAS_DIRECTORY ?> :
			<select name="medias" onchange="document.forms[1].submit();">
				<option value="images" <?php echo basename($_SESSION['medias'])=='images'?' selected="selected" ':'' ?>><?php echo L_MEDIAS_IMAGES ?></option>
				<option value="documents" <?php echo basename($_SESSION['medias'])=='documents'?' selected="selected" ':'' ?>><?php echo L_MEDIAS_DOCUMENTS ?></option>
			</select>
			 /
			<?php
			 foreach($plxMedias->currentFolder() as $path => $pathName){
				echo '<a title="'.$pathName.'" href="javascript:void(0)" onclick="document.forms[1].folder.value=\''.$path.'\';document.forms[1].submit();return true;">'.$pathName.'</a> / ';
			 }
			?>
		</p>
		<p class="create">
			<?php echo L_MEDIAS_NEW_FOLDER ?>&nbsp;:&nbsp;
			<input class="newfolder" id="id_newfolder" type="text" name="newfolder" value="" maxlength="50" size="10" />
			<input class="button new" type="submit" name="btn_newfolder" value="<?php echo L_MEDIAS_CREATE_FOLDER ?>" /><br /><br />
			<input class="button submit" type="submit" onclick="toggle_divs();return false" value="<?php echo L_MEDIAS_ADD_FILE ?>" />
		</p>

		<div class="files">

			<p style="margin-bottom:15px" id="action_top">
				<?php plxUtils::printSelect('selection[]', $selectionList, '', false, '', 'id_selection_top') ?>
				<span style="display:none" id="p_folder">
					&nbsp;
					<?php echo L_MEDIAS_IN_FOLDER ?>&nbsp;:&nbsp;
					<?php echo $plxMedias->contentFolder() ?>&nbsp;
				</span>
				<input class="button submit" type="submit" name="btn_ok_top" id="btn_ok_top" value="<?php echo L_OK ?>" onclick="return (confirmAction(this.form, 'id_selection_top', 'delete', 'idFile[]', '<?php echo L_CONFIRM_DELETE ?>')?true:confirmAction(this.form, 'id_selection_top', 'delete', 'idDir[]', '<?php echo L_CONFIRM_DELETE ?>'))" />
			</p>
			<table class="table">
			<thead>
			<tr>
				<th class="checkbox" colspan="2">
					<input type="checkbox" id="checkFile" onclick="checkAll(this.form, 'idFile[]')" />
					<label for="checkFile">
						<img src="<?php echo PLX_CORE; ?>admin/theme/images/file.png" alt="file" width="16px" height="16px" style="vertical-align:-5px;" />
					</label>
					<input type="checkbox" id="checkDir" onclick="checkAll(this.form, 'idDir[]')" />
					<label for="checkDir">
						<img src="<?php echo PLX_CORE; ?>admin/theme/images/folder.png" alt="folder" width="16px" height="16px" style="vertical-align:-5px;" />
					</label>
				</th>
				<th><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[1].sort.value='<?php echo $sort_title ?>';document.forms[1].submit();return true;"><?php echo L_MEDIAS_NAME ?></a></th>
				<th class="infos"><?php echo L_MEDIAS_TYPE ?></th>
				<th class="infos"><?php echo L_MEDIAS_FILESIZE ?></th>
				<th class="infos"><?php echo L_MEDIAS_DIMENSIONS ?></th>
				<th class="date"><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[1].sort.value='<?php echo $sort_date ?>';document.forms[1].submit();return true;"><?php echo L_MEDIAS_DATE ?></a></th>
			</tr>
			</thead>
			<tbody>
			<?php
			# Initialisation de l'ordre
			$num = 0;
			# Si ce n'est pas le dossier root
			if ($_SESSION['folder'] != '') {
				$num++;
				echo '<tr class="line-'.($num%2).'">';
				echo '<td class="checkbox center"></td>';
				echo '<td class="icon center"><a title="&crarr; .." href="javascript:void(0)" onclick="document.forms[1].folder.value=\'..\';document.forms[1].submit();return true;"><img src="'.PLX_CORE.'admin/theme/images/folder.png" alt="folder" /></a></td>';
				echo '<td><a title="&crarr; .."  href="javascript:void(0)" onclick="document.forms[1].folder.value=\'..\';document.forms[1].submit();return true;">&crarr;&nbsp;..&nbsp;</a></td>';
				echo '<td>'.L_MEDIAS_FOLDER.'</td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '</tr>';
			}
			# Si on a des répertoires
			if($plxMedias->aDirs) {
				$level = false;
				foreach($plxMedias->aDirs as $v) { # Pour chaque dossier

					if ($_SESSION['folder'] == '' && $v['level'] != 0)
						continue;
					if ($v['path'] == $_SESSION['folder']) {
						 $level = $v['level'] + 1;
						continue;
					}
					if ($_SESSION['folder'] != '' AND ($v['path'] == $_SESSION['folder'] OR $v['level'] != $level OR substr($v['path'], 0, strlen($_SESSION['folder'])) != $_SESSION['folder'])) {
						continue;
					}
					$level = $v['level'];
					$num++;
					echo '<tr class="line-'.($num%2).'">';
					echo '<td class="checkbox center"><input type="checkbox" name="idDir[]" value="'.$v['name'].'" /></td>';
					echo '<td class="icon center">';
					echo '<a title="'.plxUtils::strCheck($v['name']).'"  href="javascript:void(0)" onclick="document.forms[1].folder.value=\''.$v['path'].'\';document.forms[1].submit();return true;"><img src="'.PLX_CORE.'admin/theme/images/folder.png" alt="folder"  /></a>';
					echo '</td>';
					echo '<td><a title="'.plxUtils::strCheck($v['name']).'" href="javascript:void(0)" onclick="document.forms[1].folder.value=\''.$v['path'].'\';document.forms[1].submit();return true;">'.plxUtils::strCheck($v['name']).'</a></td>';
					echo '<td>'.L_MEDIAS_FOLDER.'</td>';
					echo '<td></td>';
					echo '<td></td>';
					echo '<td></td>';
					echo '</tr>';
				}
			}
			# Si on a des fichiers
			if($plxMedias->aFiles) {
				foreach($plxMedias->aFiles as $v) { # Pour chaque fichier
					$num++;
					echo '<tr class="line-'.($num%2).'">';
					echo '<td class="checkbox center"><input type="checkbox" name="idFile[]" value="'.$v['name'].'" /></td>';
					echo '<td class="icon center"><a onclick="this.target=\'_blank\';return true;" title="'.plxUtils::strCheck($v['name']).'" href="'.$v['path'].'"><img alt="" src="'.$v['.thumb'].'" class="thumb" /></a><br /></td>';
					echo '</td>';
					echo '<td>';
					echo '<a onclick="this.target=\'_blank\';return true;" title="'.plxUtils::strCheck($v['name']).'" href="'.$v['path'].'">'.plxUtils::strCheck($v['name']).'</a><br />';
					if($v['thumb']) {
						echo '<a onclick="this.target=\'_blank\';return true;" title="'.L_MEDIAS_THUMB.' : '.plxUtils::strCheck($v['name']).'" href="'.plxUtils::thumbName($v['path']).'">'.L_MEDIAS_THUMB.'</a> : '.$v['thumb']['infos'][0].' x '.$v['thumb']['infos'][1]. ' ('.plxUtils::formatFilesize($v['thumb']['filesize']).')';
					}
					echo '</td>';
					echo '<td>'.strtoupper($v['extension']).'</td>';
					echo '<td>'.plxUtils::formatFilesize($v['filesize']).'</td>';
					$dimensions = '&nbsp;';
					if(isset($v['infos']) AND isset($v['infos'][0]) AND isset($v['infos'][1])) {
						$dimensions = $v['infos'][0].' x '.$v['infos'][1];
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
			<p id="action_bottom">
				<?php plxUtils::printSelect('selection[]', $selectionList , '', false, '', 'id_selection_bottom') ?>
				<input class="button submit" type="submit" id="btn_ok_bottom" name="btn_ok_bottom" value="<?php echo L_OK ?>" onclick="return (confirmAction(this.form, 'id_selection_bottom', 'delete', 'idFile[]', '<?php echo L_CONFIRM_DELETE ?>')?true:confirmAction(this.form, 'id_selection_bottom', 'delete', 'idDir[]', '<?php echo L_CONFIRM_DELETE ?>'))" />
				<input type="hidden" name="sort" value="" />
				<input type="hidden" name="folder" value="<?php echo $_SESSION['folder'] ?>" />
				<?php echo plxToken::getTokenPostMethod() ?>
			</p>
		</div>
	</form>
	<div class="clear"></div>
</div>
<script type="text/javascript">
	document.getElementById('id_selection_top').onchange = action_change;
	document.getElementById('id_selection_bottom').onchange = action_change;
	document.getElementById('folder_list').onchange = function () {
		document.forms[1].folder.value=this.options[this.selectedIndex].value;
	};
</script>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>


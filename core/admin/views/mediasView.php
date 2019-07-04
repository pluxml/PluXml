<?php ob_start(); ?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasTop')) # Hook Plugins ?>

<div class="admin-title">
	<h2><?php echo L_MENU_MEDIAS ?></h2>
</div>

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

	<div id="files_manager" class="inline-form panel panel-content" >

		<div class="inline-form">
			<p class="no-margin">
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
		</div>

		<div class="grid">
    		<div class="col sml-12 med-6">
    			<?php echo L_MEDIAS_FOLDER ?>&nbsp;:&nbsp;
    			<?php echo $plxMedias->contentFolder() ?>
    			<input type="submit" name="btn_changefolder" value="<?php echo L_OK ?>" />
    			<button onclick="dialogBox('dlgNewFolder');return false;" id="btnNewFolder"><?php echo L_MEDIAS_NEW_FOLDER ?></button>
    			<?php if(!empty($_SESSION['folder'])) { ?>
    			<input type="submit" name="btn_delete" class="red" value="<?php echo L_DELETE_FOLDER ?>" onclick="return confirm('<?php printf(L_MEDIAS_DELETE_FOLDER_CONFIRM, $curFolder) ?>')" />
    			<?php } ?>
    			<input type="hidden" name="sort" value="" />
    			<?php echo plxToken::getTokenPostMethod() ?>
    		</div>
    
    		<div class="col sml-12 med-6 text-right">
    			<input type="text" id="medias-search" onkeyup="plugFilter()" placeholder="<?php echo L_SEARCH ?>..." title="<?php echo L_SEARCH ?>" />
    		</div>
		</div>
	</div>
	
	<div style="clear:both" class="scrollable-table panel">
	   	<div class="panel-content panel-title">
	   		<h3 class="no-margin"><?php echo L_MEDIAS_TITLE ?></h3>
    	</div>
		<table id="medias-table" class="panel-content">
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
					    $isImage = in_array(strtolower($v['extension']), $plxMedias->img_supported);
						echo '<tr>';
						echo '<td><input type="checkbox" name="idFile[]" value="'.$v['name'].'" /></td>';
						echo '<td class="icon">';
							if(is_file($v['path']) AND $isImage) {
								echo '<a class="overlay" title="'.plxUtils::strCheck($v['name']).'" href="'.$v['path'].'"><img alt="" src="'.$v['.thumb'].'" class="thumb" /></a>';
							}
							else 
							    echo '<img alt="" src="'.$v['.thumb'].'" class="thumb" />';
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
					echo '<td>'.$dimensions.'</td>';
					echo '<td>'.plxDate::formatDate(plxDate::timestamp2Date($v['date'])).'</td>';
					echo '</tr>';
				}
    			else echo '<tr><td colspan="7" class="center">'.L_MEDIAS_NO_FILE.'</td></tr>';
			?>
			</tbody>
		</table>
		<div class="panel-content panel-bottom">
			<?php plxUtils::printSelect('selection', $selectionList, '', false, 'no-margin', 'id_selection') ?>
			<input type="submit" name="btn_ok" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idFile[]', '<?php echo L_CONFIRM_DELETE ?>')" />
			<input type="submit" onclick="toggle_divs();return false" value="<?php echo L_MEDIAS_ADD_FILE ?>" />
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

// zoombox
var all = document.querySelectorAll(".overlay");
var mbox = document.getElementById("modal__box");
var mb = document.getElementById("modal");
for (var i = 0, nb = all.length; i < nb; i++) {
	all[i].addEventListener('click', function(e) {
		e.preventDefault();
		mbox.innerHTML = '<img src="'+this.href+'" alt="" />';
		mb.click();
	},false);
}
window.addEventListener("keydown", function (event) {
	// validate if the press key is the escape key
	if (event.code=="Escape" || event.key=="Escape" || event.keyCode==27) {
    	mbox.innerHTML = "";
    	if (mb.checked === true) {
    		mb.click();
    	}
    }	
});

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
?>

<?php $mainContent = ob_get_clean(); ?>
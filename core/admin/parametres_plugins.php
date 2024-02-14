<?php

/**
 * Gestion des plugins
 *
 * @package PLX
 * @author	Stephane F
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

if(isset($_POST['update']) OR (isset($_POST['selection']) AND in_array($_POST['selection'], array('delete', 'activate', 'deactivate')))) {
	$plxAdmin->plxPlugins->saveConfig($_POST);
	header('Location: parametres_plugins.php');
	exit;
}

function pluginsList($plugins, $defaultLang, $plugins_actifs) {
	/*
	 * plugins		array()		contient la liste des plugins à afficher
	 * defaultLang	string		langue utilisée dans l'admin
	 * plugins_actifs			true|false	true=liste des plugins actifs, false=liste des plugins inactifs
	 * */
	ob_start();
	$plxAdmin = plxAdmin::getInstance();#OR global $plxAdmin;
	if(sizeof($plugins)>0) {
		$num=0;
		foreach($plugins as $plugName => $plugInstance) {
			$ordre = ++$num;
			# détermination de l'icone à afficher
			if(is_file(PLX_PLUGINS.$plugName.'/icon.png'))
				$icon=PLX_PLUGINS.$plugName.'/icon.png';
			elseif(is_file(PLX_PLUGINS.$plugName.'/icon.jpg'))
				$icon=PLX_PLUGINS.$plugName.'/icon.jpg';
			elseif(is_file(PLX_PLUGINS.$plugName.'/icon.gif'))
				$icon=PLX_PLUGINS.$plugName.'/icon.gif';
			else
			$icon='theme/images/icon_plugin.png';

			# plugin activé uniquement côté site (<scope> == 'site')
			if(empty($plugInstance) and $plugInstance=$plxAdmin->plxPlugins->getInstance($plugName)) {
				$plugInstance->getInfos();
			}

			# plugin non configuré
			$no_config = ($plugins_actifs AND file_exists(PLX_PLUGINS.$plugName.'/config.php') AND !file_exists(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.xml'));
?>
			<tr class="top<?=$no_config?' text-red':''?>" data-scope="<?= $plugInstance->getInfo('scope') ?>">
				<td>
					<input type="hidden" name="plugName[]" value="<?= $plugName ?>" />
					<input type="checkbox" name="chkAction[]" value="<?= $plugName ?>" />
				</td>
<?php /* icon */ ?>
				<td><img src="<?= $icon ?>" alt="<?= $plugName ?> icon" width="48" /></td>
<?php /* plugin infos */ ?>
				<td class="wrap">
					<p>
<?php /* title + version */ ?>
						<strong class="title"><?= plxUtils::strCheck($plugInstance->getInfo('title')) ?></strong><strong> - <?= L_PLUGINS_VERSION ?> <?= plxUtils::strCheck($plugInstance->getInfo('version')) ?></strong>
<?php /* date */
					if(empty($plugInstance->getInfo('date'))) {
?>
						<span>(<?= plxUtils::strCheck($plugInstance->getInfo('date')) ?>)</span>
<?php
					}
?>
					</p>
<?php
					# message d'alerte si plugin non configuré
					if($no_config) {
?>
					<p><a title="<?= L_PLUGINS_CONFIG_TITLE ?>" href="parametres_plugin.php?p=<?= urlencode($plugName) ?>"><strong class="text-blue"><?= L_PLUGIN_NO_CONFIG ?></strong></a></p>
<?php
					}
	/* description */
?>
					<p class="description"><?= plxUtils::strCheck($plugInstance->getInfo('description')) ?></p>
<?php /* author */ ?>
					<p><?= L_PLUGINS_AUTHOR ?> : <?= plxUtils::strCheck($plugInstance->getInfo('author')) ?></p>
<?php /* site */
					if($site = plxUtils::strCheck($plugInstance->getInfo('site'))) {
?>
					<p><a href="<?= $site ?>" title="<?= $site ?>" target="_blank"><?= $site ?></a></p>
<?php
					}
?>
				</td>
<?php /* colonne pour trier les plugins */
				if($plugins_actifs) {
?>
				<td>
					<input size="2" maxlength="3" type="text" name="plugOrdre[<?= $plugName ?>]" value="<?= $ordre ?>" />
				</td>
<?php
				}

	/* affichage des liens du plugin */
?>
				<td class="right">
<?php
	/* lien configuration */
					if(is_file(PLX_PLUGINS.$plugName.'/config.php')) {
?>
					<a title="<?= L_PLUGINS_CONFIG_TITLE ?>" href="parametres_plugin.php?p=<?= urlencode($plugName) ?>"><?= L_PLUGINS_CONFIG ?></a><br />
<?php
					}
	/* lien pour code css */
?>
					<a title="<?= L_PLUGINS_CSS_TITLE ?>" href="parametres_plugincss.php?p=<?= urlencode($plugName) ?>"><?= L_PLUGINS_CSS ?></a><br />
<?php
	/* lien aide */
					$all_langs = array_unique(array(
						$plugInstance->default_lang,
						PLX_SITE_LANG,
						DEFAULT_LANG
					));
					foreach($all_langs as $lang) {
						if(is_file(PLX_PLUGINS . $plugName . '/lang/' . $lang . '-help.php')) {
?>
					<a title="<?= L_HELP_TITLE ?>" href="parametres_help.php?help=plugin&page=<?= urlencode($plugName) ?>&lang=<?= $lang ?>"><?= L_HELP ?></a>
<?php
							break;
						}
					}
?>
			</td>
		</tr>
<?php
		}
	}
	else {
		$colspan = empty($_SESSION['selPlugins']) ? 4 : 5;
?>

		<tr>
			<td colspan="<?= $colspan ?>" class="center"><?= L_NO_PLUGIN ?></td>
		</tr>
<?php
	}
	return ob_get_clean();
}

# récuperation de la liste des plugins inactifs
$aInactivePlugins = $plxAdmin->plxPlugins->getInactivePlugins();
# nombre de plugins actifs
$nbActivePlugins = sizeof($plxAdmin->plxPlugins->aPlugins);
# nombre de plugins inactifs
$nbInactivePlugins = sizeof($aInactivePlugins);
# récuperation du type de plugins à afficher
$_GET['sel'] = isset($_GET['sel']) ? intval(plxUtils::nullbyteRemove($_GET['sel'])) : '';
$session = isset($_SESSION['selPlugins']) ? $_SESSION['selPlugins'] : 1;
$sel = in_array($_GET['sel'], array('0', '1')) ? intval($_GET['sel']) : $session;
$_SESSION['selPlugins'] = $sel;
if($sel == 0) {
	# plugins désactivés
	$aSelList = array(
		'' => L_FOR_SELECTION,
		'activate' => L_PLUGINS_ACTIVATE,
		'-' => '-----',
		'delete' => L_PLUGINS_DELETE,
	);
	$plugins = pluginsList($aInactivePlugins, $plxAdmin->aConf['default_lang'], false);
} else {
	# plugins actifs
	$aSelList = array(
		'' => L_FOR_SELECTION,
		'deactivate'=> L_PLUGINS_DEACTIVATE,
	);
	$plugins = pluginsList($plxAdmin->plxPlugins->aPlugins, $plxAdmin->aConf['default_lang'], true);
}
$data_rows_num = ($sel=='1') ?  'data-rows-num=\'name^="plugOrdre"\'' : false;

# On inclut le header
include 'top.php';

?>
<form action="parametres_plugins.php" method="post" id="form_plugins">
	<?= plxToken::getTokenPostMethod() ?>
	<div class="inline-form action-bar">
		<h2>
			<?= L_PLUGINS_TITLE ?>
			<span data-scope="admin">Admin</span>
			<span data-scope="site">Site</span>
		</h2>

		<ul class="menu">
			<li><a class="<?= empty($_SESSION['selPlugins']) ? '' : 'selected' ?>" href="parametres_plugins.php?sel=1"><?= L_PLUGINS_ACTIVE_LIST ?></a>&nbsp;(<?= $nbActivePlugins ?>)</li>
			<li><a class="<?= empty($_SESSION['selPlugins']) ? 'selected' : '' ?>" href="parametres_plugins.php?sel=0"><?= L_PLUGINS_INACTIVE_LIST ?></a>&nbsp;(<?= $nbInactivePlugins ?>)</li>
		</ul>
		<?php plxUtils::printSelect('selection', $aSelList,'', false,'','id_selection'); ?>
		<input type="submit" name="submit" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'chkAction[]', '<?= L_CONFIRM_DELETE ?>')" />
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
<?php
if($sel==1) {
?>
		<input type="submit" name="update" value="<?= L_PLUGINS_APPLY_BUTTON ?>" />
<?php
}
?>
	</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="plugins-table" class="full-width" <?php if(!empty($data_rows_num)) echo $data_rows_num; ?>>
			<thead>
				<tr>
					<th><input type="checkbox" onclick="checkAll(this.form, 'chkAction[]')" /></th>
					<th class="col-icon">&nbsp;</th>
					<th class="col-description">
						<i class="ico icon-search"></i>
						<input type="text" id="plugins-search" onkeyup="plugFilter()" placeholder="<?= L_SEARCH ?>..." title="<?= L_SEARCH ?>" />
					</th>
<?php
if(!empty($_SESSION['selPlugins'])) {
?>
					<th title="<?= L_PLUGINS_LOADING_SORT ?>"><?= L_CAT_LIST_ORDER ?></th>
<?php
}
?>
					<th><?= L_PLUGINS_ACTION ?></th>
				</tr>
			</thead>
			<tbody>
<?= $plugins ?>
			</tbody>
		</table>
	</div>

</form>

<script>
function plugFilter() {
	var input, filter, table, tr, td, i;
	filter = document.getElementById("plugins-search").value;
	table = document.getElementById("plugins-table");
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
		localStorage.setItem("plugins_search", filter);
	}
}
if (typeof(Storage) !== "undefined" && localStorage.getItem("plugins_search") !== "undefined") {
	input = document.getElementById("plugins-search");
	input.value = localStorage.getItem("plugins_search");
	plugFilter();
}
</script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot'));

# On inclut le footer
include 'foot.php';

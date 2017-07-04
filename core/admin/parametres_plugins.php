<?php

/**
 * Gestion des plugins
 *
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

if(isset($_POST['update']) OR (isset($_POST['selection']) AND in_array($_POST['selection'], array('delete', 'activate', 'deactivate')))) {
	$plxAdmin->plxPlugins->saveConfig($_POST);
	header('Location: parametres_plugins.php');
	exit;
}

# récupération du type de plugins à afficher
$sel_entry = (isset($_GET['sel'])) ? filter_input(INPUT_GET, 'sel', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
if(($sel_entry === null) and (isset($_SESSION['selPlugins']))) {
	$sel_entry = filter_var($_SESSION['selPlugins'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
}
$sel = (($sel_entry === null)) ? 1 : (($sel_entry) ? 1 : 0);
$_SESSION['selPlugins'] = $sel;

# liste des actions possibles sur la sélection de plugins
if($sel=='1') {
	$option1 = 'deactivate'; $caption1 = L_PLUGINS_DEACTIVATE;
	$pluginsList		= $plxAdmin->plxPlugins->aPlugins;
	$nbActivePlugins	= count($pluginsList);
	$nbInactivePlugins	= count($plxAdmin->plxPlugins->getInactivePlugins());
} else {
	$option1 = 'activate'; $caption1 = L_PLUGINS_ACTIVATE;
	$pluginsList		= $plxAdmin->plxPlugins->getInactivePlugins();
	$nbActivePlugins	= count($plxAdmin->plxPlugins->aPlugins);
	$nbInactivePlugins	= count($pluginsList);
}
$aSelList = array(
	'' => L_FOR_SELECTION,
	$option1 => $caption1,
	'-' => '-----',
	'delete' => L_PLUGINS_DELETE
);

function printInfosPlugin($plugInstance, $plugName, $sel) {
	# message d'alerte si plugin non configuré
	if(($sel == 1) AND file_exists(PLX_PLUGINS.$plugName.'/config.php') AND !file_exists(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.xml')) {
?>
						<span style="margin-top:5px" class="alert red float-right"><?php echo L_PLUGIN_NO_CONFIG; ?></span>
<?php
	} # fin d'alerte
	foreach(explode(' ', 'title version date description author site') as $field) {
		# saut de ligne
		if(strpos('description author', $field) !== false)
			echo "<br />\n";
		$value = plxUtils::strCheck($plugInstance->getInfo($field));

		if(!empty($value)) {
			switch($field) {
				case 'title':
				case 'version':
					$prefix = ($field == 'version') ? ' - '.L_PLUGINS_VERSION.' : ' : '';
					echo <<< EOT
$prefix<strong class="plugin-${field}">$value</strong>
EOT;
					break;
				case 'date':
				case 'description':
				case 'author':
					$prefix = ($field == 'author') ? L_PLUGINS_AUTHOR.' : ' : '';
					if($field == 'date')
						$value = ' ('.$value.')';
					echo <<< EOT
$prefix$value
EOT;
					if($field == 'author')
						echo ' ';
					break;
				case 'site':
					echo <<< EOT
<a href="$value">$value</a>
EOT;
			}

		}
	}
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<form action="parametres_plugins.php" method="post" id="form_plugins">

	<div class="inline-form action-bar">
		<h2><?php echo L_PLUGINS_TITLE ?></h2>
		<ul class="menu"><?php /* fil d'ariane */ ?>
<?php
	$li = array(
		1 => array(L_PLUGINS_ACTIVE_LIST, $nbActivePlugins),
		0 => array(L_PLUGINS_INACTIVE_LIST, $nbInactivePlugins)
	);
	foreach($li as $k=>$infos) {
		list($caption, $counter) = $infos;
		if($k == $sel) { ?>
			<li><span class="selected"><?php echo $caption; ?></span> (<?php echo $counter; ?>)</li>
<?php	} else { ?>
			<li><a href="parametres_plugins.php?sel=<?php echo $k; ?>"><?php echo $caption; ?></a> (<?php echo $counter; ?>)</li>
<?php	}
	}
?>
		</ul>
		<div class="flex-line">
			<?php echo plxToken::getTokenPostMethod() ?>
			<?php plxUtils::printSelect('selection', $aSelList,'', false,'','id_selection'); ?>
			<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'chkAction[]', '<?php echo L_CONFIRM_DELETE ?>')" />
			<span class="spacer">&nbsp;</span>
<?php if($sel==1) { ?>
			<input type="submit" name="update" value="<?php echo L_PLUGINS_APPLY_BUTTON ?>" />
<?php } ?>
		</div>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="plugins-table" class="full-width">
			<thead>
				<tr>
					<th><input type="checkbox" onclick="checkAll(this.form, 'chkAction[]')" /></th>
					<th>&nbsp;</th>
					<th><input type="text" id="plugins-search" placeholder="<?php echo L_SEARCH ?>..." title="<?php echo L_SEARCH ?>" /></th>
					<?php if($_SESSION['selPlugins']=='1') : ?>
					<th><?php echo L_PLUGINS_LOADING_SORT ?></th>
					<?php endif; ?>
					<th><?php echo L_PLUGINS_ACTION ?></th>
				</tr>
			</thead>
			<tbody>
<?php
if(!empty($pluginsList)) {
	$ordre = 0;
	foreach($pluginsList as $plugName => $plugInstance) {
		$ordre++;

		# détermination de l'icone à afficher
		$icon=PLX_CORE.'admin/theme/images/icon_plugin.png';
		$name = PLX_PLUGINS.$plugName.'/icon.';
		foreach(explode(' ', 'png jpg jpeg gif svg') as $ext) {
			if(is_file($name.$ext)) {
				$icon = $name.$ext;
				break;
			}
		}
?>
				<tr class="top">
					<td>
						<input type="hidden" name="plugName[]" value="<?php echo $plugName; ?>" />
						<input type="checkbox" name="chkAction[]" value="<?php echo $plugName; ?>" />
					</td>
					<td><img src="<?php echo $icon; ?>" alt="" /></td>
					<td class="wrap"><?php /* infos du plugin */ ?>
<?php printInfosPlugin($plugInstance, $plugName, $sel); ?>
					</td>
<?php
		if($sel == 1) { # colonne pour trier les plugins actifs
?>
					<td><input maxlength="3" type="text" name="plugOrdre['<?php echo $plugName; ?>']" value="<?php echo $ordre; ?>" /></td>
<?php
		}
?>
					<td class="right"><?php /* affichage des liens du plugin */ ?>
<?php
		$links = array();
		if(is_file(PLX_PLUGINS.$plugName.'/config.php')) { /* lien configuration */
			$links[] = array(L_PLUGINS_CONFIG, L_PLUGINS_CONFIG_TITLE, 'parametres_plugin.php?p='.urlencode($plugName));
		}
		/* lien pour code css */
		$links[] = array(L_PLUGINS_CSS, L_PLUGINS_CSS_TITLE, 'parametres_plugincss.php?p='.urlencode($plugName));
		if(is_file(PLX_PLUGINS.$plugName.'/lang/'.$plxAdmin->aConf['default_lang'].'-help.php')) /* lien aide */
			$links[] = array(L_HELP, L_HELP_TITLE, 'parametres_help.php?help=plugin&amp;page='.urlencode($plugName));
		echo implode("<br />\n", array_map(
			function($item) {
				list($caption, $title, $href) = $item;
				# on imprime les links (liens)
				return <<< EOT
						<a href="$href" title="${title}_TITLE">$caption</a>

EOT;
			},
			$links
		));
?>
					</td>
				</tr>
<?php
		} /* fin de boucle pour les plugins */
} else {
?>
				<tr>
					<td class="center" colspan="<?php echo (4 + $sel); ?>"><?php echo L_NO_PLUGIN; ?></td>
				</tr>
<?php
}
?>
			</tbody>
		</table>
	</div>

	<?php if($_SESSION['selPlugins']=='1') : ?>
	<?php endif; ?>

</form>

<script type="text/javascript">
	(function(selector) {

		'use strict';

		const key = 'plugins-search';

		var input = document.getElementById(selector);
		if(input != null) {

			input.addEventListener('keyup', function(event) {
				event.preventDefault();
				var	query = event.target.value.trim().toLowerCase();
				if(query.length > 0) {
					var	rows = document.querySelectorAll('#plugins-table tbody tr');
					if(rows != null) {
						rows.forEach(function(item) {
							var titleElmt = item.querySelector('.plugin-title');
							if(titleElmt != null) {
								var title = titleElmt.innerHTML.toLowerCase();
								if(title.search(query) >= 0) {
									item.classList.remove('hide');
								} else {
									item.classList.add('hide');
								}
							}
						});
					}
				} else {
					var	rows = document.querySelectorAll('#plugins-table tbody tr.hide');
					if(rows != null) {
						rows.forEach(function(item) {
							item.classList.remove('hide');
						});
					}
				}

				if (typeof(Storage) !== "undefined") {
					localStorage.setItem(key, query);
				}
			});

			if (typeof(Storage) !== "undefined" && localStorage.getItem(key) !== "undefined") {
				input.value = localStorage.getItem(key);
				// plugFilter();
			}

		}

	})('plugins-search');
</script>

<?php
if($sel === 1) {
?>
<script type="text/javascript">
	dragAndDrop('#plugins-table tbody tr', '#plugins-table tbody tr input[name^="plugOrdre"]');
</script>
<?php
}

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
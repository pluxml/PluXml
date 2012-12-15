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

if(isset($_POST['submit'])) {

	$_POST['selection'] = $_POST['selection'][0] | $_POST['selection'][1];

	if($_POST['selection'] == 'delete') {
		if(!empty($_POST['action'])) {
			$error=false;
			foreach($_POST['action'] as $plugName => $activate) {
				if($plxAdmin->plxPlugins->deleteDir(realpath(PLX_PLUGINS.$plugName))) {
					unset($_POST['plugName'][$plugName]);
					unset($plxAdmin->plxPlugins->aPlugins[$plugName]);
				}
				else $error=true;
			}
			if(!$error)	$error=!$plxAdmin->plxPlugins->saveConfig($_POST);
			if($error) plxMsg::Error(L_PLUGINS_DELETE_ERROR);
			else plxMsg::Info(L_PLUGINS_DELETE_SUCCESSFUL);
			header('Location: parametres_plugins.php');
			exit;
		}
	}
	elseif($_POST['selection'] == 'activate' OR $_POST['selection'] == 'deactivate') {
		$plxAdmin->plxPlugins->saveConfig($_POST);
		header('Location: parametres_plugins.php');
		exit;
	}
}
elseif(isset($_POST['update'])) {
	$plxAdmin->plxPlugins->saveConfig($_POST);
	header('Location: parametres_plugins.php');
	exit;
}

# on récupère la liste des plugins dans le dossier plugins
$plxAdmin->plxPlugins->getList();

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2><?php echo L_PLUGINS_TITLE ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsTop')) # Hook Plugins ?>

<form action="parametres_plugins.php" method="post" id="form_plugins">
<p><?php echo plxToken::getTokenPostMethod() ?></p>
<p>
	<?php plxUtils::printSelect('selection[]', array(
								'' 			=> L_FOR_SELECTION,
								'activate'	=> L_PLUGINS_ACTIVATE,
								'deactivate'=> L_PLUGINS_DEACTIVATE,
								'-'			=> '-----',
								'delete'	=> L_PLUGINS_DELETE
							),'', false,'',false);
	?>
	<input class="button submit" type="submit" name="submit" value="<?php echo L_OK ?>" />
</p>

<table class="table">
<thead>
	<tr>
		<th class="checkbox">&nbsp;</th>
		<th class="icon">&nbsp;</th>
		<th class="description"><a title="<?php echo L_PLUGINS_ALPHA_SORT ?>" href="parametres_plugins.php?sort"><?php echo L_MENU_CONFIG_PLUGINS ?></a></th>
		<th class="col"><a href="parametres_plugins.php"><?php echo L_PLUGINS_LOADING_SORT ?></a></th>
		<th class="action"><?php echo L_PLUGINS_ACTION ?></th>
	</tr>
</thead>
<tbody>
	<?php
	$tmp = array();
	foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugin) {
		if(isset($plugin['instance']))
			$tmp[] = strtolower($plugin['instance']->getInfo('title'));
		else
			unset($plxAdmin->plxPlugins->aPlugins[$plugName]);
	}
	if(sizeof($tmp)>0) {
		# Tri des plugins par titre
		if(isset($_GET['sort']) OR !is_file(path(XMLFILE_PLUGINS))) {
			array_multisort($tmp, $plxAdmin->plxPlugins->aPlugins);
		}

		# Affichage des plugins
		$num=1;
		foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugAttrs) {
			$plugin = $plugAttrs['instance'];

			# determination de l'icone à afficher
			if(is_file(PLX_PLUGINS.$plugName.'/icon.png'))
				$icon=PLX_PLUGINS.$plugName.'/icon.png';
			elseif(is_file(PLX_PLUGINS.$plugName.'/icon.jpg'))
				$icon=PLX_PLUGINS.$plugName.'/icon.jpg';
			elseif(is_file(PLX_PLUGINS.$plugName.'/icon.gif'))
				$icon=PLX_PLUGINS.$plugName.'/icon.gif';
			else
			$icon=PLX_CORE.'admin/theme/images/icon_plugin.png';

			echo '<tr class="plugins-'.$plugAttrs['activate'].' top">';

			echo '<td>';
			echo '<input type="hidden" name="plugName['.$plugName.']" value="'.$plugAttrs['activate'].'" />';
			echo '<input type="hidden" name="plugTitle['.$plugName.']" value="'.plxUtils::strCheck($plugin->getInfo('title')).'" />';
			echo '<input type="checkbox" name="action['.$plugName.']" />';
			echo '</td>';

			echo '<td><img src="'.$icon.'" alt="" /></td>';

			# si pour le plugin un fichier config.php existe on créer le lien pour accèder à l'écran
			# de configuration du plugin
			echo '<td>';
			echo '<strong>'.plxUtils::strCheck($plugin->getInfo('title')).'</strong>';
			echo ' - '.L_PLUGINS_VERSION.' <strong>'.plxUtils::strCheck($plugin->getInfo('version')).'</strong>';
			if($plugin->getInfo('date')!='')
			echo ' ('.plxUtils::strCheck($plugin->getInfo('date')).')';
			echo '<br />';
			echo plxUtils::strCheck($plugin->getInfo('description')).'<br />';
			echo L_PLUGINS_AUTHOR.' : '.plxUtils::strCheck($plugin->getInfo('author'));
			if($plugin->getInfo('site')!='') echo ' - <a href="'.plxUtils::strCheck($plugin->getInfo('site')).'">'.plxUtils::strCheck($plugin->getInfo('site')).'</a>';
			echo '</td>';

			echo '<td>';
			echo '<input size="2" maxlength="3" type="text" name="plugOrdre['.$plugName.']" value="'.$num++.'" />';
			echo '</td>';

			# affichage des liens pour acceder à l'aide et à la configuration du plugin
			echo '<td class="right">';
			if(is_file(PLX_PLUGINS.$plugName.'/lang/'.$plxAdmin->aConf['default_lang'].'-help.php'))
			echo '<a title="'.L_PLUGINS_HELP_TITLE.'" href="parametres_pluginhelp.php?p='.urlencode($plugName).'">'.L_PLUGINS_HELP.'</a>';
			# affichage du lien pour configurer le plugin
			if(is_file(PLX_PLUGINS.$plugName.'/config.php'))
			echo '&nbsp;<a title="'.L_PLUGINS_CONFIG_TITLE.'" href="parametres_plugin.php?p='.urlencode($plugName).'">'.L_PLUGINS_CONFIG.'</a>';
			//if(trim($plugin->getInfo('requirements'))!='')
			//echo L_PLUGINS_REQUIREMENTS.' : '.plxUtils::strCheck($plugin->getInfo('requirements'));
			echo '&nbsp;</td>';
			echo '</tr>';
		}
	}
	else
		echo '<tr><td colspan="5" class="center">'.L_NO_PLUGIN.'</td></tr>';

?>
</tbody>
</table>

<p class="center">
	<input class="button update " type="submit" name="update" value="<?php echo L_PLUGINS_APPLY_BUTTON ?>" />
</p>


<p>
	<?php plxUtils::printSelect('selection[]', array(
								'' 			=> L_FOR_SELECTION,
								'activate'	=> L_PLUGINS_ACTIVATE,
								'deactivate' 	=> L_PLUGINS_DEACTIVATE,
								'-'			=> '-----',
								'delete'	=> L_PLUGINS_DELETE
							),'', false,'',false);
	?>
	<input class="button submit" type="submit" name="submit" value="<?php echo L_OK ?>" />
</p>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>

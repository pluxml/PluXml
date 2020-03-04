<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Philippe-M, Pedro "P3ter" CADETE"
 **/

include __DIR__ .'/prepend.php';
use Pluxml\PlxToken;

# Control du token du formulaire
PlxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: configuration.php');
	exit;
}

# On inclut le header
include __DIR__ .'/tags/top.php';

//Vue.js datas initialisation
$builkDatas = array(
		'panelToDisplay' => 'basic',
);
$datas = json_encode($builkDatas);
?>

<div class="adminheader">
	<h2 class="h3-like"><?= L_MENU_CONFIG ?></h2>
</div>

<div class="admin mtm grid-6">
	<div class="col-1 mtl">
		<ul class="mlm configMenu unstyled">
			<li v-on:click="panelToDisplay='basic'"><span><?= L_MENU_CONFIG_BASE ?></span></li>
			<li v-on:click="panelToDisplay='display'"><span><?= L_MENU_CONFIG_VIEW ?></span></li>
			<li v-on:click="panelToDisplay='advanced'"><span><?= L_MENU_CONFIG_ADVANCED ?></span></li>
			<li v-on:click="panelToDisplay='users'"><span><?= L_MENU_CONFIG_USERS ?></span></li>
			<li v-on:click="panelToDisplay='plugins'"><span><?= L_MENU_CONFIG_PLUGINS ?></span></li>
			<li v-on:click="panelToDisplay='themes'"><span><?= L_THEMES ?></span></li>
			<li v-on:click="panelToDisplay='about'"><span><?= L_MENU_CONFIG_INFOS ?></span></li>
		</ul>
	</div>
	<div v-if="panelToDisplay=='basic'" class="panel col-5">
		<?php
			include __DIR__ .'/tags/configurationBasic.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));
		?>
	</div>
	<div v-if="panelToDisplay=='display'"class="panel col-5">
		<?php
			include __DIR__ .'/tags/configurationDisplay.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayFoot'));
		?>
	</div>
	<div v-if="panelToDisplay=='advanced'"class="panel col-5">
		<?php
			include __DIR__ .'/tags/configurationAdvanced.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));
		?>
	</div>
	<div v-if="panelToDisplay=='users'"class="panel col-5">
		<?php 
			include __DIR__ .'/tags/configurationUsers.php';
			eval($plxAdmin->plxPlugins->callHook('AdminUsersFoot'));
		?>
	</div>
	<div v-if="panelToDisplay=='plugins'"class="panel col-5">
		<?php 
			include __DIR__ .'/tags/configurationPlugins.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot'));
		?>
	</div>
	<div v-if="panelToDisplay=='themes'"class="panel col-5">
		<?php 
			include __DIR__ .'/tags/configurationThemes.php';
			eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));
		?>
	</div>
	<div v-if="panelToDisplay=='about'"class="panel col-5">
		<?php 
			include __DIR__ .'/tags/configurationAbout.php';
			eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));
		?>
	</div>
</div>

<?php
# On inclut le footer
include __DIR__ .'/tags/foot.php';
?>

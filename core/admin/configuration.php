<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Philippe-M, Pedro "P3ter" CADETE"
 **/

include __DIR__ .'/tags/prepend.php';
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
?>

<div class="adminheader">
	<h2 class="h3-like"><?= L_MENU_CONFIG ?></h2>
</div>

<div class="admin mtm grid-6">
	<div class="col-1 mtl">
		<ul class="mlm configMenu unstyled">
			<li><?= L_MENU_CONFIG_BASE ?></li>
			<li><?= L_MENU_CONFIG_VIEW ?></li>
			<li><?= L_MENU_CONFIG_ADVANCED ?></li>
			<li><?= L_MENU_CONFIG_USERS ?></li>
			<li><?= L_MENU_CONFIG_PLUGINS ?></li>
			<li><?= L_THEMES ?></li>
			<li><?= L_MENU_CONFIG_INFOS ?></li>
		</ul>
	</div>
	<div class="panel col-5">
		<?php
			include __DIR__ .'/tags/configurationBasic.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));
		?>
	</div>
	<div class="panel col-5">
		<?php
			include __DIR__ .'/tags/configurationDisplay.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayFoot'));
		?>
	</div>
	<div class="panel col-5">
		<?php
			include __DIR__ .'/tags/configurationAdvanced.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));
		?>
	</div>
	<div class="panel col-5">
		<?php 
			include __DIR__ .'/tags/configurationUsers.php';
			eval($plxAdmin->plxPlugins->callHook('AdminUsersFoot'));
		?>
	</div>
	<div class="panel col-5">
		<?php 
			include __DIR__ .'/tags/configurationPlugins.php';
			eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot'));
		?>
	</div>
	<div class="panel col-5">
		<?php 
			include __DIR__ .'/tags/configurationThemes.php';
			eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));
		?>
	</div>
	<div class="panel col-5">
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

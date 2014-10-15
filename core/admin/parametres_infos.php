<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2><?php echo L_CONFIG_INFOS_TITLE ?></h2>

<p><?php echo L_CONFIG_INFOS_DESCRIPTION ?></p>
<p><strong><?php echo L_PLUXML_CHECK_VERSION ?></strong><br />
<?php echo $plxAdmin->checkMaj(); ?></p>
<p><strong><?php echo L_PLUXML_VERSION; ?> <?php echo $plxAdmin->version; ?> (<?php echo L_INFO_CHARSET ?> <?php echo PLX_CHARSET ?>)</strong></p>
<p><?php echo L_INFO_PHP_VERSION; ?> : <?php echo phpversion(); ?></p>
<ul class="list-unstyled">
	<li><?php echo L_INFO_MAGIC_QUOTES; ?> : <?php echo get_magic_quotes_gpc(); ?></li>
	<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_articles']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_commentaires']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_statiques']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['images']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['documents']); ?>
	<?php plxUtils::testModReWrite() ?>
	<?php plxUtils::testLibGD() ?>
	<?php plxUtils::testMail() ?>
</ul>
<p><?php echo L_CONFIG_INFOS_NB_CATS ?> <?php echo sizeof($plxAdmin->aCats); ?></p>
<p><?php echo L_CONFIG_INFOS_NB_STATICS ?> <?php echo sizeof($plxAdmin->aStats); ?></p>
<p><?php echo L_CONFIG_INFOS_WRITER ?> <?php echo $plxAdmin->aUsers[$_SESSION['user']]['name'] ?></p>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsInfos')) ?>
<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
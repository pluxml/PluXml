<?php 
$adminTitle = L_CONFIG_INFOS_TITLE;
$inputChecked = true;
?>

<?php ob_start(); ?>

<p><strong><?php echo L_PLUXML_CHECK_VERSION ?></strong></p>
<?php
    $plxAdmin->checkMaj();
    if ($maj['maj'] == false) {
        $classCss = 'green';
    }
    else {
        $classCss = 'red';
    }
    echo '<div class="alert '.$classCss.'">'.$maj['msg'].'</div>';
?>

<p><?php echo L_CONFIG_INFOS_DESCRIPTION ?></p>

<p><strong><?php echo L_PLUXML_VERSION; ?> <?php echo PLX_VERSION; ?> (<?php echo L_INFO_CHARSET ?> <?php echo PLX_CHARSET ?>)</strong></p>
<ul class="unstyled-list">
	<li><?php echo L_INFO_PHP_VERSION; ?> : <?php echo phpversion(); ?></li>
	<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
	<li><?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
	<?php } ?>
</ul>
<ul class="unstyled-list">
	<?php plxUtils::testWrite(PLX_ROOT) ?>
	<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH); ?>
	<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_articles']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_commentaires']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_statiques']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['medias']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_plugins']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_themes']); ?>
	<?php plxUtils::testModReWrite() ?>
	<?php plxUtils::testLibGD() ?>
	<?php plxUtils::testLibXml() ?>
	<?php plxUtils::testMail() ?>
</ul>
<p><?php echo L_CONFIG_INFOS_NB_CATS ?> <?php echo sizeof($plxAdmin->aCats); ?></p>
<p><?php echo L_CONFIG_INFOS_NB_STATICS ?> <?php echo sizeof($plxAdmin->aStats); ?></p>
<p><?php echo L_CONFIG_INFOS_WRITER ?> <?php echo $plxAdmin->aUsers[$_SESSION['user']]['name'] ?></p>

<?php 
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsInfos'))
?>

<?php $mainContent = ob_get_clean(); ?>
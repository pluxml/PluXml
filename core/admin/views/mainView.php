<?php
$lang = $plxAdmin->aConf['default_lang'];
$titre = plxUtils::strCheck($plxAdmin->aConf['title']).' - '.L_ADMIN;
$charset = strtolower(PLX_CHARSET);
$scriptName = basename($_SERVER['SCRIPT_NAME'], ".php"); 
$adminUrl = PLX_ROOT.'core/admin';
$siteTitle = plxUtils::strCheck($plxAdmin->aConf['title']);
$siteUrl = PLX_ROOT;
$backToSite = L_BACK_TO_SITE;
$profilPicture = PLX_CORE.'admin/theme/images/pluxml.png';
$userName = plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']);
$logOutUrl = PLX_CORE.'admin/auth.php?d=1';
$logOutTitle = L_ADMIN_LOGOUT_TITLE;
$logOut = L_ADMIN_LOGOUT;
$pluxmlVersion = $plxAdmin->aConf['version'];
$hookAdminTopEndHead = eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead'));
$hookAdminTopBottomHead = eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
$hookAdminFootEndBody = eval ($plxAdmin->plxPlugins->callHook('AdminFootEndBody'));
?>

<?php ob_start(); ?>
	<?php if(is_file(PLX_ROOT.$plxAdmin->aConf['custom_admincss_file'])): ?>
		<link rel="stylesheet" type="text/css" href="'.PLX_ROOT.$plxAdmin->aConf['custom_admincss_file'].'" media="screen" />
	<?php endif; ?>
<?php $custom_admincss = ob_get_clean();?>

<?php ob_start(); ?>
	<?php if(file_exists(PLX_ROOT.$plxAdmin->aConf['racine_plugins'].'admin.css')): ?>
		<link rel="stylesheet" type="text/css" href="'.PLX_ROOT.$plxAdmin->aConf['racine_plugins'].'admin.css" media="screen" />
    <?php endif; ?>
<?php $plugin_admincss = ob_get_clean();?>

<?php ob_start(); ?>
	<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])): ?>
		<li class="inbl">
			<small><a class="back-blog" href="<?php echo $plxAdmin->urlRewrite('?blog'); ?>" title="<?php echo L_BACK_TO_BLOG_TITLE ?>"><?php echo L_BACK_TO_BLOG;?></a></small>
		</li>
	<?php endif; ?>
<?php $blogLink = ob_get_clean();?>

<?php ob_start(); ?>
	<?php $maj = $plxAdmin->checkMaj(); if ($maj['maj'] == true): ?>
		<!-- TODO : CHANGE TO A LANG VARIABLE -->
		<small><span class="label"><a href="/core/admin/parametres_infos.php">New</a></span></small>
	<?php endif; ?>
<?php $pluxmlMaj = ob_get_clean();?>

<?php ob_start(); ?>
	<?php if(is_file(PLX_ROOT.'install.php')): ?>
    	<p class="alert red"><?php echo L_WARNING_INSTALLATION_FILE ?></p>
        <?php plxMsg::Display();?>
	<?php endif; ?>
<?php $adminMessage = ob_get_clean();?>

<?php include 'aside.php'; ?>
<?php include 'gabarit.php'; ?>




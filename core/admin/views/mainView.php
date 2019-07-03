<?php  ############### HEADER and MAIN ###############
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
		<li class="inbl pas">
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
    	<p class="alert"><?php echo L_WARNING_INSTALLATION_FILE ?></p>
        <?php plxMsg::Display();?>
	<?php endif; ?>
<?php $adminMessage = ob_get_clean();?>

<?php ############### ASIDE ###############
$menus = array();
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);
$menus[] = plxUtils::formatMenu(L_DASHBOARD_TITLE, PLX_CORE.'admin/index.php?page=1', L_MENU_ARTICLES_TITLE, false, false, $arts_mod, '<i class="ico icon-th-large"></i>');
$nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
$arts_mod = $nbartsmod>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/index.php?sel=mod&amp;page=1\';return false;">'.$nbartsmod.'</span>':'';
$menus[] = plxUtils::formatMenu(L_MENU_ARTICLES, PLX_CORE.'admin/articles.php?page=1', L_MENU_ARTICLES_TITLE, false, false, $arts_mod, '<i class="ico icon-doc-inv"></i>');
if(isset($_GET['a'])) # edition article
    $menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', '<i class="ico icon-plus-circled"></i>', false);
else # nouvel article
    $menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', '<i class="ico icon-plus-circled"></i>');
    $menus[] = plxUtils::formatMenu(L_MENU_MEDIAS, PLX_CORE.'admin/medias.php', L_MENU_MEDIAS_TITLE, false, false, '', '<i class="ico icon-picture"></i>');
if($_SESSION['profil'] <= PROFIL_MANAGER)
    $menus[] = plxUtils::formatMenu(L_MENU_STATICS, PLX_CORE.'admin/statiques.php', L_MENU_STATICS_TITLE, false, false, '', '<i class="ico icon-doc-text-inv"></i>');
if($_SESSION['profil'] <= PROFIL_MODERATOR) {
    $nbcoms = $plxAdmin->nbComments('offline');
    $coms_offline = $nbcoms>0 ? '<span class="badge" onclick="window.location=\''.PLX_CORE.'admin/comments.php?sel=offline&amp;page=1\';return false;">'.$plxAdmin->nbComments('offline').'</span>':'';
    $menus[] = plxUtils::formatMenu(L_MENU_COMMENTS, PLX_CORE.'admin/comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline, '<i class="ico icon-comment"></i>');
}
if($_SESSION['profil'] <= PROFIL_EDITOR)
    $menus[] = plxUtils::formatMenu(L_MENU_CATEGORIES, PLX_CORE.'admin/categories.php', L_MENU_CATEGORIES_TITLE, false, false, '', '<i class="ico icon-tag"></i>');
    $menus[] = plxUtils::formatMenu(L_MENU_PROFIL, PLX_CORE.'admin/profil.php', L_MENU_PROFIL_TITLE, false, false, '', '<i class="ico icon-user"></i>');
if($_SESSION['profil'] == PROFIL_ADMIN) {
    $menus[] = plxUtils::formatMenu(L_MENU_CONFIG, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', '<i class="ico icon-sliders"></i>', false);
    if (preg_match('/parametres/',basename($_SERVER['SCRIPT_NAME']))) {
        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_BASE, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'menu-config');
        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_VIEW, PLX_CORE.'admin/parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'menu-config');
        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_USERS, PLX_CORE.'admin/parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'menu-config');
        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_ADVANCED, PLX_CORE.'admin/parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'menu-config');
        $menus[] = plxUtils::formatMenu(L_THEMES, PLX_CORE.'admin/parametres_themes.php', L_THEMES_TITLE, 'menu-config');
        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_PLUGINS, PLX_CORE.'admin/parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'menu-config');
        $menus[] = plxUtils::formatMenu(L_MENU_CONFIG_INFOS, PLX_CORE.'admin/parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'menu-config');
    }
}

    # récuperation des menus admin pour les plugins
    foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugInstance) {
        if($plugInstance AND is_file(PLX_PLUGINS.$plugName.'/admin.php')) {
            if($plxAdmin->checkProfil($plugInstance->getAdminProfil(),false)) {
                if($plugInstance->adminMenu) {
                    $menu = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->adminMenu['title']), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->adminMenu['caption']), false, false, '', '<i class="ico icon-cog"></i>');
        			if($plugInstance->adminMenu['position']!='')
                        array_splice($menus, ($plugInstance->adminMenu['position']-1), 0, $menu);
                    else
                        $menus[] = $menu;
                } else {
        			 $menus[] = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->getInfo('title')), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->getInfo('title')), false, false, '', '<i class="ico icon-cog"></i>');
        		}
            }
        }
    }

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminTopMenus'));

$menuContent = implode('', $menus);
?>

<?php include 'masterTemplate.php'; ?>




<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $plxAdmin->aConf['default_lang'] ?>" lang="<?php echo $plxAdmin->aConf['default_lang'] ?>">
<head>
    <meta name="robots" content="noindex, nofollow" />
    <title><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?> <?php echo L_ADMIN ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/reset.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/base.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/style.css" media="screen" />
    <script type="text/javascript" src="<?php echo PLX_CORE ?>lib/functions.js"></script>
    <script type="text/javascript" src="<?php echo PLX_CORE ?>lib/visual.js"></script>
    <?php eval($plxAdmin->plxPlugins->callHook('AdminTopEndHead')) ?>
</head>

<body>

<div id="sidebar">

<ul>
	<li class="nav">
		<a href="<?php echo PLX_ROOT ?>" title="<?php echo L_BACK_TO_SITE_TITLE ?>"><?php echo L_BACK_TO_SITE;?></a>
		<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])) : ?>
		&nbsp;|&nbsp;&nbsp;<a href="<?php echo $plxAdmin->urlRewrite('?blog'); ?>" title="<?php echo L_BACK_TO_BLOG_TITLE ?>"><?php echo L_BACK_TO_BLOG;?></a>
		<?php endif; ?>
		<br />
		<a href="<?php echo PLX_CORE ?>admin/auth.php?d=1" title="<?php echo L_ADMIN_LOGOUT_TITLE ?>" id="logout"><?php echo L_ADMIN_LOGOUT ?></a>
	</li>
	<li class="user">
		<?php echo plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?>
	</li>
	<li class="profil">
		<?php
		if($_SESSION['profil']==PROFIL_ADMIN) echo L_PROFIL_ADMIN;
		elseif($_SESSION['profil']==PROFIL_MANAGER) echo L_PROFIL_MANAGER;
		elseif($_SESSION['profil']==PROFIL_MODERATOR) echo L_PROFIL_MODERATOR;
		elseif($_SESSION['profil']==PROFIL_EDITOR) echo L_PROFIL_EDITOR;
		else echo L_PROFIL_WRITER;
		?>
	</li>
	<?php
		$menus = array();
		$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);
		$nbartsmod = $plxAdmin->nbArticles('all', $userId, '_');
		$arts_mod = $nbartsmod>0 ? '&nbsp;<a class="cpt" href="'.PLX_CORE.'admin/index.php?sel=mod&amp;page=1" title="'.L_ALL_AWAITING_MODERATION.'">'.$nbartsmod.'</a>':'';
		$menus[] = plxUtils::formatMenu(L_MENU_ARTICLES, PLX_CORE.'admin/index.php?page=1', L_MENU_ARTICLES_TITLE, false, false,$arts_mod);

		if(isset($_GET['a'])) # edition article
			$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES, false, false, '', false);
		else # nouvel article
			$menus[] = plxUtils::formatMenu(L_MENU_NEW_ARTICLES_TITLE, PLX_CORE.'admin/article.php', L_MENU_NEW_ARTICLES);

		$menus[] = plxUtils::formatMenu(L_MENU_MEDIAS, PLX_CORE.'admin/medias.php', L_MENU_MEDIAS_TITLE);

		if($_SESSION['profil'] <= PROFIL_MANAGER)
			$menus[] = plxUtils::formatMenu(L_MENU_STATICS, PLX_CORE.'admin/statiques.php', L_MENU_STATICS_TITLE);

		if($_SESSION['profil'] <= PROFIL_MODERATOR) {
			$nbcoms = $plxAdmin->nbComments('offline');
			$coms_offline = $nbcoms>0 ? '&nbsp;<a class="cpt" href="'.PLX_CORE.'admin/comments.php?sel=offline&amp;page=1">'.$plxAdmin->nbComments('offline').'</a>':'';
			$menus[] = plxUtils::formatMenu(L_MENU_COMMENTS, PLX_CORE.'admin/comments.php?page=1', L_MENU_COMMENTS_TITLE, false, false, $coms_offline);
		}
		
		if($_SESSION['profil'] <= PROFIL_EDITOR)
			$menus[] = plxUtils::formatMenu(L_MENU_CATEGORIES, PLX_CORE.'admin/categories.php', L_MENU_CATEGORIES_TITLE);

		if($_SESSION['profil'] == PROFIL_ADMIN) {
			$menus[] = plxUtils::formatMenu(L_MENU_CONFIG, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_TITLE, false, false, '', false);
			if (preg_match('/parametres/',basename($_SERVER['SCRIPT_NAME']))) {
				$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_BASE, PLX_CORE.'admin/parametres_base.php', L_MENU_CONFIG_BASE_TITLE, 'sub');
				$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_VIEW, PLX_CORE.'admin/parametres_affichage.php', L_MENU_CONFIG_VIEW_TITLE, 'sub');
				$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_USERS, PLX_CORE.'admin/parametres_users.php', L_MENU_CONFIG_USERS_TITLE, 'sub');
				$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_ADVANCED, PLX_CORE.'admin/parametres_avances.php', L_MENU_CONFIG_ADVANCED_TITLE, 'sub');
				$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_PLUGINS, PLX_CORE.'admin/parametres_plugins.php', L_MENU_CONFIG_PLUGINS_TITLE, 'sub');
				$menus[] = plxUtils::formatMenu(L_MENU_CONFIG_INFOS, PLX_CORE.'admin/parametres_infos.php', L_MENU_CONFIG_INFOS_TITLE, 'sub');
			}
		}
		
		$menus[] = plxUtils::formatMenu(L_MENU_PROFIL, PLX_CORE.'admin/profil.php', L_MENU_PROFIL_TITLE);

		# récuperation des menus admin pour les plugins
		foreach($plxAdmin->plxPlugins->aPlugins as $plugName => $plugInstance) {
			if($plugInstance AND is_file(PLX_PLUGINS.$plugName.'/admin.php')) {
				if($plxAdmin->checkProfil($plugInstance->getAdminProfil(),false)) {
					if($plugInstance->adminMenu) {
						$menu = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->adminMenu['title']), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->adminMenu['caption']));
						array_splice($menus, ($plugInstance->adminMenu['position']-1), 0, $menu);
						$menus[]=$menu;
					} else {
						$menus[] = plxUtils::formatMenu(plxUtils::strCheck($plugInstance->getInfo('title')), PLX_CORE.'admin/plugin.php?p='.$plugName, plxUtils::strCheck($plugInstance->getInfo('title')));
					}
				}
			}
		}

		# Hook Plugins
		eval($plxAdmin->plxPlugins->callHook('AdminTopMenus'));
		echo implode('', $menus);
	?>
	<li class="pluxml">
            <a title="PluXml" href="http://www.pluxml.org">Pluxml <?php echo $plxAdmin->aConf['version'] ?></a>
	</li>
    </ul>

</div><!-- sidebar -->

<div id="content">

    <h1 id="sitename"><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?></h1>
    <?php
	if(is_file(PLX_ROOT.'install.php')) echo L_WARNING_INSTALLATION_FILE;
            plxMsg::Display();
    ?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminTopBottom')) ?>

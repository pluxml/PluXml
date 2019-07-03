<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="robots" content="noindex, nofollow" />
    <meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=<?= $charset ?>" />
    <title><?= $titre ?></title>
    <link rel="stylesheet" type="text/css" href="theme/css/knacss.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="theme/css/theme.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="theme/fonts/fontello.css" media="screen" />
    <link rel="icon" href="theme/images/favicon.png" />
    <?= $custom_admincss ?>
    <?= $plugin_admincss ?>

    <script src="theme/js/functions.js"></script>
    <script src="theme/js/visual.js"></script>
    <script src="theme/js/mediasManager.js"></script>
    <script defer src="theme/js/multifiles.js"></script>

    <?= $hookAdminTopEndHead ?>
</head>

<body id="<?= $scriptName ?>">

	<div class="flex-container--column page">

	<header id="header" role="banner" class="flex-container header">
		<div class="pls prl title">
			<a href="<?= $adminUrl ?>"><strong><?= $siteTitle ?></strong></a>
		</div>
		<nav id="navigation" role="navigation" class="item-fluid">
        	<ul class="pan man">
        		<?= $menuContent ?>
        	</ul>
		</nav>
    	<div class="fr">
           	<ul class="pan man">
           		<li class="inbl pas">
            		<a class="" href="<?= $siteUrl ?>" title="<?= $siteTitle ?>"><small><?= $backToSite ?></small></a>
            	</li>
				<?= $blogLink ?>
        		<li class="inbl pas">
        			<a class="logout" href="<?= $logOutUrl ?>" title="<?= $logOutTitle ?>"><small><?= $logOut ?></small></a>
        		</li>
        		<li class="inbl pas">
        			<span class="badge"><img class="profil" src="<?= $profilPicture ?>"></span>
				</li>
        	</ul>
        </div>
	</header>

	<div>
		<h1 class="h3-like pas pll bk-white"><?= $adminTitle?></h1>
	</div>

   	<main id="main" role="main" class="item-fluid pts pbs pll prl">
		<?= $adminMessage ?>
    	<?= $hookAdminTopBottom ?>
    	<?= $mainContent ?>
	</main>

	<footer class="footer">
    	<ul class="pan man">
        	<li class="inbl pas">
            	<small><a class="version" title="PluXml" href="http://www.pluxml.org">PluXml <?= $pluxmlVersion ?></a></small>
    			<?= $pluxmlMaj ?>
    		</li>
    	</ul>
    	
    	<?= $hookAdminFootEndBody ?>
    
        <script src="<?php echo PLX_CORE ?>theme/js/drag-and-drop.js"></script>
        <script>
        	setMsg();
        	mediasManager.construct({
        		windowName : "<?php echo L_MEDIAS_TITLE ?>",
        		racine:	"<?php echo plxUtils::getRacine() ?>",
        		urlManager: "core/admin/medias.php"
        	});
        </script>
    </footer>
    
    </div>

</body>
</html>
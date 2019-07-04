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
   		<div class="pls prl title item-fluid ">
   			<a href="<?= $adminUrl ?>"><strong><?= $siteTitle ?></strong></a>
   		</div>
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
           			<span class="badge"><a href=<?= $profileUrl ?>><img class="profil" src="<?= $profilPicture ?>"></a></span>
   				</li>
           	</ul>
		</div>
   	</header>

	<div class="grid-10 item-fluid">
    	<aside class="col-1-small-all aside">
    		<nav id="navigation" role="navigation">
            	<ul class="pan man">
            		<?= $menuContent ?>
            	</ul>
    		</nav>
    		<ul class="pan man">
           		<li class="inbl pas">
               		<small><a class="version" title="PluXml" href="http://www.pluxml.org">PluXml <?= $pluxmlVersion ?></a></small>
       				<?= $pluxmlMaj ?>
       			</li>
       		</ul>
    	</aside>
		<div class="flex-container--column col-9-small-all">    
        	<div class="pas pll bk-white">
        		<h1 class="man h3-like"><?= $adminTitle?></h1>
        		<?= $adminSubMenu ?>
        	</div>
           	<main id="main" role="main" class="item-fluid pts pbs pll prl mbm">
        		<?= $adminMessage ?>
            	<?= $hookAdminTopBottom ?>
            	<?= $mainContent ?>
        	</main>
		</div>
    </div>
    
   	<footer class="footer">
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
    	<script>
        	function asideDropdown() {
        	  document.getElementById("asideDropdown").classList.toggle("show");
        	}

    	</script>
	</footer>
	
</div>
</body>
</html>
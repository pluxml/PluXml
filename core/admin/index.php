<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

include __DIR__ .'/prepend.php';

use Pluxml\PlxMsg;

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

# On inclut le header
include __DIR__ .'/tags/top.php';

// Dashboard statistique calculation
$nbarts = $plxAdmin->nbArticles('published');
$nbcomments = $plxAdmin->nbComments();
$nbpages = $plxAdmin->nbPages(true);
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<div class="adminheader">
	<h2 class="h3-like">Tableau de bord (lang)</h2>
</div>

<div class="admin">
	<?php
	if(is_file(PLX_ROOT.'install.php'))
		echo '<p class="alert red">'.L_WARNING_INSTALLATION_FILE.'</p>'."\n";
	PlxMsg::Display();
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
	?>

	<div class="grid-3-small-1 has-gutter-xl mtm">
		<div class="panel">
			<div class="panel-header">
				<strong>Statistiques (L)</strong>
			</div>
			<div class="panel-content">
				<p><i class="icon-pencil"></i><a href=""><?= $nbarts.'&nbsp;'.L_MENU_ARTICLES ?></a></p>
				<p><i class="icon-doc-text-inv"></i><a href=""><?= $nbcomments.'&nbsp;'.L_MENU_STATICS ?></a></p>
				<p><i class="icon-comment"></i><a href=""><?= $nbpages.'&nbsp;'.L_MENU_COMMENTS ?></a></p>
			</div>
		</div>
		<div class="panel">
			<div class="panel-header">
				<strong><?= L_ALL_DRAFTS ?></strong>
			</div>
			<div class="panel-content">
				
			</div>
		</div>
		<div class="panel">
			<div class="panel-header">
				<strong><?= L_ALL_AWAITING_MODERATION ?></strong>
			</div>
			<div class="panel-content">
				
			</div>
		</div>

	</div>

	<div class="grid-2-small-1 has-gutter-xl mtl">
		<div class="panel panel-content">
			commentaire en modération ou dernier comm
		</div>
		<div class="panel panel-content">
			flux rss PluXml
		</div>
	</div>
</div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include __DIR__ .'/tags/foot.php';
?>

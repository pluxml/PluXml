<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
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
	
	<div class="grid-3-small-1 has-gutter-xl mtl">
		<div class="grid-3-small-1">
			<div class="panel">
				<span class="count"><?= $nbarts ?></span><br>
				<small>published articles</small>
			</div>
			<div class="panel-grey">
				<span class="count"><?= $nbarts ?></span><br>
				<small>published articles</small>
			</div>
			<div class="panel-grey">
				<span class="count"><?= $nbarts ?></span><br>
				<small>published articles</small>
			</div>
		</div>
		<div class="grid-3-small-1">
			<div class="panel">
				<span class="count"><?= $nbcomments ?></span><br>
				<small>published comments</small>
			</div>
			<div class="panel-grey">
				<span class="count"><?= $nbcomments ?></span><br>
				<small>published comments</small>
			</div>
			<div class="panel-grey">
				<span class="count"><?= $nbcomments ?></span><br>
				<small>published comments</small>
			</div>
		</div>
		<div class="grid-3-small-1">
			<div class="panel">
				<span class="count"><?= $nbpages ?></span><br>
				<small>published pages</small>
			</div>
			<div class="panel-grey">
				<span class="count"><?= $nbpages ?></span><br>
				<small>published pages</small>
			</div>
			<div class="panel-grey">
				<span class="count"><?= $nbpages ?></span><br>
				<small>published pages</small>
			</div>
		</div>
	</div>
	
	<div class="grid-3-small-1 has-gutter-xl mtl">
		<div class="panel">
			brouillons
		</div>
		<div class="panel">
			articles moderation
		</div>
		<div class="panel">
			commentaire en modération
			ou dernier comm
		</div>
	</div>
	
	<div class="panel mtl">
		flux rss PluXml
	</div>
</div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include __DIR__ .'/tags/foot.php';
?>

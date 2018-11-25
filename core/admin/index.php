<?php

/**
 * Page d'accueil de l'administration
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

include __DIR__ .'/prepend.php';

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

# On inclut le header
include __DIR__ .'/top.php';
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<div class="inline-form action-bar">
	<i class="ico icon-th-large"></i>
	<h2><?php echo L_DASHBOARD_TITLE ?></h2>
</div>

<?php echo $plxAdmin->checkMaj(); ?>

<div class="grid">
	<div class="col sml-6">

        <h3>Mes brouillons</h3>
        <?php
		# Récupération des articles
        $plxAdmin->prechauffage($plxAdmin->motif('draft','all'));
        $arts = $plxAdmin->getArticles('all');
        
        # Liste des articles
		if($arts) {
			while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
			    $idArt = $plxAdmin->plxRecord_arts->f('numero');
			    $author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
				echo plxDate::formatDate($plxAdmin->plxRecord_arts->f('date'));
				echo '<a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')).'</a>';
				echo plxUtils::strCheck($author);
				echo '<a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.L_ARTICLE_EDIT.'</a>';
			}
		} else { # Aucun article dans la liste
			echo L_NO_ARTICLE;
		}
		?>

	</div>
	<div class="col sml-6">

		<h3>Actualités</h3>
		<p>Blog</p>
		<p>Forum</p>
	</div>
</div>

<!-- Affichages des commentaires en modération -->
<?php 
if($_SESSION['profil'] <= PROFIL_MODERATOR) {
    echo'
        <div class="grid">
            <div class="col sml-12">
                <h3>Articles en modération</h3>';
		
	# Récupération des articles
    $plxAdmin->prechauffage($plxAdmin->motif('mod','all', $userId));
    $arts = $plxAdmin->getArticles('all');
        
    # Liste des articles
	if($arts) {
	   while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
	       $idArt = $plxAdmin->plxRecord_arts->f('numero');
		   $author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
		   echo plxDate::formatDate($plxAdmin->plxRecord_arts->f('date'));
		   echo '<a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')).'</a>';
		   echo plxUtils::strCheck($author);
		   echo '<a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.L_ARTICLE_EDIT.'</a>';
		}
	} else { # Aucun article dans la liste
		echo L_NO_ARTICLE;
	}
	echo'
        	</div>
        </div>';
}
?>

<!--  Affichages des articles en modération -->
<?php 
if($_SESSION['profil'] <= PROFIL_MODERATOR) {
    echo'
        <div class="grid">
        	<div class="col sml-12">
        
                <h3>Commenaires en modération</h3>
        
        
        	</div>
        </div>';
}
?>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
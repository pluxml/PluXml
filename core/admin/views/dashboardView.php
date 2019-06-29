<?php ob_start(); ?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<div class="admin-title">
	<h2><?php echo L_DASHBOARD_TITLE ?></h2>
</div>

<div class="grid">
	<div class="col sml-12 med-6">
		<div class="grid panel">
			<div class="col sml-12 panel-content panel-title">
           		<h3 class="no-margin">Mes brouillons</h3>
            </div>
            <div class="col sml-12 panel-content">
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
		</div>
	</div>
	<div class="col sml-12 med-6">
		<div class="grid panel">
			<div class="col sml-12 panel-content panel-title">
           		<h3 class="no-margin">Actualités</h3>
            </div>
    		<div class="col sml-12 panel-content">
           		<p>Blog</p>
           		<p>Forum</p>
            </div>
    	</div>
	</div>
</div>

<!-- Affichages des commentaires en modération -->
<?php if($_SESSION['profil'] <= PROFIL_MODERATOR): ?>
<div class="grid">
	<div class="col sml-12">
    	<div class="grid panel">
			<div class="col sml-12 panel-content panel-title">
				<h3 class="no-margin">Articles en modération</h3>
			</div>
			<div class="col sml-12 panel-content">
            	<?php 	
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
            	?>
        	</div>
    	</div>
	</div>
</div>
<?php endif; ?>

<!--  Affichages des articles en modération -->
<?php if($_SESSION['profil'] <= PROFIL_MODERATOR): ?>
        <div class="grid">
            <div class="col sml-12">
                <div class="grid panel">
			        <div class="col sml-12 panel-content panel-title">
			            <h3 class="no-margin">Commenaires en modération</h3>
                    </div>
                    <div class="col sml-12 panel-content">
                        <p>Liste des commantaires</p>
                   </div>
        	   </div>
            </div>
        </div>
<?php endif; ?>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
?>

<?php $mainContent = ob_get_clean(); ?>
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

<?php
    # Permettre l'affichage en fonction du profil utilisateur
    $hideClass='sml-hide';
	if($_SESSION['profil']<=PROFIL_MODERATOR) {
		$hideClass='sml-show';
	}
?>

<!--  Affichages des articles et commentaire en modération -->
<div class="grid <?php echo $hideClass;?>">
	<div class="col sml-6">

        <h3>Articles en modération</h3>
		<?php
		# Récupération des articles
        $plxAdmin->prechauffage($plxAdmin->motif('mod','all', $userId));
        $arts = $plxAdmin->getArticles('all');
        
        # Liste des articles
		if($arts) { # On a des articles
			# Initialisation de l'ordre
			$num=0;
			$datetime = date('YmdHi');
			while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
				$author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
				$publi =  (boolean)!($plxAdmin->plxRecord_arts->f('date') > $datetime);
				# Catégories : liste des libellés de toutes les categories
				$draft='';
				$libCats='';
				$aCats = array();
				$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
				if(sizeof($catIds)>0) {
					foreach($catIds as $catId) {
						$selected = ($catId==$_SESSION['sel_cat'] ? ' selected="selected"' : '');
						if($catId=='draft') $draft = ' - <strong>'.L_CATEGORY_DRAFT.'</strong>';
						elseif($catId=='home') $aCats['home'] = '<option value="home"'.$selected.'>'.L_CATEGORY_HOME.'</option>';
						elseif($catId=='000') $aCats['000'] = '<option value="000"'.$selected.'>'.L_UNCLASSIFIED.'</option>';
						elseif(isset($plxAdmin->aCats[$catId])) $aCats[$catId] = '<option value="'.$catId.'"'.$selected.'>'.plxUtils::strCheck($plxAdmin->aCats[$catId]['name']).'</option>';
					}

				}
				# en attente de validation ?
				$idArt = $plxAdmin->plxRecord_arts->f('numero');
				$awaiting = $idArt[0]=='_' ? ' - <strong>'.L_AWAITING.'</strong>' : '';
				# Commentaires
				$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$idArt.'.(.*).xml$/','all');
				$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$idArt.'.(.*).xml$/','all');
				# On affiche la ligne
				echo '<tr>';
				echo '<td><input type="checkbox" name="idArt[]" value="'.$idArt.'" /></td>';
				echo '<td>'.$idArt.'</td>';
				echo '<td>'.plxDate::formatDate($plxAdmin->plxRecord_arts->f('date')).'&nbsp;</td>';
				echo '<td class="wrap"><a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')).'</a>'.$draft.$awaiting.'&nbsp;</td>';
				echo '<td>';
				if(sizeof($aCats)>1) {
					echo '<select name="sel_cat2" class="ddcat" onchange="this.form.sel_cat.value=this.value;this.form.submit()">';
					echo implode('', $aCats);
					echo '</select>';
				}
				else echo strip_tags(implode('', $aCats));
				echo '&nbsp;</td>';
				echo '<td><a title="'.L_NEW_COMMENTS_TITLE.'" href="comments.php?sel=offline&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsToValidate.'</a> / <a title="'.L_VALIDATED_COMMENTS_TITLE.'" href="comments.php?sel=online&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsValidated.'</a>&nbsp;</td>';
				echo '<td>'.plxUtils::strCheck($author).'&nbsp;</td>';
				echo '<td>';
				echo '<a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.L_ARTICLE_EDIT.'</a>';
				if($publi AND $draft=='') # Si l'article est publié
					echo ' <a href="'.$plxAdmin->urlRewrite('?article'.intval($idArt).'/'.$plxAdmin->plxRecord_arts->f('url')).'" title="'.L_ARTICLE_VIEW_TITLE.'">'.L_VIEW.'</a>';
				echo "&nbsp;</td>";
				echo "</tr>";
			}
		} else { # Aucun article
			echo L_NO_ARTICLE;
		}
		?>


	</div>
	<div class="col sml-6">

		<h3>Commentaires en modération</h3>



	</div>
</div>

<div class="grid">
	<div class="col sml-6">

        <h3>Brouillons</h3>

	</div>
	<div class="col sml-6">

		<h3>Titre h3</h3>

	</div>
</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
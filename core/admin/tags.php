<?php
/*
 * Placer de préférence ce script dans le dossier core/admin/ et
 * l'ouvrir dans le navigateur pour restaurer le fichier tags.xml de PluXml.
 *
 * Par sécurité, la connexion au back-office sera demandée.
 * */

include 'prepend.php';

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER);

/*
 * Les articles sans tag ou à modérer sont ignorés.
 * Avant la sauvegarde, les articles seront triés selon leurs identifiants.
 * */
foreach($plxAdmin->plxGlob_arts->aFiles as $artId=>$filename) {
	if($filename[0] != '_') {
		$art = $plxAdmin->parseArticle(PLX_ROOT . $plxAdmin->aConf['racine_articles'] . $filename);
		if(!empty($art['tags'])) {
			$plxAdmin->aTags[$artId] = array(
				'tags'		=> $art['tags'],
				'date'		=> $art['date'],
				'active'	=> preg_match('#\bdraft\b#', $art['categorie']) ? 0 : 1,
			);
		}
		unset($art);
	}
}

if(!empty($plxAdmin->aTags)) {
	ksort($plxAdmin->aTags);
	$plxAdmin->editTags();
}

# On inclut le header
include 'top.php';
?>
<div class="inline-form action-bar">
	<h2><?= L_TAGS_LIST_FILE ?></h2>
</div>
	<pre><?= str_replace('<', '&lt;', file_get_contents(path('XMLFILE_TAGS'))); ?></pre>
<?php
# On inclut le footer
include 'foot.php';


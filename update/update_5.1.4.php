<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.4
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_4 extends plxUpdate{
	const NEW_TAG = <<< EOT
	<title_htmltag></title_htmltag>
</document>"
EOT;

	# mise à jour fichier parametres.xml
	public function step1() {
?>
			<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php
		# mise à jour du fichier des parametres
		return $this->updateParameters(array(
			'mod_art' => 0,
			'racine_themes' => 'themes/',
			'racine_plugins' => 'plugins/',
		));
	}

	# Migration des articles: ajout nouveau champ title_htmltag
	public function step2() {
?>
			<li><?= L_UPDATE_ARTICLES_CONVERSION ?></li>
<?php
		$articles_folder = PLX_ROOT . $this->plxAdmin->aConf['racine_articles'];
		$plxGlob_arts = plxGlob::getInstance($articles_folder);
        if($files = $plxGlob_arts->query('/(.*).xml$/','art')) {
			foreach($files as $f) {
				$filename = $articles_folder . $f;
				if(is_readable($filename)) {
					$data = file_get_contents($filename);
					if(!preg_match('#</title_htmltag>#', $data)) {
						$data = preg_replace('#</document>$#', self::NEW_TAG, $data);
					}
					if(!plxUtils::write($data, $filename)) {
						echo '<p class="error">'.L_UPDATE_ERR_FILE_PROCESSING.' : '.$filename.'</p>';
						return false;
					}
				}
			}
		}
		return true;
	}

	# Suppression des fichiers obsoletes
	public function step3() {
		@unlink(PLX_ROOT.$this->plxAdmin->aConf['racine_articles'].'index.html');
		@unlink(PLX_ROOT.$this->plxAdmin->aConf['racine_commentaires'].'index.html');
		@unlink(PLX_ROOT.$this->plxAdmin->aConf['racine_statiques'].'index.html');
		@unlink(PLX_ROOT.'blog.php');
		return true;
	}

}


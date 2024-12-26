<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.4
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_4 extends plxUpdate {
	const NEW_PARAMS = array(
		'mod_art'			=> 0,
		'racine_themes'		=> 'themes/',
		'racine_plugins'	=> 'plugins/',
	);

	/*
	 * mise à jour fichier parametres.xml
	 * */
	public function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		# mise à jour du fichier des parametres
		return $this->updateParameters(self::NEW_PARAMS);
	}

	/*
	 * Migration des articles: ajout nouveau champ title_htmltag
	 * */
	public function step2() {
?>
		<li><?= L_UPDATE_ARTICLES_CONVERSION ?></li>
<?php
		$plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles']);
        if($files = $plxGlob_arts->query('/(.*).xml$/','art')) {
			foreach($files as $filename){
				if(is_readable($filename)) {
					$data = file_get_contents(PLX_ROOT.$this->aConf['racine_articles'].$filename);
					$replace = <<< EOT
	<title_htmltag></title_htmltag>
</document>
EOT;
					if(!preg_match('#\]\]</title_htmltag>#', $data)) {
						$data = preg_replace("#</document>$#", $replace, $data);
					}
					if(!plxUtils::write($data, PLX_ROOT.$this->aConf['racine_articles'].$filename)) {
						echo '<p class="error">'.L_UPDATE_ERR_FILE_PROCESSING.' : '.$filename.'</p>';
						return false;
					}
				}
			}
		}
		return true;
	}

	/*
	 * Suppression des fichiers obsoletes
	 * */
	public function step3() {
		foreach(array('articles', 'commentaires', 'statiques') as $k) {
			$filename = PLX_ROOT . $this->aConf['racine_' . $k] . 'index.html';
			if(is_writable($filename)) {
				unlink($filename);
			} elseif(file_exists($filename)) {
?>
			<p><?php printf(L_DELETE_FILE_ERROR, $this->aConf['racine_' . $k] . 'index.html') ?></p>
<?php
			}
		}
		$filename = PLX_ROOT . 'blog.php';
		if(is_writable($filename)) {
			unlink($filename);
		} elseif(file_exists($filename)) {
?>
			<p><?php printf(L_DELETE_FILE_ERROR, 'blog.php') ?></p>
<?php
		}
		return true;
	}

}

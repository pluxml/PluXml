<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.4
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_4 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# nouveaux parametres
		$new_parameters = array(
			'mod_art' => 0,
			'racine_themes' => 'themes/',
			'racine_plugins' => 'plugins/',
		);
		# mise à jour du fichier des parametres
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

	# Migration des articles: ajout nouveau champ title_htmltag
	public function step2() {
		echo L_UPDATE_ARTICLES_CONVERSION."<br />";
		$plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->plxAdmin->aConf['racine_articles']);
        if($files = $plxGlob_arts->query('/(.*).xml$/','art')) {
			foreach($files as $filename){
				if(is_readable($filename)) {
					$data = file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['racine_articles'].$filename);
					if(!preg_match('/\]\]<\/title_htmltag>/', $data)) {
						$data = preg_replace("/<\/document>$/", "\t<title_htmltag>\n\t\t<![CDATA[]]>\n\t</title_htmltag>\n</document>", $data);
					}
					if(!plxUtils::write($data, PLX_ROOT.$this->plxAdmin->aConf['racine_articles'].$filename)) {
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
?>
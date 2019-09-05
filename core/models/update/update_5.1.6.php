<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.6
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_6 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# nouveaux parametres
		$new_parameters = array(
			'display_empty_cat' => 0,
			'timezone' => date_default_timezone_get(),
		);
		# on supprime les parametres obsoletes
		unset($this->plxAdmin->aConf['delta']);
		# mise à jour du fichier des parametres
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

	# mise à jour fichier .htaccess
	public function step2() {

		if(file_exists(PLX_ROOT.'.htaccess')) {
			echo L_UPDATE_UPDATE_HTACCESS_FILE."<br />";
			# lecture du fichier .htaccess
			$htaccess = file_get_contents(PLX_ROOT.'.htaccess');
			$old = 'RewriteRule ^([^feed\/].*)$ index.php?$1 [L]';
			$new = 'RewriteRule ^(?!feed)(.*)$ index.php?$1 [L]';
			$htaccess = str_replace($old, $new, $htaccess);
			if(!plxUtils::write($htaccess,PLX_ROOT.'.htaccess')) {
				echo '<p class="error">'.L_UPDATE_ERR_UPDATE_HTACCESS_FILE.'</p>';
				return false;
			}
		}
		return true; # pas d'erreurs

	}
	# Mise à jour des pages statiques: ajout nouveau champ title_htmltag
	public function step3() {
		echo L_UPDATE_FILE." (".$this->plxAdmin->aConf['statiques'].")<br />";
		$data = file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['statiques']);
		$tag = 'statique';
		if(preg_match_all('{<'.$tag.'[^>]*>(.*?)</'.$tag.'>}', $data, $matches, PREG_PATTERN_ORDER)) {
			foreach($matches[0] as $match) {
				if(!preg_match('/<title_htmltag>/', $match)) {
					$str = str_replace('</'.$tag.'>', '<title_htmltag><![CDATA[]]></title_htmltag></'.$tag.'>', $match);
					$data = str_replace($match, $str, $data);
				}
			}
			if(!plxUtils::write($data, PLX_ROOT.$this->plxAdmin->aConf['statiques'])) {
				echo '<p class="error">'.L_UPDATE_ERR_FILE.'</p>';
				return false;
			}
		}
		return true;
	}
	# Mise à jour des categories: ajout nouveau champ title_htmltag
	public function step4() {
		echo L_UPDATE_FILE." (".$this->plxAdmin->aConf['categories'].")<br />";
		$data = file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['categories']);
		$tag = 'categorie';
		if(preg_match_all('{<'.$tag.'[^>]*>(.*?)</'.$tag.'>}', $data, $matches, PREG_PATTERN_ORDER)) {
			foreach($matches[0] as $match) {
				if(!preg_match('/<title_htmltag>/', $match)) {
					$str = str_replace('</'.$tag.'>', '<title_htmltag><![CDATA[]]></title_htmltag></'.$tag.'>', $match);
					$data = str_replace($match, $str, $data);
				}
			}
			if(!plxUtils::write($data, PLX_ROOT.$this->plxAdmin->aConf['categories'])) {
				echo '<p class="error">'.L_UPDATE_ERR_FILE.'</p>';
				return false;
			}
		}
		return true;
	}
}
?>
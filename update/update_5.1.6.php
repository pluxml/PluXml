<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.6
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_6 extends plxUpdate {

	/*
	 * mise à jour fichier parametres.xml
	 * */
	public function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em>display_empty_cat, timezone</em></li>
<?php
		# on supprime les parametres obsoletes
		unset($this->aConf['delta']);
		# mise à jour du fichier des parametres
		return $this->updateParameters(array(
			'display_empty_cat' => 0,
			'timezone' => date_default_timezone_get(),
		));
	}

	/*
	 * mise à jour fichier .htaccess
	 * */
	public function step2() {

		if(file_exists(PLX_ROOT.'.htaccess')) {
?>
		<li><?= L_UPDATE_UPDATE_HTACCESS_FILE ?></li>
<?php
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

	/*
	 * Mise à jour des pages statiques et des catégories : ajout nouveau champ title_htmltag
	 * */
	public function step3() {
		foreach(array('statique', 'categorie') as $tag) {
			$filename = PLX_ROOT.$this->aConf[$tag . 's'];
?>
		<li><?= L_UPDATE_FILE ?> <?= basename($filename) ?></li>
<?php
			$data = file_get_contents($filename);
			if(preg_match_all('{<'.$tag.'[^>]*>(.*?)</'.$tag.'>}', $data, $matches, PREG_PATTERN_ORDER)) {
				foreach($matches[0] as $match) {
					if(!preg_match('/<title_htmltag>/', $match)) {
						$str = str_replace('</'.$tag.'>', '<title_htmltag><![CDATA[]]></title_htmltag></'.$tag.'>', $match);
						$data = str_replace($match, $str, $data);
					}
				}
				if(!plxUtils::write($data, $filename)) {
?>
		<p class="error">'. L_UPDATE_ERR_FILE</p>
<?php
					return false;
				}
			}
		}

		return true;
	}
}

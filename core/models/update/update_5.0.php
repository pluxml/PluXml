<?php
/**
 * Classe de mise a jour pour PluXml version 5.0
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_0 extends plxUpdate{

	/* Création des nouveaux paramètres dans le fichier parametres.xml */
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		$new_parameters = array(
			'urlrewriting' 	=> 0,
			'gzip'		 	=> 0,
			'feed_chapo' 	=> 0,
			'feed_footer' 	=> '',
			'users' 		=> 'data/configuration/users.xml',
			'tags' 			=> 'data/configuration/tags.xml',
			'editor'		=> 'plxtoolbar',
			'homestatic'	=> ''
		);
		$this->updateParameters($new_parameters);
		$this->plxAdmin->getConfiguration(path('XMLFILE_PARAMETERS')); # on recharge le fichier de configuration
		return true; # pas d'erreurs
	}

	/* Création du fichier data/configuration/tags.xml */
	public function step2() {
		echo L_UPDATE_CREATE_TAGS_FILE."<br />";
		$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>';
		$xml .= '<document>'."\n";
		$xml .= '</document>';
		if(!plxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['tags'])) {
			echo '<p class="error">'.L_UPDATE_ERR_CREATE_TAGS_FILE.'</p>';
			return false;
		}
		return true;
	}

	/* Création du fichier themes/style/tags.php */
	public function step3() {
		$srcfile = PLX_ROOT.'themes/'.$this->plxAdmin->aConf['style'].'/home.php';
		$dstfile = PLX_ROOT.'themes/'.$this->plxAdmin->aConf['style'].'/tags.php';
		if(!is_file($dstfile)) {
			echo L_UPDATE_CREATE_THEME_FILE.": themes/".$this->plxAdmin->aConf['style']."/tags.php<br />";
			if(!copy($srcfile, $dstfile)) {
				echo '<p class="error">'.L_UPDATE_ERR_CREATE_THEME_FILE.' themes/style/tags.php</p>';
				return false;
			}
		}
		return true;
	}

	/* Création du fichier themes/style/archives.php */
	public function step4() {
		$srcfile = PLX_ROOT.'themes/'.$this->plxAdmin->aConf['style'].'/home.php';
		$dstfile = PLX_ROOT.'themes/'.$this->plxAdmin->aConf['style'].'/archives.php';
		if(!is_file($dstfile)) {
			echo L_UPDATE_CREATE_THEME_FILE.": themes/".$this->plxAdmin->aConf['style']."/archives.php<br />";
			if(!copy($srcfile, $dstfile)) {
				echo '<p class="error">'.L_UPDATE_ERR_CREATE_THEME_FILE.' themes/style/archives.php</p>';
				return false;
			}
		}
		return true;
	}

	/* Migration des articles: formatage xml + renommage des fichiers */
	public function step5() {
		echo L_UPDATE_ARTICLES_CONVERSION."<br />";
		$plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->plxAdmin->aConf['racine_articles']);
        if($files = $plxGlob_arts->query('/^[0-9]{4}.([0-9]{3}|home|draft).[0-9]{12}.[a-z0-9-]+.xml$/','art')) {
			foreach($files as $id => $filename){
				$art = $this->parseArticle(PLX_ROOT.$this->plxAdmin->aConf['racine_articles'].$filename);
				if(!$this->plxAdmin->editArticle($art, $art['numero'])) {
					echo '<p class="error">'.L_UPDATE_ERR_FILE_PROCESSING.' : '.$filename.'</p>';
					return false;
				}
			}
		}
		return true;
	}

	/* Migration du fichier des pages statiques */
	public function step6() {
		echo L_UPDATE_STATICS_MIGRATION."<br />";
		if($statics = $this->getStatiques(PLX_ROOT.$this->plxAdmin->aConf['statiques'])) {
			# On génère le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($statics as $static_id => $static) {
				$xml .= "\t<statique number=\"".$static_id."\" active=\"".$static['active']."\" menu=\"".$static['menu']."\" url=\"".$static['url']."\" template=\"static.php\"><group><![CDATA[]]></group><name><![CDATA[".$static['name']."]]></name></statique>\n";
			}
			$xml .= "</document>";
			if(!plxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['statiques'])) {
				echo '<p class="error">'.L_UPDATE_ERR_STATICS_MIGRATION.' (data/configuration/statiques.xml)</p>';
				return false;
			}
		}
		return true;
	}

	/* Création du fichier des utilisateurs */
	public function step7() {
		echo L_UPDATE_CREATE_USERS_FILE."<br />";
		if($users = $this->getUsers(PLX_ROOT.$this->plxAdmin->aConf['passwords'])) {
			$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
			$xml .= '<document>'."\n";
			$num_user = 1;
			foreach($users as $login => $password) {
				$xml .= "\t".'<user number="'.str_pad($num_user++, 3, "0", STR_PAD_LEFT).'" active="1" profil="0" delete="0">'."\n";
				$xml .= "\t\t".'<login><![CDATA['.$login.']]></login>'."\n";
				$xml .= "\t\t".'<name><![CDATA['.$login.']]></name>'."\n";
				$xml .= "\t\t".'<infos><![CDATA[]]></infos>'."\n";
				$xml .= "\t\t".'<password><![CDATA['.$password.']]></password>'."\n";
				$xml .= "\t</user>\n";
			}
			$xml .= '</document>';
			if(!plxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['users'])) {
				echo '<p class="error">'.L_UPDATE_ERR_CREATE_USERS_FILE.' (data/configuration/users.xml)</p>';
				return false;
			}
		}
		else {
			echo '<p class="error">'.L_UPDATE_ERR_NO_USERS.' data/configuration/passwords.xml</p>';
			return false;
		}
		return true;
	}

	/* Suppression des données obsolètes */
	public function step8() {
		# suppression du fichier data/configuration/passwords.xml
		unlink(PLX_ROOT.$this->plxAdmin->aConf['passwords']);
		# suppression du fichier d'installation
		unlink(PLX_ROOT.'install.php');
		# suppression des clés obsolètes dans le fichier data/configuration/parametres.xml
		unset($this->plxAdmin->aConf['password']);
		$this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $this->plxAdmin->aConf);
		return true;
	}

	# Création du fichier .htaccess
	public function step9() {
		if(!is_file(PLX_ROOT.'.htaccess')) {
			echo L_UPDATE_CREATE_HTACCESS_FILE."<br />";
			$txt = '<Files "version">
    Order allow,deny
    Deny from all
</Files>';
			if(!plxUtils::write($txt,PLX_ROOT.'.htaccess')) {
				echo '<p class="error">'.L_UPDATE_ERR_CREATE_HTACCESS_FILE.'</p>';
				return false;
			}
		}
		return true;
	}

	/*=====*/

	private	function artInfoFromFilename($filename) {

		# On effectue notre capture d'informations
		preg_match('/([0-9]{4}).([0-9]{3}|home|draft).([0-9]{12}).([a-z0-9-]+).xml$/',$filename,$capture);
		return array('artId'=>$capture[1],'catId'=>$capture[2],'artDate'=>$capture[3],'artUrl'=>$capture[4]);
	}

	private function parseArticle($filename) {
		$art = array();
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# Recuperation des valeurs de nos champs XML
		$art['title'] = trim($values[ $iTags['title'][0] ]['value']);
		$art['author'] = '001';
		$art['allow_com'] = trim($values[ $iTags['allow_com'][0] ]['value']);
		$art['chapo'] = (isset($values[ $iTags['chapo'][0] ]['value']))?trim($values[ $iTags['chapo'][0] ]['value']):'';
		$art['content'] = (isset($values[ $iTags['content'][0] ]['value']))?trim($values[ $iTags['content'][0] ]['value']):'';
		# Informations obtenues en analysant le nom du fichier
		$art['filename'] = $filename;
		$tmp = $this->artInfoFromFilename($filename);
		$art['numero'] = $tmp['artId'];
		$art['artId'] = $art['numero'];
		$art['catId'] = array($tmp['catId']);
		$art['url'] = $tmp['artUrl'];
		preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{4})/',$tmp['artDate'],$capture);
		$art['date'] = array ('year' => $capture[1],'month' => $capture[2],'day' => $capture[3],'time' => $capture[4]);
		$art['day'] = $art['date']['day'];
		$art['month'] =$art['date']['month'];
		$art['year'] = $art['date']['year'];
		$art['time'] = $art['date']['time'];
		#nouveau champs
		$art['template'] = 'article.php';
		$art['tags'] = '';
		# On retourne le tableau
		return $art;
	}

	private function getUsers($filename) {
		$users = array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "user"
			if(isset($iTags['user'])) {
				# On compte le nombre de tags "user"
				$nb = sizeof($iTags['user']);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					$users[ $values[ $iTags['user'][$i] ]['attributes']['login'] ] = $values[ $iTags['user'][$i] ]['value'];
				}
			}
		}
		# On retourne le tableau
		return $users;
	}

	private function getStatiques($filename) {
		$aStats = array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "statique"
			if(isset($iTags['statique'])) {
				# On compte le nombre de tags "statique"
				$nb = sizeof($iTags['statique']);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					# Recuperation du nom de la page statique
					$aStats[ $values[ $iTags['statique'][$i] ]['attributes']['number'] ]['name']
					= $values[ $iTags['statique'][$i] ]['value'];
					# Recuperation de l'url de la page statique
					$aStats[ $values[ $iTags['statique'][$i] ]['attributes']['number'] ]['url']
					= strtolower($values[ $iTags['statique'][$i] ]['attributes']['url']);
					# Recuperation de l'etat de la page
					$aStats[ $values[ $iTags['statique'][$i] ]['attributes']['number'] ]['active']
					= intval($values[ $iTags['statique'][$i] ]['attributes']['active']);
					# On affiche la page statique dans le menu ?
					if(isset($values[ $iTags['statique'][$i] ]['attributes']['menu']))
						$aStats[ $values[ $iTags['statique'][$i] ]['attributes']['number'] ]['menu']
						= $values[ $iTags['statique'][$i] ]['attributes']['menu'];
					else
					$aStats[ $values[ $iTags['statique'][$i] ]['attributes']['number'] ]['menu'] = 'oui';
					# On verifie que la page statique existe bien
					$file = PLX_ROOT.$this->plxAdmin->aConf['racine_statiques'].$values[ $iTags['statique'][$i] ]['attributes']['number'];
					$file .= '.'.$values[ $iTags['statique'][$i] ]['attributes']['url'].'.php';
					if(is_readable($file)) # Le fichier existe
						$aStats[ $values[ $iTags['statique'][$i] ]['attributes']['number'] ]['readable'] = 1;
					else # Le fichier est illisible
						$aStats[ $values[ $iTags['statique'][$i] ]['attributes']['number'] ]['readable'] = 0;
				}
			}
		}
		return $aStats;
	}
}
?>
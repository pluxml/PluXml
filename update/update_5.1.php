<?php
/**
 * Classe de mise a jour pour PluXml version 5.1
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# nouveaux parametres
		$new_parameters = array(
			'bypage_archives' => 5,
			'userfolders' => 1,
			'meta_description'=>'',
			'meta_keywords'=>'',
			'plugins'=>'data/configuration/plugins.xml',
			'default_lang'=>(isset($_POST['default_lang'])?$_POST['default_lang']:DEFAULT_LANG),
		);
		# on supprime les parametres obsoletes
		unset($this->plxAdmin->aConf['editor']);
		unset($this->plxAdmin->aConf['style_mobile']);
		# mise à jour du fichier des parametres
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

	# création d'un fichier	.htacces dans le dossier data pour eviter de lister les dossiers
	public function step2() {
		echo L_UPDATE_CREATE_HTACCESS_FILE.' '.PLX_ROOT.'data/.htaccess<br />';
		if(!plxUtils::write('options -indexes', PLX_ROOT.'data/.htaccess')) {
			echo '<p class="error">'.L_UPDATE_CREATE_HTACCESS_FILE.' '.PLX_ROOT.'data/.htaccess</p>';
			return false;
		}
		return true; # pas d'erreurs
	}

	# Migration du fichier des categories
	public function step3() {
		echo L_UPDATE_CATEGORIES_MIGRATION."<br />";
		if($categories = $this->_getCategories(PLX_ROOT.$this->plxAdmin->aConf['categories'])) {
			# On génère le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($categories as $cat_id => $cat) {
				$xml .= "\t<categorie number=\"".$cat_id."\" tri=\"".$cat['tri']."\" bypage=\"".$cat['bypage']."\" menu=\"".$cat['menu']."\" url=\"".$cat['url']."\" template=\"".$cat['template']."\">";
				$xml .= "<name><![CDATA[".plxUtils::cdataCheck($cat['name'])."]]></name>";
				$xml .= "<description><![CDATA[]]></description>";
				$xml .= "<meta_description><![CDATA[]]></meta_description>";
				$xml .= "<meta_keywords><![CDATA[]]></meta_keywords>";
				$xml .= "</categorie>\n";
			}
			$xml .= "</document>";
			if(!plxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['categories'])) {
				echo '<p class="error">'.L_UPDATE_ERR_CATEGORIES_MIGRATION.' ('.$this->plxAdmin->aConf['categories'].')</p>';
				return false;
			}
		}
		return true;
	}


	# Migration du fichier des page statiques
	public function step4() {
		echo L_UPDATE_STATICS_MIGRATION."<br />";
		if($statics = $this->_getStatiques(PLX_ROOT.$this->plxAdmin->aConf['statiques'])) {
			# On génère le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($statics as $static_id => $static) {
				$xml .= "\t<statique number=\"".$static_id."\" active=\"".$static['active']."\" menu=\"".$static['menu']."\" url=\"".$static['url']."\" template=\"".$static['template']."\">";
				$xml .= "<group><![CDATA[".plxUtils::cdataCheck($static['group'])."]]></group>";
				$xml .= "<name><![CDATA[".plxUtils::cdataCheck($static['name'])."]]></name>";
				$xml .= "<meta_description><![CDATA[]]></meta_description>";
				$xml .= "<meta_keywords><![CDATA[]]></meta_keywords>";
				$xml .=	"</statique>\n";
			}
			$xml .= "</document>";
			if(!plxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['statiques'])) {
				echo '<p class="error">'.L_UPDATE_ERR_STATICS_MIGRATION.' ('.$this->plxAdmin->aConf['statiques'].')</p>';
				return false;
			}
		}
		return true;
	}

	# Migration du fichier des utilisateurs
	public function step5() {
		echo L_UPDATE_USERS_MIGRATION."<br />";
		if($users = $this->_getUsers(PLX_ROOT.$this->plxAdmin->aConf['users'])) {
			# On génère le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($users as $user_id => $user) {
				if(intval($user['profil']=='2')) $user['profil']='4';
				$xml .= "\t".'<user number="'.$user_id.'" active="'.$user['active'].'" profil="'.$user['profil'].'" delete="'.$user['delete'].'">'."\n";
				$xml .= "\t\t".'<login><![CDATA['.plxUtils::cdataCheck(trim($user['login'])).']]></login>'."\n";
				$xml .= "\t\t".'<name><![CDATA['.plxUtils::cdataCheck(trim($user['name'])).']]></name>'."\n";
				$xml .= "\t\t".'<infos><![CDATA['.plxUtils::cdataCheck(trim($user['infos'])).']]></infos>'."\n";
				$xml .= "\t\t".'<password><![CDATA['.$user['password'].']]></password>'."\n";
				$xml .= "\t\t".'<email><![CDATA[]]></email>'."\n";
				$xml .= "\t</user>\n";
			}
			$xml .= "</document>";
			if(!plxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['users'])) {
				echo '<p class="error">'.L_UPDATE_ERR_USERS_MIGRATION.' ('.$this->plxAdmin->aConf['users'].')</p>';
				return false;
			}
		}
		return true;
	}

	# Création du fichier data/configuration/plugins.xml
	public function step6() {
		echo L_UPDATE_CREATE_PLUGINS_FILE."<br />";
		$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
		$xml .= '<document>'."\n";
		$xml .= '</document>';
		if(!plxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['plugins'])) {
			echo '<p class="error">'.L_UPDATE_ERR_CREATE_PLUGINS_FILE.'</p>';
			return false;
		}
		return true;
	}

	# suppression du fichier core/admin/fullscreen.php
	public function step7() {
		if(file_exists(PLX_ROOT.'core/admin/fullscreen.php')) {
			echo L_UPDATE_DELETE_FULLSCREEN_FILE."<br />";
			if(!unlink(PLX_ROOT.'core/admin/fullscreen.php')) {
				echo '<p class="error">'.L_UPDATE_ERR_DELETE_FULLSCREEN_FILE.'</p>';
			}
		}
		return true;
	}

	# suppression du dossier de la plxtoolar
	public function step8() {
		if(is_dir(PLX_ROOT.'core/plxtoolbar')) {
			echo L_UPDATE_DELETE_PLXTOOLBAR_FOLDER."<br />";
			if(!$this->deleteDir(PLX_ROOT.'core/plxtoolbar/')) {
				echo '<p class="error">'.L_UPDATE_ERR_DELETE_PLXTOOLBAR_FOLDER.'</p>';
			}
		}
		return true;
	}

	/***************/

	private function _getCategories($filename) {
		$aCats=array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "categorie"
			if(isset($iTags['categorie'])) {
				# On compte le nombre de tags "categorie"
				$nb = sizeof($iTags['categorie']);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					# Recuperation du nom de la categorie
					$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['name']
					= $values[ $iTags['categorie'][$i] ]['value'];
					# Recuperation de l'url de la categorie
					$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['url']
					= strtolower($values[ $iTags['categorie'][$i] ]['attributes']['url']);
					# Recuperation du tri de la categorie si besoin est
					if(isset($values[ $iTags['categorie'][$i] ]['attributes']['tri']))
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['tri']
						= $values[ $iTags['categorie'][$i] ]['attributes']['tri'];
					else # Tri par defaut
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['tri']
						= $this->aConf['tri'];
					# Recuperation du nb d'articles par page de la categorie si besoin est
					if(isset($values[ $iTags['categorie'][$i] ]['attributes']['bypage']))
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['bypage']
						= $values[ $iTags['categorie'][$i] ]['attributes']['bypage'];
					else # Nb d'articles par page par defaut
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['bypage']
						= $this->bypage;
					# recuperation du fichier template
					if(isset($values[ $iTags['categorie'][$i] ]['attributes']['template']))
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['template']
						= $values[ $iTags['categorie'][$i] ]['attributes']['template'];
					else
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['template'] = 'categorie.php';
					# On affiche la categorie dans le menu ?
					if(isset($values[ $iTags['categorie'][$i] ]['attributes']['menu']))
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['menu']
						= $values[ $iTags['categorie'][$i] ]['attributes']['menu'];
					else
						$aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['menu'] = 'oui';

				}
			}
		}
		return $aCats;
	}

	private function _getStatiques($filename) {
		$aStats=array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "statique"
			if(isset($iTags['statique']) AND isset($iTags['name'])) {
				# On compte le nombre de tags "statique"
				$nb = sizeof($iTags['name']);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					$number = $values[ $iTags['statique'][$i*2] ]['attributes']['number'];
					# Recuperation du groupe de la page statique
					$aStats[$number]['group'] = isset($values[ $iTags['statique'][$i] ])?$values[ $iTags['group'][$i] ]['value']:'';
					# Recuperation du nom de la page statique
					$aStats[$number]['name'] = isset($values[ $iTags['statique'][$i] ])?$values[ $iTags['name'][$i] ]['value']:'';
					# Recuperation de l'url de la page statique
					$aStats[$number]['url'] = strtolower($values[ $iTags['statique'][$i*2] ]['attributes']['url']);
					# Recuperation de l'etat de la page
					$aStats[$number]['active'] = intval($values[ $iTags['statique'][$i*2] ]['attributes']['active']);
					# On affiche la page statique dans le menu ?
					if(isset($values[ $iTags['statique'][$i*2] ]['attributes']['menu']))
						$aStats[$number]['menu'] = $values[ $iTags['statique'][$i*2] ]['attributes']['menu'];
					else
						$aStats[$number]['menu'] = 'oui';
					# recuperation du fichier template
					if(isset($values[ $iTags['statique'][$i*2] ]['attributes']['template']))
						$aStats[$number]['template'] = $values[ $iTags['statique'][$i*2] ]['attributes']['template'];
					else
						$aStats[$number]['template'] = 'static.php';
				}
			}
		}
		return $aStats;
	}

	private function _getUsers($filename) {
		$aUsers=array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "user"
			if(isset($iTags['user']) AND isset($iTags['login'])) {
				# On compte le nombre d'utilisateur
				$nb = sizeof($iTags['login']);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					$number = $values[$iTags['user'][$i*6] ]['attributes']['number'];
					$aUsers[$number]['active'] = $values[ $iTags['user'][$i*6] ]['attributes']['active'];
					$aUsers[$number]['delete'] = $values[ $iTags['user'][$i*6] ]['attributes']['delete'];
					$aUsers[$number]['profil'] = $values[ $iTags['user'][$i*6] ]['attributes']['profil'];
					$aUsers[$number]['login'] = isset($values[ $iTags['login'][$i] ])?$values[ $iTags['login'][$i] ]['value']:'';
					$aUsers[$number]['name'] = isset($values[ $iTags['name'][$i] ])?$values[ $iTags['name'][$i] ]['value']:'';
					$aUsers[$number]['password'] = isset($values[ $iTags['password'][$i] ])?$values[ $iTags['password'][$i] ]['value']:'';
					$aUsers[$number]['infos'] = isset($values[ $iTags['infos'][$i] ])?$values[ $iTags['infos'][$i] ]['value']:'';
				}
			}
		}
		# On retourne le tableau
		return $aUsers;
	}

}
?>
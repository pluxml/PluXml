<?php
/**
 * Classe de mise a jour pour PluXml version 5.1
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1 extends plxUpdate {
	const NEW_PARAMS = array(
		'bypage_archives'	=> 5,
		'userfolders'		=> 1,
		'meta_description'	=> '',
		'meta_keywords'		=> '',
		'plugins'			=> PLX_CONFIG_PATH . 'plugins.xml',
		'default_lang'		=> USER_LANG,
	);
	# mise à jour fichier parametres.xml
	public function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		# on supprime les parametres obsoletes
		unset($this->aConf['editor']);
		unset($this->aConf['style_mobile']);
		# mise à jour du fichier des parametres
		$this->updateParameters(self::NEW_PARAMS);

		return true; # pas d'erreurs
	}

	# création d'un fichier	.htacces dans le dossier data pour eviter de lister les dossiers
	public function step2() {
		$filename = preg_replace('#^([\w-]+/).*#', '$1.htaccess', PLX_CONFIG_PATH);
?>
		<li><?= L_UPDATE_CREATE_HTACCESS_FILE ?> <em><?= PLX_ROOT . $filename ?></em></li>
<?php
		if(!plxUtils::write('options -indexes', PLX_ROOT . $filename)) {
?>
		<p class="error"><?= L_UPDATE_CREATE_HTACCESS_FILE ?></p>
<?php
			return false;
		}

		return true; # pas d'erreurs
	}

	# Migration du fichier des categories
	public function step3() {
?>
		<li><?= L_UPDATE_CATEGORIES_MIGRATION ?></li>
<?php
		if($categories = $this->_getCategories(PLX_ROOT . $this->aConf['categories'])) {
			# On génère le fichier XML
			ob_start();
			foreach($categories as $cat_id => $cat) {
?>
	<categorie number="<?= $cat_id ?>" tri="<?= $cat['tri'] ?>" bypage="<?= $cat['bypage'] ?>" menu="<?= $cat['menu'] ?>" url="<?= $cat['url'] ?>" template="<?= $cat['template'] ?>">
		<name><![CDATA[<?= plxUtils::cdataCheck($cat['name']) ?>]]></name>
		<description></description>
		<meta_description></meta_description>
		<meta_keywords></meta_keywords>
	</categorie>
<?php
			}

			if(!$this->writeAsXML(ob_get_clean(), $this->aConf['categories'])) {
?>
		<p class="error"><?= L_UPDATE_ERR_CATEGORIES_MIGRATION ?> ( <em><?= $this->aConf['categories'] ?></em> )</p>
<?php
				return false;
			}
		}

		return true;
	}


	# Migration du fichier des page statiques
	public function step4() {
?>
		<li><?= L_UPDATE_STATICS_MIGRATION ?></li>
<?php
		if($aStatics = $this->_getStatiques(PLX_ROOT.$this->aConf['statiques'])) {
			# On génère le fichier XML
			ob_start();
			foreach($aStatics as $id => $infos) {
?>
	<statique number="<?= $id ?>" active="<?= $infos['active'] ?>" menu="<?= $infos['menu'] ?>" url="<?= $infos['url'] ?>" template="<?= $infos['template'] ?>">
		<group><![CDATA[<?= plxUtils::cdataCheck($infos['group']) ?>]]></group>
		<name><![CDATA[<?= plxUtils::cdataCheck($infos['name']) ?>]]></name>
		<meta_description></meta_description>
		<meta_keywords></meta_keywords>
	</statique>
<?php
			}
			if(!$this->writeAsXML(ob_get_clean(), $this->aConf['statiques'])) {
?>
			<p class="error"><?= L_UPDATE_ERR_STATICS_MIGRATION ?> ( <em><?= $this->aConf['statiques'] ?></em> )</p>
<?php
				return false;
			}
		}

		return true;
	}

	# Migration du fichier des utilisateurs
	public function step5() {
?>
		<li><?= L_UPDATE_USERS_MIGRATION ?></li>
<?php
		if($users = $this->_getUsers(PLX_ROOT.$this->aConf['users'])) {
			# On génère le fichier XML
			ob_start();
			foreach($users as $user_id => $user) {
				if(intval($user['profil']=='2')) $user['profil']='4';
?>
	<user number="<?= $user_id ?>" active="<?= $user['active'] ?>" profil="<?= $user['profil'] ?>" delete="<?= $user['delete'] ?>">
		<login><![CDATA[<?= plxUtils::cdataCheck(trim($user['login'])) ?>]]></login>
		<name><![CDATA[<?= plxUtils::cdataCheck(trim($user['name'])) ?>]]></name>
		<infos><![CDATA[<?= plxUtils::cdataCheck(trim($user['infos'])) ?>]]></infos>
		<password><![CDATA[<?= $user['password'] ?>]]></password>
		<email></email>
	</user>
<?php
			}

			if(!$this->writeAsXML(ob_get_clean(), $this->aConf['users'])) {
?>
		<p class="error"><?= L_UPDATE_ERR_USERS_MIGRATION ?> ( <em><?= $this->aConf['users'] ?> </em> )</p>
<?php
				return false;
			}
		}
		return true;
	}

	# Création du fichier data/configuration/plugins.xml
	public function step6() {
?>
		<li><?= L_UPDATE_CREATE_PLUGINS_FILE ?></li>
<?php
		if(!$this->writeAsXML('', $this->aConf['plugins'])) {
?>
		<p class="error"><?= L_UPDATE_ERR_CREATE_PLUGINS_FILE ?></p>
<?php
			return false;
		}
		return true;
	}

	# suppression du fichier core/admin/fullscreen.php
	public function step7() {
		if(file_exists(PLX_ROOT.'core/admin/fullscreen.php')) {
?>
		<li><?= L_UPDATE_DELETE_FULLSCREEN_FILE ?></li>
<?php
			if(!unlink(PLX_ROOT.'core/admin/fullscreen.php')) {
				echo '<p class="error">'.L_UPDATE_ERR_DELETE_FULLSCREEN_FILE.'</p>';
			}
		}
		return true;
	}

	# suppression du dossier de la plxtoolar
	public function step8() {
		if(is_dir(PLX_ROOT.'core/plxtoolbar')) {
?>
		<li><?= L_UPDATE_DELETE_PLXTOOLBAR_FOLDER ?></li>
<?php
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
			if(is_array($iTags['categorie'])) {
				foreach($iTags['categorie'] as $iTag) {
					$attrs = $values[$iTag]['attributes'];
					$number = $attrs['number'];
					$aCats[$number] = array(
						'name'		=>  $this->getValue($iTag, $values),
						'url'		=> strtolower($attrs['url']),
						'tri'		=> isset($attrs['tri']) ? $attrs['tri'] : $this->aConf['tri'],
						'bypage'	=> isset($attrs['bypage']) ? $attrs['bypage'] : $this->bypage, # Recuperation du nb d'articles par page dans la categorie
						'template' => isset($attrs['template']) ? $attrs['template'] : 'categorie.php',
						'menu' => isset($attrs['menu']) ? $attrs['menu'] : 'oui',
					);
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
				$step = ceil(sizeof($iTags['statique']) / $nb);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					$attrs = $values[ $iTags['statique'][$i * $step] ]['attributes'];
					$number = $attrs['number'];
					$aStat = array(
						# Recuperation de l'url de la page statique
						'url' => strtolower($attrs['url']),
						# Recuperation de l'etat de la page
						'active' => intval($attrs['active']),
						# On affiche la page statique dans le menu ?
						'menu' => isset($attrs['menu']) ? $attrs['menu'] : 'oui',
						# recuperation du fichier template
						'template' => isset($attrs['template']) ? $attrs['template'] : 'static.php',
					);
					foreach(array('group', 'name') as $k) {
						$aStat[$k] = $this->getValue($iTags[$k][$i], $values);
					}
					$aStats[$number] = $aStat;
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
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
			xml_parse_into_struct($parser, $data,$values, $iTags);
			xml_parser_free($parser);

			# On verifie qu'il existe des tags "user"
			if(isset($iTags['user']) AND isset($iTags['login'])) {
				# On compte le nombre d'utilisateur
				$nb = sizeof($iTags['login']);
				$step = ceil(sizeof($iTags['user']) / $nb);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					$attrs = $values[$iTags['user'][$i * $step]]['attributes'];
					$userId = $attrs['number'];
					$user = array(
						'active'	=> $attrs['active'],
						'delete'	=> $attrs['delete'],
						'profil'	=> $attrs['profil'],
					);
					foreach(array('login', 'name', 'infos', 'password') as $k) {
						$user[$k] = $this->getValue($iTags[$k][$i], $values);
					}
					$aUsers[$userId] = $user;
				}
			}
		}
		# On retourne le tableau
		return $aUsers;
	}

}

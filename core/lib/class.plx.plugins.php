<?php
/**
 * Classe plxPlugins responsable de la gestion des plugins
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxPlugins {

	public $aHooks=array(); # tableau de tous les hooks des plugins à executer
	public $aPlugins=array(); #tableau contenant les plugins
	public $default_lang; # langue par defaut utilisée par PluXml

	/**
	 * Constructeur de la classe plxPlugins
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang='') {
		$this->default_lang=$default_lang;
	}

	/**
	 * Méthode qui renvoit une instance d'un plugin
	 *
	 * @param	plugName	nom du plugin
	 * @return	object		object de type plxPlugin / false en cas d'erreur
	 * @return	null
	 * @author	Stephane F
	 **/
	public function getInstance($plugName) {
		$filename = PLX_PLUGINS."$plugName/$plugName.php";
		if(is_file($filename)) {
			include_once($filename);
			if (class_exists($plugName)) {
				# réactualisation de la langue si elle a été modifié par un plugin
				$context = defined('PLX_ADMIN') ? 'admin_lang' : 'lang';
				$lang = isset($_SESSION[$context]) ? $_SESSION[$context] : $this->default_lang;
				# chargement du plugin en créant une nouvelle instance
				return new $plugName($lang);
			}
		}
		return false;
	}

	/**
	 * Méthode qui charge le fichier plugins.xml
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function loadPlugins() {

		if(!is_file(path('XMLFILE_PLUGINS'))) return;

		$updAction = false;

		# Mise en place du parseur XML
		$data = implode('',file(path('XMLFILE_PLUGINS')));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# On verifie qu'il existe des tags "plugin"
		if(isset($iTags['plugin'])) {
			# On compte le nombre de tags "plugin"
			$nb = sizeof($iTags['plugin']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				$attributes = $values[ $iTags['plugin'][$i] ]['attributes'];
				$name = $attributes['name'];
				$scope = (!empty($attributes['scope'])) ? $attributes['scope'] : '';
				if(
					defined('PLX_ADMIN') or
					empty($scope) or # retro-compatibilité pour plugin sans balise <scope>
					($scope == 'site')
				) {
					if(
						empty($scope) or
						(defined('PLX_ADMIN') and $scope == 'admin') or
						(!defined('PLX_ADMIN') and $scope == 'site')
					) {
						if($instance = $this->getInstance($name)) {
							$this->aPlugins[$name] = $instance;
							$this->aHooks = array_merge_recursive($this->aHooks, $instance->getHooks());
							# Si le plugin a une méthode pour des actions de mises à jour
							if(method_exists($instance, 'onUpdate')) {
								if(is_file(PLX_PLUGINS.$name.'/update')) {
									# on supprime le fichier update pour eviter d'appeler la methode onUpdate
									# à chaque chargement du plugin
									chmod(PLX_PLUGINS.$name.'/update', 0644);
									unlink(PLX_PLUGINS.$name.'/update');
									$updAction = $instance->onUpdate();
								}
							}
						}
					} else {
						# Si PLX_ADMIN, on vérifie que le plugin existe et on le recense pour les styles CSS, sans charger sa class.
						if(is_file(PLX_PLUGINS."$name/$name.php")) {
							$this->aPlugins[$name] = false;
						}
					}
				}
			}
		}

		if($updAction) {
			if(isset($updAction['cssCache']) AND $updAction['cssCache']==true) {
				$this->cssCache('admin');
				$this->cssCache('site');
			}
		}
	}

	/**
	 * Méthode qui execute les hooks des plugins
	 *
	 * @param	hookname	nom du hook à appliquer
	 * @param	parms		parametre ou liste de paramètres sous forme de array
	 * @return	null
	 * @author	Stephane F
	 **/
	public function callHook($hookName, $parms=null) {
		if(isset($this->aHooks[$hookName])) {
			ob_start();
			foreach($this->aHooks[$hookName] as $callback) {
				if($callback['class']=='=SHORTCODE=') {
					echo $callback['method'];
				} else {
					$return = $this->aPlugins[$callback['class']]->{$callback['method']}($parms);
				}
			}
			if(isset($return))
				return array('?>'.ob_get_clean().'<?php ', $return);
			else
				return '?>'.ob_get_clean().'<?php ';
		}
	}

	/**
	 * Méthode qui récupère les infos des plugins actifs
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function getInfos() {
		foreach($this->aPlugins as $plugName => $plugInstance) {
			$plugInstance->getInfos();
		}
	}

	/**
	 * Méthode qui renvoie la liste des plugins inactifs
	 *
	 * @return	array		liste des plugins inactifs
	 * @author	Stephane F
	 **/
	public function getInactivePlugins() {

		$aPlugins = array();
		$dirs = plxGlob::getInstance(PLX_PLUGINS, true);
		if(sizeof($dirs->aFiles)>0) {
			foreach($dirs->aFiles as $plugName) {
				if(!isset($this->aPlugins[$plugName]) AND $plugInstance=$this->getInstance($plugName)) {
					$plugInstance->getInfos();
					$aPlugins[$plugName] = $plugInstance;
				}
			}
		}
		ksort($aPlugins);
		return $aPlugins;
	}

	/**
	 * Méthode qui sauvegarde le fichier plugins.xml et qui génère les fichiers admin.css et site.css des plugins
	 *
	 * @param	content		array content $_POST
	 * @return	boolean		resultat de la sauvegarde / TRUE = ok
	 * @author	Stephane F
	 **/
	public function saveConfig($content) {

		# Pas de modification de la config des plugins, si on n'est pas en mode admin.
		if(!defined('PLX_ADMIN')) { return false; }

		if(empty($content['update'])) {
			if(!empty($content['selection'])) {
				switch($content['selection']) {
					case 'activate':		# activation des plugins
						foreach($content['chkAction'] as $idx => $plugName) {
							if($plugInstance = $this->getInstance($plugName)) {
								if(method_exists($plugName, 'OnActivate'))
									$plugInstance->OnActivate();
								$this->aPlugins[$plugName] = $plugInstance;
							}
						}
						break;
					case 'deactivate':	# désactivation des plugins
						foreach($content['chkAction'] as $idx => $plugName) {
							$plugInstance = $this->aPlugins[$plugName];
							if(empty($plugInstance)) {
								$plugInstance = $this->getInstance($plugName);
							}
							if($plugInstance) {
								if(method_exists($plugName, 'OnDeActivate'))
									$plugInstance->OnDeActivate();
								unset($this->aPlugins[$plugName]);
							}
						}
						break;
					case 'delete':		# suppression des plugins
						foreach($content['chkAction'] as $idx => $plugName) {
							if($this->deleteDir(realpath(PLX_PLUGINS.$plugName))) {
								# suppression fichier de config du plugin
								if(is_file(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.xml'))
									unlink(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.xml');
								# suppression fichier site.css du plugin
								if(is_file(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.site.css'))
									unlink(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.site.css');
								# suppression fichier admin.css du plugin
								if(is_file(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.admin.css'))
									unlink(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.admin.css');
								unset($this->aPlugins[$plugName]);
							} else {
								plxMsg::Error(L_PLUGINS_DELETE_ERROR." (".$plugName.")");
								break;
							}
						}
						break;
				}
			}
		} else {
			# tri des plugins par ordre de chargement
			$aPlugins = array();
			asort($content['plugOrdre']);
			foreach($content['plugOrdre'] as $plugName => $idx) {
				$aPlugins[$plugName] = $this->aPlugins[$plugName];
			}
			$this->aPlugins = $aPlugins;
		}

		# génération du cache css des plugins
		$this->cssCache('site');
		$this->cssCache('admin');

		# Début du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";

		foreach($this->aPlugins as $name=>$plugin) {
			if(!empty($plugin)) {
				$scope = $plugin->getInfo('scope');
			} elseif($plugInstance=$this->getInstance($name)) {
				$scope = $plugInstance->getInfo('scope');
			} else {
				$scope = '';
			}
			$xml .= "\t<plugin name=\"$name\" scope=\"$scope\"></plugin>\n";
		}

		$xml .= "</document>";

		# On écrit le fichier
		if(plxUtils::write($xml,path('XMLFILE_PLUGINS')))
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		else
			return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_PLUGINS'));

	}

	/**
	 * Méthode récursive qui supprime tous les dossiers et les fichiers d'un répertoire
	 *
	 * @param	deldir	répertoire de suppression
	 * @return	boolean	résultat de la suppression
	 * @author	Stephane F
	 **/
	public function deleteDir($deldir) { #fonction récursive

		if(is_dir($deldir) AND !is_link($deldir)) {
			if($dh = opendir($deldir)) {
				while(($file = readdir($dh)) != false) {
					if($file != '.' AND $file != '..') {
						$this->deleteDir("$deldir/$file");
					}
				}
				closedir($dh);
			}
			return @rmdir($deldir);
		}
		# Suppression des messages de warning
		return @unlink($deldir);
	}

	/**
	 * Méthode qui génère le fichier css admin.css ou site.css
	 *
	 * @param	type		type du fichier (admin|site)
	 * @return	boolean		vrai si cache généré
	 * @author	Stephane F
	 **/
	public function cssCache($type) {

		$cache = '';
		if(!preg_match('@\.css$@', $type)) $type .= '.css';
		foreach(array_keys($this->aPlugins) as $plugName) {
			$filesList = array(
				PLX_ROOT.PLX_CONFIG_PATH."plugins/$plugName.$type",
				PLX_PLUGINS."$plugName/css/$type"
			);
			foreach($filesList as $filename) {
				if(is_file($filename)) {
					$cache .= trim(file_get_contents($filename));
					break;
				}
			}
		}
		$minify_filename = PLX_PLUGINS.$type;
		if(!empty($cache)) {
			return plxUtils::write(plxUtils::minify($cache), $minify_filename);
		} elseif((is_file($minify_filename))) {
			unlink($minify_filename);
		}
		return true;
	}
}

/**
 * Classe plxPlugin destiné à créer un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxPlugin {

	protected $aInfos=array();  # tableau des infos sur le plugins venant du fichier infos.xml
	protected $aParams=array(); # tableau des paramètres sur le plugins venant du fichier parameters.xml
	protected $aHooks=array(); # tableau des hooks du plugin
	protected $aLang=array(); # tableau contenant les clés de traduction de la langue courante de PluXml

	protected $plug=array(); # tableau contenant des infos diverses pour le fonctionnement du plugin
	protected $adminProfil=''; # profil(s) utilisateur(s) autorisé(s) à acceder à la page admin.php du plugin
	protected $configProfil=''; # profil(s) utilisateur(s) autorisé(s) à acceder à la page config.php du plugin

	public $default_lang=DEFAULT_LANG; # langue par defaut de PluXml
	public $adminMenu=false; # infos de customisation du menu pour accèder à la page admin.php du plugin

	/**
	 * Constructeur de la classe plxPlugin
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang='') {

		$plugName= get_class($this);
		$this->getPluginLang($plugName, $default_lang);
		$this->plug = array(
			'dir' 			=> PLX_PLUGINS,
			'name' 			=> $plugName,
			'filename'		=> PLX_PLUGINS.$plugName.'/'.$plugName.'.php',
			'parameters.xml'=> PLX_ROOT.PLX_CONFIG_PATH.'plugins/'.$plugName.'.xml',
			'infos.xml'		=> PLX_PLUGINS.$plugName.'/infos.xml'
		);
		$this->loadParams();
		if(defined('PLX_ADMIN'))
			$this->getInfos();

	}

	/**
	 * Méthode qui charge le fichier de langue du plugin
	 * Si la langue par défaut n'est pas disponible on tente de charger le fr.php sinon on prend le 1er fichier de langue dispo
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/

	public function getPluginLang($plugName, $lang) {

		$dirname = PLX_PLUGINS.$plugName.'/lang/';

		if(is_dir($dirname)) {

			$filename = $dirname.$lang.'.php';

			if(!is_file($filename)) {
				if(is_file($dirname.'fr.php'))
					$lang = 'fr';
				else {
					if($dh = opendir($dirname)) {
						while(false !== ($file = readdir($dh))) {
							if(preg_match('/^([a-zA-Z]{2})\.php$/', $file, $capture)) {
								$lang = $capture[1];
								break;
							}
						}
					}
					closedir($dh);
				}
			}
		}

		$this->default_lang = $lang;
		$this->aLang = $this->loadLang(PLX_PLUGINS.$plugName.'/lang/'.$this->default_lang.'.php');

	}

	/**
	 * Méthode qui renvoit le(s) profil(s) utilisateur(s) autorisé(s) à acceder à la page admin.php du plugin
	 *
	 * @return	string		profil(s) utilisateur(s)
	 * @author	Stephane F
	 **/
	public function getAdminProfil() {
		return $this->adminProfil;
	}

	/**
	 * Méthode qui mémorise le(s) profil(s) utilisateur(s) autorisé(s) à acceder à la page admin.php du plugin
	 *
	 * @param	profil		profil(s) (PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR, PROFIL_WRITER)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function setAdminProfil($profil) {
		$this->adminProfil=func_get_args();
	}

	/**
	 * Méthode qui permet de personnaliser le menu qui permet d'acceder à la page admin.php du plugin
	 *
	 * @param	title 		titre du menu
	 * @param	position 	position du menu dans la sidebar
	 * @param	caption 	légende du menu (balise title du lien)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function setAdminMenu($title='', $position='', $caption='') {
		$this->adminMenu = array(
			'title'=>$title,
			'position'=>($position==''?false:$position),
			'caption'=>($caption==''?$title:$caption)
		);
	}

	/**
	 * Méthode qui renvoit le(s) profil(s) utilisateur(s) autorisé(s) à accéder à la page config.php du plugin
	 *
	 * @return	string		profil(s) utilisateur(s)
	 * @author	Stephane F
	 **/
	public function getConfigProfil() {
		return $this->configProfil;
	}

	/**
	 * Méthode qui mémorise le(s) profil(s) utilisateur(s) autorisé(s) à accéder à la page config.php du plugin
	 *
	 * @param	profil		profil(s) (PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR, PROFIL_WRITER)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function setConfigProfil($profil) {
		$this->configProfil=func_get_args();
	}

	/**
	 * Méthode qui retourne les hooks définis dans le plugin
	 *
	 * @return	array		tableau des hooks du plugin
	 * @author	Stephane F
	 **/
	public function getHooks() {
		return $this->aHooks;
	}

	/**
	 * Méthode qui charge le fichier de langue par défaut du plugin
	 *
	 * @param	filename	fichier de langue à charger
	 * @return	array		tableau contenant les clés de traduction
	 * @author	Stephane F
	 **/
	public function loadLang($filename) {
		if(!is_file($filename)) return;
		include($filename);
		return $LANG;
	}

	/**
	 * Méthode qui affiche une clé de traduction dans la langue par défaut de PluXml
	 *
	 * @param	key		clé de traduction à récuperer
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function lang($key='') {
		if(isset($this->aLang[$key]))
			echo $this->aLang[$key];
		else
			echo $key;
	}

	/**
	 * Méthode qui retourne une clé de traduction dans la langue par défaut de PluXml
	 *
	 * @param	key		clé de traduction à récuperer
	 * @return	string	clé de traduite
	 * @author	Stephane F
	 **/
	public function getLang($key='') {
		if(isset($this->aLang[$key]))
			return $this->aLang[$key];
		else
			return $key;
	}

	/**
	 * Méthode qui charge le fichier des parametres du plugin parameters.xml
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function loadParams() {

		if(!is_file($this->plug['parameters.xml'])) return;

		# Mise en place du parseur XML
		$data = implode('',file($this->plug['parameters.xml']));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# On verifie qu'il existe des tags "parameter"
		if(isset($iTags['parameter'])) {
			# On compte le nombre de tags "parameter"
			$nb = sizeof($iTags['parameter']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				if(isset($values[$iTags['parameter'][$i]]['attributes']['name'])) {
					$name=$values[$iTags['parameter'][$i]]['attributes']['name'];
					$type=isset($values[$iTags['parameter'][$i]]['attributes']['type'])?$values[$iTags['parameter'][$i]]['attributes']['type']:'numeric';
					$value=isset($values[$iTags['parameter'][$i]]['value'])?$value=$values[$iTags['parameter'][$i]]['value']:'';
					$this->aParams[$name] = array(
						'type'	=> $type,
						'value'	=> $value
					);
				}
			}
		}
	}

	/**
	 * Méthode qui sauvegarde le fichier des parametres du plugin parameters.xml
	 *
	 * @return	boolean		resultat de la sauvegarde / TRUE = ok
	 * @author	Stephane F
	 **/
	public function saveParams() {

		# Début du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		foreach($this->aParams as $k=>$v) {
			switch($v['type']) {
				case 'numeric':
					$xml .= "\t<parameter name=\"$k\" type=\"".$v['type']."\">".intval($v['value'])."</parameter>\n";
					break;
				case 'string':
					$xml .= "\t<parameter name=\"$k\" type=\"".$v['type']."\">".plxUtils::cdataCheck(plxUtils::strCheck($v['value']))."</parameter>\n";
					break;
				case 'cdata':
					$xml .= "\t<parameter name=\"$k\" type=\"".$v['type']."\"><![CDATA[".plxUtils::cdataCheck($v['value'])."]]></parameter>\n";
					break;
			}
		}
		$xml .= "</document>";

		# On écrit le fichier
		if(plxUtils::write($xml,$this->plug['parameters.xml'])) {
			# suppression ancien fichier parameters.xml s'il existe encore (5.1.7+)
			if(file_exists($this->plug['dir'].$this->plug['name'].'/parameters.xml'))
				unlink($this->plug['dir'].$this->plug['name'].'/parameters.xml');
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		}
		else
			return plxMsg::Error(L_SAVE_ERR.' '.$this->plug['parameters.xml']);
	}

	/**
	 * Méthode qui renvoie le tableau des paramètres
	 *
	 * @return	array		tableau aParams
	 * @author	Stephane F
	 **/
	public function getParams() {
		if(sizeof($this->aParams)>0)
			return $this->aParams;
		else
			return false;
	}

	/**
	 * Méthode qui renvoie la valeur d'un parametre du fichier parameters.xml
	 *
	 * @param	param	nom du parametre à recuperer
	 * @return	string	valeur du parametre
	 * @author	Stephane F
	 **/
	public function getParam($param) {
		return (isset($this->aParams[$param])? $this->aParams[$param]['value']:'');
	}

	/**
	 * Méthode qui modifie la valeur d'un parametre du fichier parameters.xml
	 *
	 * @param	param	nom du parametre à recuperer
	 * @param	value	valeur du parametre
	 * @type	type 	type du parametre (numeric, string, cdata)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function setParam($param, $value, $type=false) {

		if(!empty($type) and in_array($type, array('numeric', 'string', 'cdata'))) {
			$this->aParams[$param]['type'] = $type;
			$this->aParams[$param]['value'] = ($type == 'numeric') ? intval($value) : $value;
		}
	}

	/**
	 * Méthode qui supprime un parametre du fichier parameters.xml
	 *
	 * @param	param	nom du parametre à supprimer
	 * @return	true si parametre supprimé, false sinon
	 * @author	Sebastien H
	 **/
	public function delParam($param) {
		if(isset($this->aParams[$param])) {
			unset($this->aParams[$param]);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Méthode qui recupere les données du fichier infos.xml
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function getInfos() {

		if(!is_file($this->plug['infos.xml'])) return;

		# Mise en place du parseur XML
		$data = implode('',file($this->plug['infos.xml']));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		$this->aInfos = array(
			'title'			=> (isset($iTags['title']) AND isset($values[$iTags['title'][0]]['value']))?$values[$iTags['title'][0]]['value']:'',
			'author'		=> (isset($iTags['author']) AND isset($values[$iTags['author'][0]]['value']))?$values[$iTags['author'][0]]['value']:'',
			'version'		=> (isset($iTags['version']) AND isset($values[$iTags['version'][0]]['value']))?$values[$iTags['version'][0]]['value']:'',
			'date'			=> (isset($iTags['date']) AND isset($values[$iTags['date'][0]]['value']))?$values[$iTags['date'][0]]['value']:'',
			'site'			=> (isset($iTags['site']) AND isset($values[$iTags['site'][0]]['value']))?$values[$iTags['site'][0]]['value']:'',
			'description'	=> (isset($iTags['description']) AND isset($values[$iTags['description'][0]]['value']))?$values[$iTags['description'][0]]['value']:'',
			'scope'			=> (isset($iTags['scope']) AND isset($values[$iTags['scope'][0]]['value']))?strtolower($values[$iTags['scope'][0]]['value']):''
			);

	}

	/**
	 * Méthode qui renvoie la valeur d'un parametre du fichier infos.xml
	 *
	 * @param	param	nom du parametre à recuperer
	 * @return	string	valeur de l'info
	 * @author	Stephane F
	 **/
	public function getInfo($param) {
		return (isset($this->aInfos[$param])?$this->aInfos[$param]:'');
	}

	/**
	 * Méthode qui ajoute un hook à executer
	 *
	 * @param	hookname		nom du hook
	 * @param	userfunction	nom de la fonction du plugin à executer
	 * @return	null
	 * @author	Stephane F
	 **/
	public function addHook($hookname, $userfunction) {
		if(method_exists(get_class($this), $userfunction)) {
			$this->aHooks[$hookname][]=array(
				'class'		=> get_class($this),
				'method'	=> $userfunction
			);
		}
	}

	/**
	 * Méthode qui retourne le chemin relatif du dossier du plugin
	 *
	 * @return	string		chemin vers le dossier du plugin
	 * @author	Stephane F
	 **/
	public function REL_PATH() {
		return PLX_PLUGINS.get_class($this).'/';
	}

	/**
	 * Méthode qui retourne le chemin absolu du dossier du plugin
	 *
	 * @return	string		chemin vers le dossier du plugin
	 * @author	Stephane F
	 **/
	public function ABS_PATH() {
		return str_replace(PLX_ROOT, '', $this->REL_PATH());
	}

	/**
	 * Méthode qui retourne l'url du dossier du plugin (avec le http://)
	 *
	 * @return	string		url vers le dossier du plugin
	 * @author	Stephane F
	 **/
	public function URL() {
		return plxUtils::getRacine().$this->ABS_PATH();
	}

}
?>
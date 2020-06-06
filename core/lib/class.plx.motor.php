<?php

/**
 * Classe plxMotor responsable du traitement global du script
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F, Pedro "P3ter" CADETE
 **/

if(!defined('PLX_CONFIG_PATH') or !defined('PLX_VERSION')) { exit; }

class plxMotor {
	const PLX_TEMPLATES = PLX_TEMPLATES;#declaration in
	const PLX_TEMPLATES_DATA = PLX_TEMPLATES_DATA;#lib/config.php

	public $get = false; # Donnees variable GET
	public $racine = false; # Url de PluXml
	public $path_url = false; # chemin de l'url du site
	public $style = false; # Dossier contenant le thème
	public $tri; # Tri d'affichage des articles
	public $tri_coms; # Tri d'affichage des commentaires
	public $bypage = false; # Pagination des articles
	public $page = 1; # Numéro de la page
	public $motif = false; # Motif de recherche
	public $mode = false; # Mode de traitement
	public $template = false; # Template d'affichage
	public $cible = false; # Article, categorie ou page statique cible

	public $activeCats = false; # Liste des categories actives sous la forme 001|002|003 etc
	public $homepageCats = false; # Liste des categories à afficher sur la page d'accueil sous la forme 001|002|003 etc
	public $activeArts = array(); # Tableaux des articles appartenant aux catégories actives

	public $aConf = array(  # Tableau de configuration. Valeurs par défaut.
		'version' 						=> PLX_VERSION,
		'title'							=> 'PluXml',
		'description'					=> '',
		'meta_description'				=> '',
		'meta_keywords'					=> 'cms,xml,pluxml',#,' . DEFAULT_LANG, #Legacy #Fix PHP 5.5 on Install : Parse error: syntax error, unexpected '.', expecting ')'
		'timezone'						=> 'Europe/Paris',
		'allow_com'						=> 1,
		'mod_com'						=> 0,
		'mod_art'						=> 0,
		'capcha'						=> 1,
		'style'							=> 'defaut',
		'clef'							=> '', # A générer
		'bypage'						=> 5,
		'bypage_archives'				=> 5,
		'bypage_tags'					=> 5,
		'bypage_admin'					=> 10,
		'bypage_admin_coms'				=> 10,
		'bypage_feed'					=> 8,
		'tri'							=> 'desc',
		'tri_coms'						=> 'asc',
		'images_l'						=> 800,
		'images_h'						=> 600,
		'miniatures_l'					=> 200,
		'miniatures_h'					=> 100,
		'thumbs'						=> 1,
		'medias'						=> 'data/medias/',
		'racine_articles'				=> 'data/articles/',
		'racine_commentaires'			=> 'data/commentaires/',
		'racine_statiques'				=> 'data/statiques/',
		'racine_themes'					=> 'themes/',
		'racine_plugins'				=> 'plugins/',
		'custom_admincss_file'			=> '',
		'homestatic'					=> '',
		'urlrewriting'					=> 0,
		'gzip'							=> 0,
		'feed_chapo'					=> 0,
		'feed_footer'					=> '',
		'default_lang'					=> DEFAULT_LANG,
		'userfolders'					=> 0,
		'display_empty_cat'				=> 0,
		# PluXml 5.1.7 et plus
		'hometemplate'					=> 'home.php',
		# PluXml 5.8 et plus
		'enable_rss'					=> '1',
		'lostpassword'					=> '1',
		'email_method'					=> 'sendmail',
		'smtp_server'					=> '',
		'smtp_username'					=> '',
		'smtp_password'					=> '',
		'smtp_port'						=> 465,
		'smtp_security'					=> 'ssl',
		'smtpOauth2_emailAdress'		=> '',
		'smtpOauth2_clientId'			=> '',
		'smtpOauth2_clientSecret'		=> '',
		'smtpOauth2_refreshToken'		=> '',
		# PluXml 5.8.3 et plus
		'cleanurl'						=> 0,
		'thumbnail'						=> '',
	);
	public $aCats = array(); # Tableau de toutes les catégories
	public $aStats = array(); # Tableau de toutes les pages statiques
	public $aTags = array(); # Tableau des tags
	public $aUsers = array();  # Tableau des utilisateurs
	public $aTemplates = null; # Tableau des templates

	public $plxGlob_arts = null; # Objet plxGlob des articles
	public $plxGlob_coms = null; # Objet plxGlob des commentaires
	public $plxRecord_arts = null; # Objet plxRecord des articles
	public $plxRecord_coms = null; # Objet plxRecord des commentaires
	public $plxCapcha = null; # Objet plxCapcha
	public $plxErreur = null; # Objet plxErreur
	public $plxPlugins = null; # Objet plxPlugins

	private static $instance;

	/**
	 * Méthode qui se charger de créer le Singleton plxMotor
	 *
	 * @return	self			return une instance de la classe plxMotor
	 * @author	Stephane F
	 **/
	public static function getInstance(){
		if (!isset(self::$instance)) {
			self::$instance = false;
			self::$instance = new plxMotor(path('XMLFILE_PARAMETERS'));
		}
		return self::$instance;
	}

	/**
	 * Constructeur qui initialise certaines variables de classe
	 * et qui lance le traitement initial
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	protected function __construct($filename) {

		# On parse le fichier de configuration
		$this->getConfiguration($filename);
		if(!defined('PLX_SITE_LANG')) define('PLX_SITE_LANG', $this->aConf['default_lang']);
		# récupération des paramètres dans l'url
		$this->get = plxUtils::getGets();
		# gestion du timezone
		date_default_timezone_set($this->aConf['timezone']);

		if(defined('PLX_INSTALLER')) {
			# En cours d'installation
			return;
		}

		# On vérifie s'il faut faire une mise à jour
		if(
			(empty($this->aConf['version']) OR PLX_VERSION != $this->aConf['version']) AND
			!defined('PLX_UPDATER')
		) {
			header('Location: '.PLX_ROOT.'update/index.php');
			exit;
		}

		# Chargement des variables
		$this->style = $this->aConf['style'];
		$this->racine = $this->aConf['racine'];
		$this->bypage = $this->aConf['bypage'];
		$this->tri = $this->aConf['tri'];
		$this->tri_coms = $this->aConf['tri_coms'];
		# On récupère le chemin de l'url
		$var = parse_url($this->racine);
		$this->path_url = str_replace(ltrim($var['path'], '\/'), '', ltrim($_SERVER['REQUEST_URI'], '\/'));

		# Traitement des plugins
		# Détermination du fichier de langue (nb: la langue peut être modifiée par plugins via $_SESSION['lang'])
		$context = defined('PLX_ADMIN') ? 'admin_lang' : 'lang';
		$lang = isset($_SESSION[$context]) ? $_SESSION[$context] : $this->aConf['default_lang'];

		if(class_exists('plxPlugins')) {
			# Récupération de la liste des plugins actifs
			$this->plxPlugins = new plxPlugins($lang);
			$this->plxPlugins->loadPlugins();
			# Hook plugins
			eval($this->plxPlugins->callHook('plxMotorConstructLoadPlugins'));
		}

		if(class_exists('plxGlob')) {
			# Traitement sur les répertoires des articles et des commentaires
			$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles'],false,true,'arts');
			$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires']);
			# Récuperation des articles appartenant aux catégories actives
			$this->getActiveArts();
		}

		# Récupération des données dans les autres fichiers xml
		$this->getCategories(path('XMLFILE_CATEGORIES')); # utilise l'objet $this->plxGlob_arts
		$this->getStatiques(path('XMLFILE_STATICS'));
		$this->getTags(path('XMLFILE_TAGS'));
		$this->getUsers(path('XMLFILE_USERS'));

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxMotorConstruct'));
		}

		# Get templates from core/templates and data/templates
		$this->getTemplates(self::PLX_TEMPLATES);
		$this->getTemplates(self::PLX_TEMPLATES_DATA);
	}

	/**
	 * Méthode qui effectue une analyse de la situation et détermine
	 * le mode à appliquer. Cette méthode alimente ensuite les variables
	 * de classe adéquates
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function prechauffage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorPreChauffageBegin'))) return;

		if(!$this->get AND $this->aConf['homestatic']!='' AND isset($this->aStats[$this->aConf['homestatic']]) AND $this->aStats[$this->aConf['homestatic']]['active']) {
			$this->mode = 'static'; # Mode static
			$this->cible = $this->aConf['homestatic'];
			$this->template = $this->aStats[ $this->cible ]['template'];
		}
		elseif(empty($this->get)
				OR preg_match('#^(blog|blog\/page\d*|\/?page\d*)$#', $this->get)
				AND !preg_match('#^(?:article|static|categorie|archives|tag|preview|telechargement|download)[\b\d/]+#', $this->get)) {
			$this->mode = 'home';
			$this->template = $this->aConf['hometemplate'];
			$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			# On regarde si on a des articles en mode "home"
			if($this->plxGlob_arts->query('#^\d{4}\.(home[0-9,]*)\.\d{3}\.\d{12}\.[\w-]+\.xml$#')) {
				$this->motif = '#^\d{4}.(home[0-9,]*).\d{3}.\d{12}.[\w-]+.xml$#';
			} else { # Sinon on recupere tous les articles
				$this->motif = '#^\d{4}.(?:\d|,)*(?:'.$this->homepageCats.')(?:\d|,)*.\d{3}.\d{12}.[\w-]+.xml$#';
			}
		}
		elseif($this->get AND preg_match('#^article(\d+)\/?([\w-]+)?#',$this->get,$capture)) {
			$this->mode = 'article'; # Mode article
			$this->template = 'article.php';
			$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT); # On complete sur 4 caracteres
			$this->motif = '#^'.$this->cible.'.(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.\d{12}.[\w-]+.xml$#'; # Motif de recherche
			if($this->getArticles()) {
				# Redirection 301
				if(!isset($capture[2]) OR $this->plxRecord_arts->f('url')!=$capture[2]) {
					$this->redir301($this->urlRewrite('?article'.intval($this->cible).'/'.$this->plxRecord_arts->f('url')));
				}
			} else {
				$this->error404(L_UNKNOWN_ARTICLE);
			}
		}
		elseif($this->get AND preg_match('#^static(\d+)\/?([\w-]+)?#',$this->get,$capture)) {
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complète sur 3 caractères
			if(!isset($this->aStats[$this->cible]) OR !$this->aStats[$this->cible]['active']) {
				$this->error404(L_UNKNOWN_STATIC);
			} else {
				if(!empty($this->aConf['homestatic']) AND $capture[1]){
					if($this->aConf['homestatic']==$this->cible){
						$this->redir301($this->urlRewrite());
					}
				}
				if($this->aStats[$this->cible]['url']==$capture[2]) {
					$this->mode = 'static'; # Mode static
					$this->template = $this->aStats[$this->cible]['template'];
				} else {
					$this->redir301($this->urlRewrite('?static'.intval($this->cible).'/'.$this->aStats[$this->cible]['url']));
				}
			}
		}
		elseif($this->get AND preg_match('#^categorie(\d+)\/?([\w-]+)?#',$this->get,$capture)) {
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			if(!empty($this->aCats[$this->cible]) AND $this->aCats[$this->cible]['active'] AND $this->aCats[$this->cible]['url']==$capture[2]) {
				$this->mode = 'categorie'; # Mode categorie
				$this->motif = '#^\d{4}.((?:\d|home|,)*(?:'.$this->cible.')(?:\d|home|,)*).\d{3}.\d{12}.[\w-]+.xml$#'; # Motif de recherche
				$this->template = $this->aCats[$this->cible]['template'];
				$this->tri = $this->aCats[$this->cible]['tri']; # Recuperation du tri des articles
				$this->bypage = $this->aCats[$this->cible]['bypage'] > 0 ? $this->aCats[$this->cible]['bypage'] : $this->bypage;
			}
			elseif(isset($this->aCats[$this->cible])) { # Redirection 301
				if($this->aCats[$this->cible]['url']!=$capture[2]) {
					$this->redir301($this->urlRewrite('?categorie'.intval($this->cible).'/'.$this->aCats[$this->cible]['url']));
				}
			} else {
				$this->error404(L_UNKNOWN_CATEGORY);
			}
		}
		elseif($this->get AND preg_match('#^archives\/(\d{4})[\/]?(\d{2})?[\/]?(\d{2})?#',$this->get,$capture)) {
			$this->mode = 'archives';
			$this->template = 'archives.php';
			$this->bypage = $this->aConf['bypage_archives'];
			$this->cible = $search = $capture[1];
			if(!empty($capture[2])) $this->cible = ($search .= $capture[2]);
			else $search .= '\d{2}';
			if(!empty($capture[3])) $search .= $capture[3];
			else $search .= '\d{2}';
			$this->motif = '#^\d{4}.(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.'.$search.'\d{4}.[\w-]+.xml$#';
		}
		elseif($this->get AND preg_match('#^tag\/([\w-]+)#',$this->get,$capture)) {
			$this->cible = $capture[1];
			$ids = array();
			$datetime = date('YmdHi');
			foreach($this->aTags as $idart => $tag) {
				if($tag['date']<=$datetime) {
					$tags = array_map("trim", explode(',', $tag['tags']));
					$tagUrls = array_map(array('plxUtils', 'urlify'), $tags);
					if(in_array($this->cible, $tagUrls)) {
						if(!isset($ids[$idart])) $ids[$idart] = $idart;
						if(!isset($this->cibleName)) {
							$key = array_search($this->cible, $tagUrls);
							$this->cibleName=$tags[$key];
						}
					}
				}
			}
			if(sizeof($ids)>0) {
				$this->mode = 'tags'; # Affichage en mode home
				$this->template = 'tags.php';
				$this->motif = '#('.implode('|', $ids).').(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.\d{12}.[\w-]+.xml$#';
				$this->bypage = $this->aConf['bypage_tags']; # Nombre d'article par page
			} else {
				$this->error404(L_ARTICLE_NO_TAG);
			}
		}
		elseif($this->get AND preg_match('#^preview\/?#',$this->get) AND isset($_SESSION['preview'])) {
			$this->mode = 'preview';
		}
		elseif($this->get AND preg_match('#^(telechargement|download)\/(.+)$#',$this->get,$capture)) {
			if($this->sendTelechargement($capture[2])) {
				$this->mode = 'telechargement'; # Mode telechargement
				$this->cible = $capture[2];
			} else {
				$this->error404(L_DOCUMENT_NOT_FOUND);
			}
		}
		else {
			$this->error404(L_ERR_PAGE_NOT_FOUND);
		}

		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorPreChauffageEnd'));
	}

	/**
	 * Méthode qui fait une redirection de type 301
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function redir301($url) {
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorRedir301'));
		# Redirection 301
		header('Status: 301 Moved Permanently', false, 301);
		header('Location: '.$url);
		exit();
	}

	/**
	 * Méthode qui retourne une erreur 404 Document non trouvé
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function error404($msg) {
		header("Status: 404 Not Found");
		header("HTTP/1.0 404 Not Found");
		$this->plxErreur = new plxErreur($msg);
		$this->mode = 'erreur';
		$this->template = 'erreur.php';
	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stephane F, J.P. Pourrez, T. Ingles
	 **/
	public function demarrage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorDemarrageBegin'))) return;

		if(in_array($this->mode, array('home', 'categorie', 'archives', 'tags'))) {
			$_SESSION['previous'] = array(
				'mode'	=> $this->mode,
				'cible'	=> $this->cible,
				'motif'	=> $this->motif,
				'tri'	=> $this->tri,
			);
			$this->getPage(); # Recuperation du numéro de la page courante
			if(!$this->getArticles()) { # Si aucun article
				$this->error404(L_NO_ARTICLE_PAGE);
			}
		}
		elseif($this->mode == 'article') {

			# On a validé le formulaire commentaire
			if(!empty($_POST) AND $this->plxRecord_arts->f('allow_com') AND $this->aConf['allow_com']) {
				# On récupère le retour de la création
				$retour = $this->newCommentaire($this->cible, plxUtils::unSlash($_POST));
				# Url de l'article
				$url = $this->urlRewrite('?article'.intval($this->plxRecord_arts->f('numero')).'/'.$this->plxRecord_arts->f('url'));
				eval($this->plxPlugins->callHook('plxMotorDemarrageNewCommentaire')); # Hook Plugins
				if($retour[0] == 'c') { # Le commentaire a été publié
					$_SESSION['msgcom'] = L_COM_PUBLISHED;
					header('Location: '.$url.'#'.$retour);
				} elseif($retour == 'mod') { # Le commentaire est en modération
					$_SESSION['msgcom'] = L_COM_IN_MODERATION;
					header('Location: '.$url.'#form');
				} else {
					$_SESSION['msgcom'] = $retour;
					$_SESSION['msg']['name'] = plxUtils::unSlash($_POST['name']);
					$_SESSION['msg']['site'] = plxUtils::unSlash($_POST['site']);
					$_SESSION['msg']['mail'] = plxUtils::unSlash($_POST['mail']);
					$_SESSION['msg']['content'] = plxUtils::unSlash($_POST['content']);
					$_SESSION['msg']['parent'] = plxUtils::unSlash($_POST['parent']);
					eval($this->plxPlugins->callHook('plxMotorDemarrageCommentSessionMessage')); # Hook Plugins
					header('Location: '.$url.'#form');
				}
				exit;
			}
			# Récupération des commentaires
			$this->getCommentaires('#^'.$this->cible.'.\d{10}-\d+.xml$#',$this->tri_coms);
			$this->template=$this->plxRecord_arts->f('template');
			if($this->aConf['capcha']) $this->plxCapcha = new plxCapcha(); # Création objet captcha

			# Gestion des articles précédent, suivant, dans le mode précèdent (home, categorie, archives, tags)
			if(!empty($_SESSION['previous'])) {
				# On récupère un tableau indexé des articles
				$aFiles = $this->plxGlob_arts->query($_SESSION['previous']['motif'], 'art', $_SESSION['previous']['tri'], 0, false, 'before');
				$artIds = array();
				if($aFiles) {
					foreach($aFiles as $key=>$value) {
						if(substr($value, 0, 4) == $this->cible) {
							if($key > 0) {
								if($key > 1) { $artIds['first'] = $aFiles[0]; }
								$artIds['prev'] = $aFiles[$key - 1];
							}
							if($key < count($aFiles) - 1) {
								if($key < count($aFiles) - 2) { $artIds['last'] = $aFiles[count($aFiles) - 1]; }
								$artIds['next'] = $aFiles[$key + 1];
							}
							$_SESSION['previous']['position'] = $key + 1;
							$_SESSION['previous']['count'] = count($aFiles);
							break;
						}
					}
				}
				$_SESSION['previous']['artIds'] = $artIds;
			}
		}
		elseif($this->mode == 'preview') {
			$this->mode='article';
			$this->plxRecord_arts = new plxRecord($_SESSION['preview']);
			$this->template=$this->plxRecord_arts->f('template');
			if($this->aConf['capcha']) $this->plxCapcha = new plxCapcha(); # Création objet captcha
		}

		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorDemarrageEnd'));
	}

	/**
	 * Méthode qui parse le fichier de configuration et alimente
	 * le tableau aConf
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function getConfiguration($filename) {

		# valeurs variables par défaut
		$root = dirname(PLX_CONFIG_PATH) . '/';
		if($root != 'data') {
			foreach(array('medias', 'racine_articles', 'racine_commentaires', 'racine_statiques') as $k) {
				$this->aConf[$k] = preg_replace('@^data/@', $root, $this->aConf[$k]);
			}
		}

		# Mise en place du parseur XML
		if(!empty($filename) and file_exists($filename)) {
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "parametre"
			if(isset($iTags['parametre'])) {
				# On compte le nombre de tags "parametre"
				$nb = sizeof($iTags['parametre']);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					if(isset($values[ $iTags['parametre'][$i] ]['value'])) # On a une valeur pour ce parametre
						$this->aConf[ $values[ $iTags['parametre'][$i] ]['attributes']['name'] ] = $values[ $iTags['parametre'][$i] ]['value'];
					else # On n'a pas de valeur
						$this->aConf[ $values[ $iTags['parametre'][$i] ]['attributes']['name'] ] = '';
				}
			}
		}

		# détermination automatique de la racine du site
		$this->aConf['racine'] = plxUtils::getRacine();

		if(!defined('PLX_PLUGINS')) define('PLX_PLUGINS', PLX_ROOT . $this->aConf['racine_plugins']);
	}

	/**
	 * Méthode qui parse le fichier des catégories et alimente
	 * le tableau aCats
	 *
	 * @param	filename	emplacement du fichier XML des catégories
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function getCategories($filename) {

		if(defined('PLX_INSTALLER')) {
			$this->aCats['001'] = array(
			);
			return;
		}

		if(!is_file($filename)) return;

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);

		$activeCats = array('000'); # on rajoute la catégorie 'Non classée'
		$homepageCats = array('000'); # on rajoute la catégorie 'Non classée'

		if(isset($iTags['categorie']) AND isset($iTags['name'])) {
			$nb = sizeof($iTags['name']);
			$size=ceil(sizeof($iTags['categorie'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['categorie'][$i*$size]]['attributes'];
				$number = $attributes['number'];

				$title_htmltag = plxUtils::getValue($iTags['title_htmltag'][$i]);
				$meta_description = plxUtils::getValue($iTags['meta_description'][$i]);
				$meta_keywords = plxUtils::getValue($iTags['meta_keywords'][$i]);

				$homepage = isset($attributes['homepage']) ? $attributes['homepage'] : '1';
				if($homepage == '1') { $homepageCats[] = $number; }

				$active = isset($attributes['active']) ? $attributes['active'] : 1;
				if($active == '1') { $activeCats[] = $number; }

				# non-régression pour PluXml < 5.3.1
				$thumbnail = plxUtils::getValue($iTags['thumbnail'][$i]);
				$thumbnail_title = plxUtils::getValue($iTags['thumbnail_title'][$i]);
				$thumbnail_alt = plxUtils::getValue($iTags['thumbnail_alt'][$i]);

				$this->aCats[$number] = array(
					'name'				=> plxUtils::getValue($values[$iTags['name'][$i]]['value'], 'cat-' . $number), # nom de la catégorie
					'description'		=> plxUtils::getValue($values[$iTags['description'][$i]]['value']), # nom de la description
					'title_htmltag'		=> plxUtils::getValue($values[$title_htmltag]['value']),  # balise title
					'meta_description'	=> plxUtils::getValue($values[$meta_description]['value']), #meta description
					'meta_keywords'		=> plxUtils::getValue($values[$meta_keywords]['value']), # meta keywords
					'url'				=> strtolower($attributes['url']), # url de la categorie
					'tri'				=> isset($attributes['tri']) ? $attributes['tri'] : $this->aConf['tri'], # tri de la categorie si besoin est
					'bypage'			=> isset($attributes['bypage']) ? $attributes['bypage'] : $this->bypage, # nb d'articles par page de la categorie si besoin est
					'template'			=> isset($attributes['template']) ? $attributes['template'] : 'categorie.php', # fichier template
					'menu'				=> isset($attributes['menu']) ? $attributes['menu'] : 'oui', # état affichage de la catégorie dans le menu
					'active'			=> $active, # activation de la catégorie dans le menu
					'homepage'			=> $homepage, # affichage en page d'accueil
					'articles'			=> 0,
					# Non-régression pour PluXml < 5.8.1 - informations de l'image représentant la catégorie.
					'thumbnail'			=> plxUtils::getValue($values[$thumbnail]['value']),
					'thumbnail_title'	=> plxUtils::getValue($values[$thumbnail_title]['value']),
					'thumbnail_alt'		=> plxUtils::getValue($values[$thumbnail_alt]['value']),
				);

				# Recuperation du nombre d'article de la categorie
				$motif = "#^\d{4}\.(?:home,|\d{3},)*$number(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#";
				$arts = $this->plxGlob_arts->query($motif,'art','',0,false,'before');
				if(!empty($arts)) { $this->aCats[$number]['articles'] = sizeof($arts); }

				# Hook plugins
				eval($this->plxPlugins->callHook('plxMotorGetCategories'));
			}
		}

		$this->homepageCats = implode('|', $homepageCats);
		$this->activeCats = implode('|', $activeCats);
	}

	/**
	 * Méthode qui parse le fichier des pages statiques et alimente
	 * le tableau aStats
	 *
	 * @param	filename	emplacement du fichier XML des pages statiques
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function getStatiques($filename) {

		if(!is_file($filename)) return;

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		if(isset($iTags['statique']) AND isset($iTags['name'])) {
			$nb = sizeof($iTags['name']);
			$size=ceil(sizeof($iTags['statique'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['statique'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				# Récupération du nom de la page statique
				$this->aStats[$number]['name']=plxUtils::getValue($values[$iTags['name'][$i]]['value']);
				# Récupération de la balise title
				$title_htmltag = plxUtils::getValue($iTags['title_htmltag'][$i]);
				$this->aStats[$number]['title_htmltag']=plxUtils::getValue($values[$title_htmltag]['value']);
				# Récupération du meta description
				$meta_description = plxUtils::getValue($iTags['meta_description'][$i]);
				$this->aStats[$number]['meta_description']=plxUtils::getValue($values[$meta_description]['value']);
				# Récupération du meta keywords
				$meta_keywords = plxUtils::getValue($iTags['meta_keywords'][$i]);
				$this->aStats[$number]['meta_keywords']=plxUtils::getValue($values[$meta_keywords]['value']);
				# Récupération du groupe de la page statique
				$this->aStats[$number]['group']=plxUtils::getValue($values[$iTags['group'][$i]]['value']);
				# Récupération de l'url de la page statique
				$this->aStats[$number]['url']=strtolower($attributes['url']);
				# Récupération de l'etat de la page
				$this->aStats[$number]['active']=intval($attributes['active']);
				# On affiche la page statique dans le menu ?
				$this->aStats[$number]['menu']=isset($attributes['menu'])?$attributes['menu']:'oui';
				# Récupération du fichier template
				$this->aStats[$number]['template']=isset($attributes['template'])?$attributes['template']:'static.php';
				# Récupération de la date de création
				$date_creation = plxUtils::getValue($iTags['date_creation'][$i]);
				$this->aStats[$number]['date_creation']=plxUtils::getValue($values[$date_creation]['value']);
				# Récupération de la date de mise à jour
				$date_update = plxUtils::getValue($iTags['date_update'][$i]);
				$this->aStats[$number]['date_update']=plxUtils::getValue($values[$date_update]['value']);
				# On verifie que la page statique existe bien
				$file = PLX_ROOT.$this->aConf['racine_statiques'].$number.'.'.$attributes['url'].'.php';
				# On test si le fichier est lisible
				$this->aStats[$number]['readable'] = (is_readable($file) ? 1 : 0);
				# Hook plugins
				eval($this->plxPlugins->callHook('plxMotorGetStatiques'));
			}
		}
	}

	/**
	 * Méthode qui parse le fichier des utilisateurs
	 *
	 * @param	filename	emplacement du fichier XML des passwd
	 * @return	array		tableau des utilisateurs
	 * @author	Stephane F
	 **/
	public function getUsers($filename) {

		if(!is_file($filename)) return;

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);

		if(isset($iTags['user']) AND isset($iTags['login'])) {
			$nb = sizeof($iTags['login']);
			$size=ceil(sizeof($iTags['user'])/$nb);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				$attributes = $values[$iTags['user'][$i*$size]]['attributes'];
				$number = $attributes['number'];

				$salt = plxUtils::getValue($iTags['salt'][$i]);
				$email = plxUtils::getValue($iTags['email'][$i]);
				$password_token = plxUtils::getValue($iTags['password_token'][$i]);
				$password_token_expiry = plxUtils::getValue($iTags['password_token_expiry'][$i]);

				$this->aUsers[$number] = array(
					'active'			=> $attributes['active'],
					'delete'			=> $attributes['delete'],
					'profil'			=> $attributes['profil'],
					'login'				=> plxUtils::getValue($values[$iTags['login'][$i]]['value']),
					'name'				=> plxUtils::getValue($values[$iTags['name'][$i]]['value']),
					'password'			=> plxUtils::getValue($values[$iTags['password'][$i]]['value']),
					'salt'				=> plxUtils::getValue($values[$salt]['value']),
					'infos'				=> plxUtils::getValue($values[$iTags['infos'][$i]]['value']),
					'email'				=> plxUtils::getValue($values[$email]['value']),
					'lang'				=> isset($iTags['lang'][$i]) ? $values[$iTags['lang'][$i]]['value'] : $this->aConf['default_lang'],
					'password_token'	=> plxUtils::getValue($values[$password_token]['value']),
					'password_token_expiry'	=> plxUtils::getValue($values[$password_token_expiry]['value']),
				);

				# Hook plugins
				eval($this->plxPlugins->callHook('plxMotorGetUsers'));
			}
		}
	}

	/**
	 * Méthode qui selon le paramètre tri retourne sort ou rsort (tri PHP)
	 *
	 * @param	tri	asc ou desc
	 * @return	string
	 * @author	Stéphane F.
	 **/
	protected function mapTri($tri) { /* obsolete ! 2017-12-03 */

		if($tri=='desc')
			return 'rsort';
		elseif($tri=='asc')
			return 'sort';
		elseif($tri=='alpha')
			return 'alpha';
		elseif($tri=='ralpha')
			return 'ralpha';
		elseif($tri=='random')
			return 'random';
		else
			return 'rsort';

	}

	/**
	 * Méthode qui récupère le numéro de la page active
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	protected function getPage() {

		# On check pour avoir le numero de page
		if(preg_match('#page(\d*)#',$this->get,$capture))
			$this->page = $capture[1];
		else
			$this->page = 1;
	}

	/**
	 * Méthode qui récupere la liste des  articles
	 *
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	boolean	vrai si articles trouvés, sinon faux
	 * @author	Stéphane F
	 **/
	public function getArticles($publi='before') {

		# On calcule la valeur start
		$start = $this->bypage*($this->page-1);
		# On recupere nos fichiers (tries) selon le motif, la pagination, la date de publication
		if($aFiles = $this->plxGlob_arts->query($this->motif,'art',$this->tri,$start,$this->bypage,$publi)) {
			# on mémorise le nombre total d'articles trouvés
			foreach($aFiles as $k=>$v) # On parcourt tous les fichiers
				$array[$k] = $this->parseArticle(PLX_ROOT.$this->aConf['racine_articles'].$v);
			# On stocke les enregistrements dans un objet plxRecord
			$this->plxRecord_arts = new plxRecord($array);
			return true;
		}
		else return false;
	}

	/**
	 * Méthode qui retourne les informations $output en analysant
	 * le nom du fichier de l'article $filename
	 *
	 * @param	filename	fichier de l'article à traiter
	 * @return	array		information à récupérer
	 * @author	Stephane F
	 **/
	public function artInfoFromFilename($filename) {

		# On effectue notre capture d'informations
		if(preg_match('#(_?\d{4})\.([\d,|home|draft]*)\.(\d{3})\.(\d{12})\.([\w-]+)\.xml$#',$filename,$capture)) {
			return array(
				'artId'		=> $capture[1],
				'catId'		=> $capture[2],
				'usrId'		=> $capture[3],
				'artDate'	=> $capture[4],
				'artUrl'	=> $capture[5]
			);
		}
	}

	/**
	 * Méthode qui parse l'article du fichier $filename
	 *
	 * @param	filename	fichier de l'article à parser
	 * @return	array
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function parseArticle($filename) {

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# Recuperation des valeurs de nos champs XML
		$art['title'] = plxUtils::getValue($values[$iTags['title'][0]]['value']);
		$art['allow_com'] = plxUtils::getValue($values[$iTags['allow_com'][0]]['value']);
		$art['template'] = plxUtils::getValue($values[$iTags['template'][0]]['value'],'article.php');
		$art['chapo'] = plxUtils::getValue($values[$iTags['chapo'][0]]['value']);
		$art['content'] = plxUtils::getValue($values[$iTags['content'][0]]['value']);
		$art['tags'] = plxUtils::getValue($values[ $iTags['tags'][0] ]['value']);
		$meta_description = plxUtils::getValue($iTags['meta_description'][0]);
		$art['meta_description'] = plxUtils::getValue($values[$meta_description]['value']);
		$meta_keywords = plxUtils::getValue($iTags['meta_keywords'][0]);
		$art['meta_keywords'] = plxUtils::getValue($values[$meta_keywords]['value']);
		$art['title_htmltag'] = isset($iTags['title_htmltag']) ? plxUtils::getValue($values[$iTags['title_htmltag'][0]]['value']) : '';
		$art['thumbnail'] = isset($iTags['thumbnail']) ? plxUtils::getValue($values[$iTags['thumbnail'][0]]['value']) : '';
		$art['thumbnail_title'] = isset($iTags['thumbnail_title']) ? plxUtils::getValue($values[$iTags['thumbnail_title'][0]]['value']) : '';
		$art['thumbnail_alt'] = isset($iTags['thumbnail_alt']) ? plxUtils::getValue($values[$iTags['thumbnail_alt'][0]]['value']) : '';
		# Informations obtenues en analysant le nom du fichier
		$art['filename'] = $filename;
		$tmp = $this->artInfoFromFilename($filename);
		$art['numero'] = $tmp['artId'];
		$art['author'] = $tmp['usrId'];
		$art['categorie'] = $tmp['catId'];
		$art['url'] = $tmp['artUrl'];
		$art['date'] = $tmp['artDate'];
		$art['nb_com'] = $this->getNbCommentaires('#^'.$art['numero'].'.\d{10}.\d+.xml$#');
		$art['date_creation'] = isset($iTags['date_creation']) ? plxUtils::getValue($values[$iTags['date_creation'][0]]['value']) : $art['date'];
		$art['date_update'] = isset($iTags['date_update']) ? plxUtils::getValue($values[$iTags['date_update'][0]]['value']) : $art['date'];
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorParseArticle'));
		# On retourne le tableau
		return $art;
	}

	/**
	 * Méthode qui retourne le nombre de commentaires respectants le motif $motif et le paramètre $publi
	 *
	 * @param	motif	motif de recherche des commentaires
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	integer
	 * @author	Florent MONTHEL
	 **/
	public function getNbCommentaires($motif,$publi='before') {

		if($coms = $this->plxGlob_coms->query($motif,'com','',0,false,$publi))
			return sizeof($coms);
		else
			return 0;
	}

	/**
	 * Méthode qui retourne les informations $output en analysant
	 * le nom du fichier du commentaire $filename
	 *
	 * @param	filename	fichier du commentaire à traiter
	 * @return	array		information à récupérer
	 * @author	Stephane F
	 **/
	public function comInfoFromFilename($filename) {
		# On effectue notre capture d'informations
		if(preg_match('#([[:punct:]]?)(\d{4}).(\d{10})-(\d+).xml$#',$filename,$capture)) {
			return array(
				'comStatus'	=> $capture[1],
				'artId'		=> $capture[2],
				'comDate'	=> plxDate::timestamp2Date($capture[3]),
				'comId'		=> $capture[3].'-'.$capture[4],
				'comIdx'	=> $capture[4],

			);
		}
		return false;
	}

	/**
	 * Méthode qui parse le commentaire du fichier $filename
	 *
	 * @param	filename	fichier du commentaire à parser
	 * @return	array
	 * @author	Florent MONTHEL
	 **/
	public function parseCommentaire($filename) {

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# Recuperation des valeurs de nos champs XML
		$com['author'] = plxUtils::getValue($values[ $iTags['author'][0]]['value']);
		if(isset($iTags['type']))
			$com['type'] = plxUtils::getValue($values[ $iTags['type'][0]]['value'],'normal');
		else
			$com['type'] = 'normal';
		$com['ip'] = plxUtils::getValue($values[$iTags['ip'][0]]['value']);
		$com['mail'] = plxUtils::getValue($values[$iTags['mail'][0]]['value']);
		$com['site'] = plxUtils::getValue($values[$iTags['site'][0]]['value']);
		$com['content'] = trim($values[ $iTags['content'][0] ]['value']);
		$com['parent'] = isset($iTags['parent'])?plxUtils::getValue($values[$iTags['parent'][0]]['value']):'';
		# Informations obtenues en analysant le nom du fichier
		$tmp = $this->comInfoFromFilename(basename($filename));
		$com['status'] = $tmp['comStatus'];
		$com['numero'] = $tmp['comId'];
		$com['article'] = $tmp['artId'];
		$com['date'] = $tmp['comDate'];
		$com['index'] = $tmp['comIdx'];
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorParseCommentaire'));
		# On retourne le tableau
		return $com;
	}

	/**
	 * Méthode qui trie récursivement les commentaires d'un article en fonction des parents
	 *
	 * @return	array	liste des commentaires triés
	 * @author	Stéphane F.
	 **/
	public function parentChildSort_r($idField, $parentField, $els, $parentID = 0, &$result = array(), &$level = 0){
		foreach ($els as $key => $value) {
			if ($value[$parentField] == $parentID) {
				$value['level'] = $level;
				array_push($result, $value);
				unset($els[$key]);
				$oldParent = $parentID;
				$parentID = $value[$idField];
				$level++;
				$this->parentChildSort_r($idField,$parentField, $els, $parentID, $result, $level);
				$parentID = $oldParent;
				$level--;
			}
		}
		return $result;
	}

	/**
	 * Méthode qui enregistre dans un objet plxRecord tous les commentaires
	 * respectant le motif $motif et la limite $limite
	 *
	 * @param	motif	motif de recherche des commentaires
	 * @param	ordre	ordre du tri : sort ou rsort
	 * @param	start	commencement
	 * @param	limite	nombre de commentaires à retourner
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	bool	true if there is comments else false
	 * @author	Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
	 **/
	public function getCommentaires($motif,$ordre='sort',$start=0,$limite=false,$publi='before') {

		# On récupère les fichiers des commentaires
		$aFiles = $this->plxGlob_coms->query($motif,'com',$ordre,$start,$limite,$publi);
		if($aFiles) { # On a des fichiers
			foreach($aFiles as $k=>$v) {
				$array[$k] = $this->parseCommentaire(PLX_ROOT.$this->aConf['racine_commentaires'].$v);
			}

			# hiérarchisation et indentation des commentaires seulement sur les écrans requis
			if (!preg_match('#comments?\.php#',basename($_SERVER['SCRIPT_NAME']))) {
				$array = $this->parentChildSort_r('index', 'parent', $array);
			}

			# On stocke les enregistrements dans un objet plxRecord
			$this->plxRecord_coms = new plxRecord($array);

		}

		return !empty($aFiles);
	}

	/**
	 *  Méthode qui retourne le prochain id d'un commentaire pour un article précis
	 *
	 * @param	idArt		id de l'article
	 * @return	string		id d'un nouveau commentaire
	 * @author	Stephane F.
	 **/
	 public function nextIdArtComment($idArt) {

		$ret = '0';
		if($dh = opendir(PLX_ROOT.$this->aConf['racine_commentaires'])) {
			$Idxs = array();
			while(false !== ($file = readdir($dh))) {
				if(preg_match("/_?".$idArt.".\d+-(\d+).xml/", $file, $capture)) {
					if ($capture[1] > $ret)
						$ret = $capture[1];
				}
			}
			closedir($dh);
		}
		return $ret+1;
	}

	/**
	 * Méthode qui crée un nouveau commentaire pour l'article $artId
	 *
	 * @param	artId	identifiant de l'article en question
	 * @param	content	tableau contenant les valeurs du nouveau commentaire
	 * @return	string
	 * @author	Florent MONTHEL, Stéphane F, J.P. Pourrez
	 **/
	public function newCommentaire($artId, $content) {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorNewCommentaire'))) return;

		if(
			!empty($this->aConf['capcha']) AND (
				empty($_SESSION['capcha_token']) OR
				empty($_POST['capcha_token']) or
				($_SESSION['capcha_token'] != $_POST['capcha_token'])
			)
		) {
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}

		# On vérifie que le capcha est correct
		if($this->aConf['capcha'] == 0 OR $_SESSION['capcha'] == sha1($content['rep'])) {
			if(!empty($content['name']) AND !empty($content['content'])) { # Les champs obligatoires sont remplis
				$comment=array();
				$comment['type'] = 'normal';
				$comment['author'] = plxUtils::strCheck(trim($content['name']));
				$comment['content'] = plxUtils::strCheck(trim($content['content']));
				# On vérifie le mail
				$comment['mail'] = (plxUtils::checkMail(trim($content['mail'])))?trim($content['mail']):'';
				# On vérifie le site
				$comment['site'] = (plxUtils::checkSite($content['site'])?$content['site']:'');
				# On récupère l'adresse IP du posteur
				$comment['ip'] = plxUtils::getIp();
				# index du commentaire
				$idx = $this->nextIdArtComment($artId);
				# Commentaire parent en cas de réponse
				if(isset($content['parent']) AND !empty($content['parent'])) {
					$comment['parent'] = intval($content['parent']);
				} else {
					$comment['parent'] = '';
				}
				# On génère le nom du fichier
				$time = time();
				if($this->aConf['mod_com']) # On modère le commentaire => underscore
					$comment['filename'] = '_'.$artId.'.'.$time.'-'.$idx.'.xml';
				else # On publie le commentaire directement
					$comment['filename'] = $artId.'.'.$time.'-'.$idx.'.xml';
				# On peut créer le commentaire
				if($this->addCommentaire($comment)) { # Commentaire OK
					if($this->aConf['mod_com']) # En cours de modération
						return 'mod';
					else # Commentaire publie directement, on retourne son identifiant
						return 'c'.$artId.'-'.$idx;
				} else { # Erreur lors de la création du commentaire
					return L_NEWCOMMENT_ERR;
				}
			} else { # Erreur de remplissage des champs obligatoires
				return L_NEWCOMMENT_FIELDS_REQUIRED;
			}
		} else { # Erreur de vérification capcha
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}
	}

	/**
	 * Méthode qui crée physiquement le fichier XML du commentaire
	 *
	 * @param	comment	array avec les données du commentaire à ajouter
	 * @return	boolean
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Stéphane F
	 **/
	public function addCommentaire($content) {
		if(!empty($this->plxPlugins)) {
			# Hook plugins
			if(eval($this->plxPlugins->callHook('plxMotorAddCommentaire'))) return;
		}

		# On genere le contenu de notre fichier XML
		ob_start();
?>
<comment>
	<author><?= plxUtils::cdataCheck($content['author']) ?></author>
	<type><?= $content['type'] ?></type>
	<ip><?= $content['ip'] ?></ip>
	<mail><?= plxUtils::cdataCheck($content['mail']) ?></mail>
	<site><?= plxUtils::cdataCheck($content['site']) ?></site>
	<content><?= plxUtils::cdataCheck($content['content']) ?></content>
	<parent><?= plxUtils::cdataCheck($content['parent']) ?></parent>
<?php
		if(!empty($this->plxPlugins)) {
			# Hook plugins
			$xml = '';
			eval($this->plxPlugins->callHook('plxMotorAddCommentaireXml'));
			if(!empty($xml)) {
				echo $xml;
			}
		}

?>
</comment>
<?php
		# On ecrit ce contenu dans notre fichier XML
		return plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT . $this->aConf['racine_commentaires'] . $content['filename']);
	}

	/**
	 * Méthode qui parse le fichier des tags et alimente
	 * le tableau aTags
	 *
	 * @param	filename	emplacement du fichier XML contenant les tags
	 * @return	null
	 * @author	Stephane F., J.P. Pourrez (bazooka07)
	 **/
	public function getTags($filename) {

		if(!is_file($filename)) return;

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		$array = array();
		# On verifie qu'il existe des tags "file"
		if(isset($iTags['article'])) {
			# On compte le nombre de tags "file"
			foreach($iTags['article'] as $k) {
				$datas = $values[$k];
				$idArt = $datas['attributes']['number'];
				$array[$idArt] = array(
					'tags'		=> (!empty($datas['value'])) ? trim($datas['value']) : '',
					'date'		=> $datas['attributes']['date'],
					'active'	=> $datas['attributes']['active'],
				);
			}
		}
		# Mémorisation de la liste des tags
		$this->aTags = $array;
	}

	/**
	 * Méthode qui alimente le tableau aTemplate
	 *
	 * @param	string	dossier contenant les templates
	 * @return	null
	 * @author	Pedro "P3ter" CADETE
	 **/
	public function getTemplates($templateFolder) {
		if(is_dir($templateFolder)) {
			$files = array_diff(scandir($templateFolder), array('..', '.'));
			if (!empty($files)) {
				foreach ($files as $file) {
					$this->aTemplates[$file] = new PlxTemplate($templateFolder, $file);
				}
			}
		}
	}

	/**
	 * Méthode qui lance le téléchargement d'un document
	 *
	 * @param	cible	cible de téléchargement cryptée
	 * @return	boolean
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function sendTelechargement($cible) {

		# On décrypte le nom du fichier
		$file = PLX_ROOT.$this->aConf['medias'].plxEncrypt::decryptId($cible);
		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorSendDownload'))) return;
		# On lance le téléchargement et on check le répertoire medias
		if(file_exists($file) AND preg_match('#^'.str_replace('\\', '#', realpath(PLX_ROOT.$this->aConf['medias']).'#'), str_replace('\\', '/', realpath($file)))) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: no-cache');
			header('Content-Length: '.filesize($file));
			readfile($file);
			exit;
		} else { # On retourne false
			return false;
		}

	}

	/**
	 * Méthode qui réécrit les urls pour supprimer le ?
	 *
	 * @param	url		url à réécrire
	 * @return	string	url réécrite
	 * @author	Stéphane F, J.P. Pourrez
	 **/
	public function urlRewrite($url='') {

		# On teste si $url est une adresse absolue ou une image embarquée
		if(!empty(trim($url)) and preg_match('@^(?:https?|data):@', $url)) {
			return $url;
		}

		if($url=='' OR $url=='?') return $this->racine;

		preg_match('#^([0-9a-z\_\-\.\/]+)?[\?]?([0-9a-z\_\-\.\/,&=%]+)?[\#]?(.*)$#i', $url, $args);

		if($this->aConf['urlrewriting']) {
			$new_url  = str_replace('index.php', '', $args[1]);
			$new_url  = str_replace('feed.php', 'feed/', $new_url);
			$new_url .= !empty($args[2])?$args[2]:'';
			if(empty($new_url))	$new_url = $this->path_url;
			$new_url .= !empty($args[3])?'#'.$args[3]:'';
			return str_replace('&', '&amp;', $this->racine.$new_url);
		} else {
			if(empty($args[1]) AND !empty($args[2])) $args[1] = 'index.php';
			$new_url  = !empty($args[1])?$args[1]:$this->path_url;
			$new_url .= !empty($args[2])?'?'.$args[2]:'';
			$new_url .= !empty($args[3])?'#'.$args[3]:'';
			return $this->racine.$new_url;
		}
	}

	/**
	 * Méthode qui comptabilise le nombre d'articles du site.
	 *
	 * @param	select	critere de recherche: draft, published, all, n° categories séparés par un |
	 * @param	userid	filtre sur les articles d'un utilisateur donné
	 * @param	mod		filtre sur les articles en attente de validation
	 * @param	publi	selection en fonciton de la date du jour (all, before, after)
	 * @return	integer	nombre d'articles
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function nbArticles($select='all', $userId='\d{3}', $mod='_?', $publi='all') {

		$nb = 0;
		if($select == 'all')
			$motif = '[home|draft|0-9,]*';
		elseif($select=='published')
			$motif = '[home|0-9,]*';
		elseif($select=='draft')
			$motif = '[\w,]*[draft][\w,]*';
		else
			$motif = $select;

		if($arts = $this->plxGlob_arts->query('#^'.$mod.'\d{4}.('.$motif.').'.$userId.'.\d{12}.[\w-]+.xml$#', 'art', '', 0, false, $publi))
			$nb = sizeof($arts);

		return $nb;
	}

	/**
	 * Méthode qui comptabilise le nombre de commentaires du site
	 *
	 * @param	select	critere de recherche des commentaires: all, online, offline
	 * @param	publi	type de sélection des commentaires: all, before, after
	 * @return	integer	nombre de commentaires
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function nbComments($select='online', $publi='all') {

		$nb = 0;
		if($select == 'all')
			$motif = '#[^[:punct:]?]\d{4}.(.*).xml$#';
		elseif($select=='offline')
			$motif = '#^_\d{4}.(.*).xml$#';
		elseif($select=='online')
			$motif = '#^\d{4}.(.*).xml$#';
		else
			$motif = $select;

		if($coms = $this->plxGlob_coms->query($motif,'com','',0,false,$publi))
			$nb = sizeof($coms);

		return $nb;
	}

	/**
	 * Méthode qui recherche les articles appartenant aux catégories actives
	 *
	 * @return	null
	 * @scope	global
	 * @author	Stéphane F.
	 **/
	public function getActiveArts() {
		if($this->plxGlob_arts->aFiles) {
			$datetime=date('YmdHi');
			foreach($this->plxGlob_arts->aFiles as $filename) {
				if(preg_match('#^(\d{4}).(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.(\d{12}).[\w-]+.xml$#', $filename, $capture)) {
					if($capture[2]<=$datetime) { # on ne prends que les articles publiés
						$this->activeArts[$capture[1]]=1;
					}
				}
			}
		}
	}

}

<?php

if(!defined('PLX_ROOT')) {
	header('Content-Type:text/plain; charset=utf-8');
	exit('Undefined constant: PLX_ROOT');
}

/**
 * Classe plxMotor responsable du traitement global du script
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F, Pedro "P3ter" CADETE
 **/

class plxMotor {
	const PLX_TEMPLATES = PLX_CORE . 'templates/';
	const PLX_TEMPLATES_DATA = PLX_ROOT . 'data/templates/';

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
	public $aConf = DEFAULT_CONFIG;

	# Tableau de configuration. On gère la non régression depuis PluXml-5.1.7 en cas d'ajout de paramètres sur une version de pluxml déjà installée
	/*
	public $aConf = array(
		'enable_rss'=>1,
		'enable_rss_comment'=>1,
		'lostpassword'=>1,
		'bypage_tags'=>5,
		'thumbs'=>0,
		'medias'=>'data/medias/',
		'hometemplate'=>'home.php',
		'version'=>PLX_VERSION,
		'display_empty_cat'=>0,
		'custom_admincss_file'=>'',
		'email_method' => 'sendmail',
		'smtp_server' => '',
		'smtp_username' => '',
		'smtp_password' => '',
		'smtp_port' => '465',
		'smtp_security' => 'ssl',
		'smtpOauth2_emailAdress' => '',
		'smtpOauth2_clientId'=> '',
		'smtpOauth2_clientSecret' => '',
		'smtpOauth2_refreshToken' => '',
	);
	* */

		/*
		'bypage_admin'			=> 10,
		'tri'					=> 'desc'
		'bypage_admin_coms'		=> 10,
		'bypage_archives'		=> 5,
		'bypage_tags'			=> 5,
		'userfolders'			=> 0,
		'meta_description'		=> '',
		'meta_keywords'			=> '',
		'default_lang'			=> DEFAULT_LANG,
		'racine_plugins'		=> 'plugins/',
		'racine_themes'			=> 'themes/',
		'medias'				=> 'data/images/',
		'mod_art'				=> 0,
		'display_empty_cat'		=> 0,
		'timezone'				=> @date_default_timezone_get(),
		'thumbs'				=> 1,
		'hometemplate'			=> 'home.php',
		'custom_admincss_file'	=> '',
		'lostpassword'			=> : 1,
		'enable_rss'			=> 1,
		'enable_rss_comment'	=> 1,
		* */

	public $aCats = array(); # Tableau de toutes les catégories
	public $aStats = array(); # Tableau de toutes les pages statiques
	public $aTags = array(); # Tableau des tags
	public $aUsers = array(); # Tableau des utilisateurs
	public $aTemplates = null; # Tableau des templates

	public $plxGlob_arts = null; # Objet plxGlob des articles
	public $plxGlob_coms = null; # Objet plxGlob des commentaires
	public $plxRecord_arts = null; # Objet plxRecord des articles
	public $plxRecord_coms = null; # Objet plxRecord des commentaires
	public $plxCapcha = null; # Objet plxCapcha
	public $plxErreur = null; # Objet plxErreur
	public $plxPlugins = null; # Objet plxPlugins

	protected static $instance = null;

	/**
	 * Méthode qui se charger de créer le Singleton plxMotor
	 *
	 * @return	self			return une instance de la classe plxMotor
	 * @author	Stephane F
	 **/
	public static function getInstance(){
		if (empty(self::$instance)) {
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
		define('PLX_SITE_LANG', $this->aConf['default_lang']);

		# On vérifie s'il faut faire une mise à jour
		if(
			(
				!isset($this->aConf['version']) OR
				version_compare($this->aConf['version'], PLX_VERSION_DATA, '<')
			) AND
			!defined('PLX_UPDATER')
		) {
			if(defined('PLX_FEED')) {
				header('Content-Type: Text/Plain; charset=utf-8');
				exit('Available update');
			} else {
				header('Location: '.PLX_ROOT.'update/index.php');
				exit;
			}
		}

		# récupération des paramètres dans l'url
		$this->get = plxUtils::getGets();

		# gestion du timezone
		date_default_timezone_set($this->aConf['timezone']);

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
		if(defined('PLX_FEED')) {
			$lang = $this->aConf['default_lang'];
		} else {
			$context = defined('PLX_ADMIN') ? 'admin_lang' : 'lang';
			$lang = isset($_SESSION[$context]) ? $_SESSION[$context] : $this->aConf['default_lang'];
		}
		$this->plxPlugins = new plxPlugins($lang);
		$this->plxPlugins->loadPlugins();
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorConstructLoadPlugins'));

		# Traitement sur les répertoires des articles et des commentaires
		$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles']);
		$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires'], false, true, 'commentaires');

		# Récupération des données dans les autres fichiers xml
		$this->getCategories(path('XMLFILE_CATEGORIES'));
		$this->getStatiques(path('XMLFILE_STATICS'));
		$this->getTags(path('XMLFILE_TAGS'));
		$this->getUsers(path('XMLFILE_USERS'));

		# Récuperation des articles appartenant aux catégories actives
		$this->getActiveArts();

		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorConstruct'));

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

		if(!empty($this->get) and !preg_match('#^(?:blog|article\d{1,4}/|static\d{1,3}/|categorie\d{1,3}/|user\d{1,3}|archives/\d{4}(?:/\d{2})?|tag/\w|page\d+|preview|telechargement|download)#', $this->get)) {
			$this->get = '';
		}

		if(
			empty($this->get) AND
			!empty($this->aConf['homestatic']) AND
			isset($this->aStats[$this->aConf['homestatic']]) AND
			$this->aStats[$this->aConf['homestatic']]['active']
		) {
			$this->mode = 'static'; # Mode static
			$this->cible = $this->aConf['homestatic'];
			$this->template = $this->aStats[ $this->cible ]['template'];
		} elseif(
			empty($this->get) OR
			preg_match('#^(?:blog(?:/page\d+)?|page\d+)$#', $this->get)
		) {
			$this->mode = 'home';
			$this->template = $this->aConf['hometemplate'];
			$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			# On regarde si on a des articles en mode "home"
			$this->motif = '#^\d{4}\.(?:\d{3},|pin,)*home(,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
			if(!$this->plxGlob_arts->query($this->motif)) {
				# Sinon on recupère tous les articles
				$this->motif = '#^\d{4}\.(?:pin,|\d{3},)*(?:'.$this->homepageCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
			}
		} else {
			# $this->get not empty !
			if(preg_match('#^article(\d+)/?([\w-]+)?#',$this->get,$capture)) {
				$this->mode = 'article'; # Mode article
				$this->template = 'article.php';
				$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT); # On complete sur 4 caracteres
				$this->motif = '#^'.$this->cible.'\.(?:pin,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#'; # Motif de recherche
				if($this->getArticles()) {
					# Redirection 301
					if(!isset($capture[2]) OR $this->plxRecord_arts->f('url') != $capture[2]) {
						$this->redir301($this->urlRewrite('?article'.intval($this->cible).'/'.$this->plxRecord_arts->f('url')));
					}
				} else {
					$this->error404(L_UNKNOWN_ARTICLE);
				}
			} elseif(preg_match('#^static(\d+)/?([\w-]+)?#',$this->get,$capture)) {
				$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complète sur 3 caractères
				if(isset($this->aStats[$this->cible]) and $this->aStats[$this->cible]['active']) {
					if(!empty($this->aConf['homestatic']) AND $this->aConf['homestatic'] == $this->cible){
						$this->redir301($this->urlRewrite());
					} elseif(isset($capture[2]) AND $this->aStats[$this->cible]['url'] == $capture[2]) {
						$this->mode = 'static'; # Mode static
						$this->template = $this->aStats[$this->cible]['template'];
					} else {
						$this->redir301($this->urlRewrite('?static'.intval($this->cible).'/'.$this->aStats[$this->cible]['url']));
					}
				} else {
					$this->error404(L_UNKNOWN_STATIC);
				}
			} elseif(preg_match('#^categorie(\d+)/?([\w-]+)?#',$this->get,$capture)) {
				$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
				if(isset($this->aCats[$this->cible]) and $this->aCats[$this->cible]['active']) {
					if(isset($capture[2]) AND $this->aCats[$this->cible]['url'] == $capture[2]) {
						$this->mode = 'categorie'; # Mode categorie
						$this->motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*' . $this->cible . '(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#'; # Motif de recherche
						$this->template = $this->aCats[$this->cible]['template'];
						$this->tri = $this->aCats[$this->cible]['tri']; # Recuperation du tri des articles
						$this->bypage =  ($this->aCats[$this->cible]['bypage'] > 0) ? $this->aCats[$this->cible]['bypage'] : $this->aConf['bypage'];
					} else {
						# Redirection 301
						$this->redir301($this->urlRewrite('?categorie'.intval($this->cible).'/'.$this->aCats[$this->cible]['url']));
					}
				} else {
					$this->error404(L_UNKNOWN_CATEGORY);
				}
			} elseif(preg_match('#^user(\d+)/?(\w[\w+-]+)?#',$this->get,$capture)) {
				$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
				if(isset($this->aUsers[$this->cible]) and $this->aUsers[$this->cible]['active']) {
					$this->bypage = $this->aConf['bypage'];
					$urlName = plxUtils::urlify($this->aUsers[$this->cible]['name']);
					if(isset($capture[2]) AND $urlName == $capture[2]) {
						$this->mode = 'user'; # Mode user
						$this->motif = '#^\d{4}\.(?:pin,|\d{3},)*(?:' . $this->activeCats . ')(?:,\d{3})*\.' . $this->cible . '.\d{12}\.[\w-]+\.xml$#'; # Motif de recherche
						$this->template = 'user.php';
					} else {
						# Redirection 301
						$this->redir301($this->urlRewrite('?user' . intval($this->cible) . '/' . $urlName));
					}
				} else {
					$this->error404(L_UNKNOWN_AUTHOR);
				}
			} elseif(preg_match('#^tag/([\w-]+)#',$this->get,$capture)) {
				$this->cible = $capture[1];
				$this->tri = 'desc';
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
					$this->motif = '#(?:' . implode('|', $ids) . ')\.(?:pin,|\d{3},)*(?:' . $this->activeCats . ')(?:,\d{3})*\.\d{3}.\d{12}\.[\w-]+\.xml$#';
					$this->bypage = $this->aConf['bypage_tags']; # Nombre d'article par page
				} else {
					$this->error404(L_ARTICLE_NO_TAG);
				}
			} elseif(preg_match('#^archives\/(\d{4})[\/]?(\d{2})?[\/]?(\d{2})?#',$this->get,$capture)) {
				$this->mode = 'archives';
				$this->template = 'archives.php';
				$this->bypage = $this->aConf['bypage_archives'];
				$this->cible = $searchDate = $capture[1];
				if(!empty($capture[2])) {
					$this->cible = ($searchDate .= $capture[2]);
				} else {
					$searchDate .= '\d{2}';
				}
				$searchDate .= !empty($capture[3]) ? $capture[3] : '\d{2}';
				$this->motif = '#^\d{4}\.(?:pin,|\d{3},)*(?:' . $this->activeCats . ')(?:,\d{3})*\.\d{3}\.' . $searchDate . '\d{4}\.[\w-]+\.xml$#';
			} elseif(preg_match('#^preview\/?#',$this->get) AND isset($_SESSION['preview'])) {
				$this->mode = 'preview';
			} elseif(preg_match('#^(?:telechargement|download)/([^/]+)$#',$this->get,$capture)) {
				if($this->sendTelechargement($capture[1])) {
					$this->mode = 'telechargement'; # Mode telechargement
					$this->cible = $capture[1];
				} else {
					$this->error404(L_DOCUMENT_NOT_FOUND);
				}
			} else {
				$this->error404(L_ERR_PAGE_NOT_FOUND);
			}
		}

		# On vérifie l'existence du template
		$filename = $this->style . '/' . $this->template;
		if(!file_exists(PLX_ROOT . $this->aConf['racine_themes'] . $filename)) {
			$this->error404(L_ERR_FILE_NOTFOUND . ' ( <i>' . $filename . '</i> )');
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
		header('Status: 404 Not Found');
		header('HTTP/1.0 404 Not Found');
		$this->plxErreur = new plxErreur($msg);
		$this->mode = 'erreur';
		$this->template = 'erreur.php';
	}

	/**
	 * Méthode qui vérifie que les commentaires sont autorisés pour l'article courant
	 *
	 * @return	bool
	 * @author	Jean-Pierre Pourrez "bazooka07"
	 **/
	public function articleAllowComs() {
		return (
			$this->mode == 'article' and
			(
				intval($this->aConf['allow_com']) > 0 or
				(
					!empty($this->plxRecord_arts) and
					intval($this->plxRecord_arts->f('allow_com')) > 0
				)
			)
		);
	}

	/**
	 * Méthode qui vérifie si la publication d'un commentaire pour article est réservé aux abonnés
	 *
	 * @return	bool
	 * @author	Jean-Pierre Pourrez "bazooka07"
	 **/
	public function articleComLoginRequired() {
		return (
			$this->mode == 'article' and
			(
				intval($this->aConf['allow_com']) == 2 or
				(
					!empty($this->plxRecord_arts) and
					intval($this->plxRecord_arts->f('allow_com')) == 2
				)
			)
		);
	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function demarrage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorDemarrageBegin'))) return;

		if(in_array($this->mode, array('home', 'categorie', 'tags', 'user', 'archives'))) {
			$this->getPage(); # Recuperation du numéro de la page courante
			if(!$this->getArticles()) { # Si aucun article
				$this->error404(L_NO_ARTICLE_PAGE);
			}
		}
		elseif($this->mode == 'article') {

			# On a validé le formulaire commentaire
			if($this->articleAllowComs() and !empty($_POST)) {
				# On récupère le retour de la création
				$content = plxUtils::unSlash($_POST);
				$retour = $this->newCommentaire($this->cible, $content);
				unset($_SESSION['msg']);
				# Url de l'article
				$url = $this->urlRewrite('?article'.intval($this->plxRecord_arts->f('numero')).'/'.$this->plxRecord_arts->f('url'));
				eval($this->plxPlugins->callHook('plxMotorDemarrageNewCommentaire')); # Hook Plugins
				if(preg_match('~^c\d{4}-\d+~', $retour)) { # Le commentaire a été publié
					$_SESSION['msgcom'] = L_COM_PUBLISHED;
					header('Location: '.$url.'#'.$retour);
				} elseif($retour == 'mod') { # Le commentaire est en modération
					$_SESSION['msgcom'] = L_COM_IN_MODERATION;
					header('Location: '.$url.'#form');
				} else {
					$_SESSION['msgcom'] = $retour;
					$_SESSION['msg'] = $content;
					eval($this->plxPlugins->callHook('plxMotorDemarrageCommentSessionMessage')); # Hook Plugins
					header('Location: '.$url.'#form');
				}
				exit;
			}

			# Récupération des commentaires
			$this->getCommentaires('#^'.$this->cible.'\.\d{10}-\d+\.xml$#',$this->tri_coms);
			$this->template=$this->plxRecord_arts->f('template');
			if($this->aConf['capcha']) $this->plxCapcha = new plxCapcha(); # Création objet captcha
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

		# Mise en place du parseur XML
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
				$param = $values[$iTags['parametre'][$i]];
				$name = $param['attributes']['name'];
				$this->aConf[$name] = isset($param['value']) ? $param['value'] : '';
				if(preg_match('#^(?:bypage|image|miniature)#', $name)) {
					$this->aConf[$name] = intval($this->aConf[$name]);
				}
			}
		}

		# détermination automatique de la racine du site
		$this->aConf['racine'] = plxUtils::getRacine();

		if(!defined('PLX_PLUGINS')) define('PLX_PLUGINS', PLX_ROOT . $this->aConf['racine_plugins']);
		if(!defined('PLX_PLUGINS_CSS_PATH')) define('PLX_PLUGINS_CSS_PATH', preg_replace('@^([^/]+/).*@', '$1', $this->aConf['medias']));

		# valeurs non nulles requises pour ces champs :
		if(empty($this->aConf['timezone'])) {
			$this->aConf['timezone'] = @date_default_timezone_get();
		}
		if(empty($this->aConf['clef'])) {
			$this->aConf['clef'] = plxUtils::charAleatoire(15);
		}

		if (!isset($this->aConf['urlrewriting']) or $this->aConf['urlrewriting'] != 1) {
			$this->aConf['urlrewriting'] = '0';
		}
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

		if(!is_file($filename)) return;

		$activeCats = ['000', 'home'];
		$homepageCats = $activeCats;

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		if(isset($iTags['categorie']) AND isset($iTags['name'])) {
			$nb = sizeof($iTags['name']);
			$size=ceil(sizeof($iTags['categorie'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['categorie'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				# Recuperation du nom de la catégorie
				$this->aCats[$number]['name']=plxUtils::getTagIndexValue($iTags['name'], $values, $i);
				# Recuperation du nom de la description
				$this->aCats[$number]['description']=plxUtils::getTagIndexValue($iTags['description'], $values, $i);
				# Recuperation de la balise title
				$this->aCats[$number]['title_htmltag']=plxUtils::getTagIndexValue($iTags['title_htmltag'], $values, $i);
				# Recuperation du meta description
				$this->aCats[$number]['meta_description']=plxUtils::getValue($values[$iTags['meta_description'][$i]]['value']);
				# Recuperation du meta keywords
				$this->aCats[$number]['meta_keywords']=plxUtils::getTagIndexValue($iTags['meta_keywords'], $values, $i);
				# Recuperation de l'url de la categorie
				$this->aCats[$number]['url']=strtolower($attributes['url']);
				# Recuperation du tri de la categorie si besoin est
				$this->aCats[$number]['tri']=isset($attributes['tri'])?$attributes['tri']:$this->aConf['tri'];
				# Recuperation du nb d'articles par page de la categorie si besoin est
				$this->aCats[$number]['bypage']=isset($attributes['bypage'])?intval($attributes['bypage']):$this->bypage;
				# Recuperation du fichier template
				$this->aCats[$number]['template']=isset($attributes['template']) ? $attributes['template']:'categorie.php';
				# Récupération des informations de l'image représentant la catégorie
				$this->aCats[$number]['thumbnail']=plxUtils::getTagIndexValue($iTags['thumbnail'], $values, $i);
				$this->aCats[$number]['thumbnail_title']=plxUtils::getTagIndexValue($iTags['thumbnail_title'], $values, $i);
				$this->aCats[$number]['thumbnail_alt']=plxUtils::getTagIndexValue($iTags['thumbnail_alt'], $values, $i);
				# Récuperation état affichage de la catégorie dans le menu
				$this->aCats[$number]['menu']=isset($attributes['menu'])?$attributes['menu']:'oui';
				# Récuperation état activation de la catégorie dans le menu
				$this->aCats[$number]['active'] = !empty(isset($attributes['active']) ? $attributes['active'] : '1');
				# Recuperation affichage en page d'accueil
				$homepage = isset($attributes['homepage']) ? $attributes['homepage'] : 1;
				if(!in_array($homepage, ['0', '1',])) {
					$homepage = 1;
				}
				$this->aCats[$number]['homepage'] = !empty($homepage);

				if($this->aCats[$number]['active']) {
					$activeCats[]=$number;
					if(!empty($homepage)) {
						$homepageCats[] = $number;
					}
				}
				# Recuperation du nombre d'article de la categorie
				$motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*' . $number . '(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
				$arts = $this->plxGlob_arts->query($motif,'art','',0,false,'before');
				$this->aCats[$number]['articles'] = ($arts?sizeof($arts):0);
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
				$this->aStats[$number]['name']=plxUtils::getTagIndexValue($iTags['name'], $values, $i);
				# Récupération de la balise title
				$this->aStats[$number]['title_htmltag']=plxUtils::getTagIndexValue($iTags['title_htmltag'], $values, $i);
				# Récupération du meta description
				$this->aStats[$number]['meta_description']=plxUtils::getTagIndexValue($iTags['meta_description'], $values, $i);
				# Récupération du meta keywords
				$this->aStats[$number]['meta_keywords']=plxUtils::getTagIndexValue($iTags['meta_keywords'], $values, $i);
				# Récupération du groupe de la page statique
				$this->aStats[$number]['group']=plxUtils::getTagIndexValue($iTags['group'], $values, $i);
				# Récupération de l'url de la page statique
				$this->aStats[$number]['url']=strtolower($attributes['url']);
				# Récupération de l'etat de la page
				$this->aStats[$number]['active']=intval($attributes['active']);
				# On affiche la page statique dans le menu ?
				$this->aStats[$number]['menu']=isset($attributes['menu'])?$attributes['menu']:'oui';
				# Récupération du fichier template
				$this->aStats[$number]['template']=isset($attributes['template'])?$attributes['template']:'static.php';
				# Récupération de la date de création
				$this->aStats[$number]['date_creation']=plxUtils::getTagIndexValue($iTags['date_creation'], $values, $i);
				# Récupération de la date de mise à jour
				$this->aStats[$number]['date_update']=plxUtils::getTagIndexValue($iTags['date_update'], $values, $i);
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
				$this->aUsers[$number]['active']=$attributes['active'];
				$this->aUsers[$number]['delete']=$attributes['delete'];
				$this->aUsers[$number]['profil']=$attributes['profil'];
				$this->aUsers[$number]['login']=plxUtils::getTagIndexValue($iTags['login'], $values, $i);
				$this->aUsers[$number]['name']=plxUtils::getTagIndexValue($iTags['name'], $values, $i);
				$this->aUsers[$number]['password']=plxUtils::getTagIndexValue($iTags['password'], $values, $i);
				$this->aUsers[$number]['salt']=plxUtils::getTagIndexValue($iTags['salt'], $values, $i);
				$this->aUsers[$number]['infos']=plxUtils::getTagIndexValue($iTags['infos'], $values, $i);
				$this->aUsers[$number]['email']=plxUtils::getTagIndexValue($iTags['email'], $values, $i);
				$lang = plxUtils::getTagIndexValue($iTags['lang'], $values, $i);
				$this->aUsers[$number]['lang'] = empty($lang) ? $lang : $this->aConf['default_lang'];
				$this->aUsers[$number]['password_token']=plxUtils::getTagIndexValue($iTags['password_token'], $values, $i);
				$this->aUsers[$number]['password_token_expiry']=plxUtils::getTagIndexValue($iTags['password_token_expiry'], $values, $i);
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
	 * @author	Stéphane F, J.P. Pourrez (bazooka07)
	 **/
	public function getArticles($publi='before') {

		# On calcule la valeur start
		$start = $this->bypage*($this->page-1);
		# On recupere nos fichiers (tries) selon le motif, la pagination, la date de publication
		if($aFiles = $this->plxGlob_arts->query($this->motif,'art',$this->tri,$start,$this->bypage,$publi)) {
			# On analyse tous les fichiers
			$artsList = array();
			foreach($aFiles as $v) {
				$art = $this->parseArticle(PLX_ROOT . $this->aConf['racine_articles'] . $v);
				if(!empty($art)) {
					$artsList[] = $art;
				}
			}
			# On stocke les enregistrements dans un objet plxRecord
			$this->plxRecord_arts = new plxRecord($artsList);
			return true;
		}

		$this->plxRecord_arts = false;
		return false;
	}

	/**
	 * Méthode qui retourne les informations $output en analysant
	 * le nom du fichier de l'article $filename
	 *
	 * @param	filename	fichier de l'article à traiter
	 * @return	array		information à récupérer
	 * @author	Stephane F, J.P. Pourrez "bazooka07"
	 **/
	public function artInfoFromFilename($filename) {

		# On effectue notre capture d'informations
		if(preg_match('#^(_?\d{4})\.((?:draft,|pin,|\d{3},)*(?:home|\d{3})+(?:,\d{3})*)\.(\d{3})\.(\d{12})\.(.*)\.xml$#', basename($filename), $capture)) {
			$ids = array_merge(array_keys($this->aCats), array('draft', 'pin', 'home',));
			$artCats = array_filter(
				explode(',', $capture[2]),
				# on vérifie que les catégories de l'article existent
				function($item) use($ids) {
					return in_array($item, $ids);
				}
			);
			if(count($artCats) == 1 and  in_array('draft', $artCats)) {
				$artCats[] = '000';
			}
			return array(
				'artId'		=> $capture[1],
				'catId'		=> !empty($artCats) ? implode(',', $artCats) : '000',
				'usrId'		=> $capture[3],
				'artDate'	=> $capture[4],
				'artUrl'	=> $capture[5]
			);
		}
		return false;
	}

	/**
	 * Méthode qui parse l'article du fichier $filename
	 *
	 * @param	filename	fichier de l'article à parser
	 * @return	array
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F, J.P. Pourrez (bazooka07)
	 **/
	public function parseArticle($filename) {

		# Informations obtenues en analysant le nom du fichier
		$tmp = $this->artInfoFromFilename($filename);
		if(!empty($tmp)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);

			$art = array(
				'filename'		=> $filename,
				# Recuperation des valeurs de nos champs XML
				'title'				=> plxUtils::getTagValue($iTags['title'], $values),
				'allow_com'			=> plxUtils::getTagValue($iTags['allow_com'], $values, 0),
				'template'			=> plxUtils::getTagValue($iTags['template'], $values, 'article.php'),
				'chapo'				=> plxUtils::getTagValue($iTags['chapo'], $values),
				'content'			=> plxUtils::getTagValue($iTags['content'], $values),
				'tags'				=> plxUtils::getTagValue($iTags['tags'], $values),
				'meta_description'	=> plxUtils::getTagValue($iTags['meta_description'], $values),
				'meta_keywords'		=> plxUtils::getTagValue($iTags['meta_keywords'], $values),
				'title_htmltag'		=> plxUtils::getTagValue($iTags['title_htmltag'], $values),
				'thumbnail'			=> plxUtils::getTagValue($iTags['thumbnail'], $values),
				'thumbnail_title'	=> plxUtils::getTagValue($iTags['thumbnail_title'], $values),
				'thumbnail_alt'		=> plxUtils::getTagValue($iTags['thumbnail_alt'], $values),
				'numero'			=> $tmp['artId'],
				'author'			=> $tmp['usrId'],
				'categorie'			=> $tmp['catId'],
				'url'				=> $tmp['artUrl'],
				'date'				=> $tmp['artDate'],
				'nb_com'			=> $this->getNbCommentaires('#^' . $tmp['artId'] . '.\d{10}.\d+.xml$#'),
				'date_creation'		=> plxUtils::getTagValue($iTags['date_creation'], $values, $tmp['artDate']),
				'date_update'		=> plxUtils::getTagValue($iTags['date_update'], $values, $tmp['artDate']),
			);

			# Hook plugins
			eval($this->plxPlugins->callHook('plxMotorParseArticle'));

			# On retourne le tableau
			return $art;
		} else {
			# le nom du fichier article est incorrect !!
			if(defined('PLX_ADMIN') and class_exists('plxMsg')) {
				plxMsg::Error('Invalid filename for article :<br />' . basename($filename));
			}
			return false;
		}
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
		$com['author'] = plxUtils::getTagValue($iTags['author'], $values);
		if(isset($iTags['type']))
			$com['type'] = plxUtils::getTagValue($iTags['type'], $values,'normal');
		else
			$com['type'] = 'normal';
		$com['ip'] = plxUtils::getTagValue($iTags['ip'], $values);
		$com['mail'] = plxUtils::getTagValue($iTags['mail'], $values);
		$com['site'] = plxUtils::getTagValue($iTags['site'], $values);
		$com['content'] = trim($values[ $iTags['content'][0] ]['value']);
		$com['parent'] = plxUtils::getTagValue($iTags['parent'], $values);
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
			if (intval($value[$parentField]) == $parentID) {
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
			if (!preg_match('#comments?$#',basename($_SERVER['SCRIPT_NAME'], '.php'))) {
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

		if(intval($this->aConf['allow_com']) == 2 or intval($this->plxRecord_arts->f('allow_com')) == 2) {
			$success = false;
			if(
				(!empty($content['name']) or !empty($content['login'])) and
				!empty($content['password'])
			) {
				foreach($this->aUsers as $user) {
					if($user['active'] and !$user['delete'] and $user['login'] == (isset($content['login']) ? $content['login'] : $content['name'])) {
						$success = (sha1($user['salt'] . md5($content['password'])) === $user['password']);
						# $_POST['login'] == $user['login'] and sha1($user['salt'] . md5($_POST['password'])) === $user['password']
						break;
					}
				}
			}

			if(!$success) {
				return L_NEWCOMMENT_ERR_LOGIN;
			}
		}

		if(
			!empty($this->aConf['capcha']) AND (
				empty($_SESSION['capcha_token']) OR
				empty($content['capcha_token']) or
				($_SESSION['capcha_token'] != $content['capcha_token'])
			)
		) {
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}

		# On vérifie que le capcha est correct
		if(empty($this->aConf['capcha']) OR $_SESSION['capcha'] == sha1($content['rep'])) {
			if((!empty($content['login']) or !empty($content['name'])) AND !empty($content['content'])) {
				# Les champs obligatoires sont remplis
				$artId = str_pad($artId, 4, '0', STR_PAD_LEFT);
				# index du commentaire
				$idx = $this->nextIdArtComment($artId);
				# On modère le commentaire => underscore si besoin
				$mod = $this->aConf['mod_com'] ? '_' : '';
				# On génère le nom du fichier
				$filename = $mod . $artId . '.' . time() . '-' . $idx . '.xml';

				$comment = [
					'type' => 'normal',
					'author' => plxUtils::strCheck(trim(!empty($content['name']) ? $content['name'] : $content['login'])),
					'content' => plxUtils::strCheck(trim($content['content'])),
					# On vérifie le mail
					'mail' => (!empty($content['mail']) and plxUtils::checkMail(trim($content['mail']))) ? trim($content['mail']) : '',
					# On vérifie le site
					'site' => (!empty($content['site']) and plxUtils::checkSite(trim($content['site']))) ? trim($content['site']) : '',
					# On récupère l'adresse IP du posteur
					'ip' => plxUtils::getIp(),
					# Commentaire parent en cas de réponse
					'parent' => !empty($content['parent']) ? intval($content['parent']) : '',
					'filename' => $filename,
				];

				# On peut créer le commentaire
				if($this->addCommentaire($comment)) { # Commentaire OK
					return $this->aConf['mod_com'] ? 'mod' : 'c' . $artId . '-' . $idx;
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
		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorAddCommentaire'))) return;
		# On genere le contenu de notre fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<comment>\n";
		$xml .= "\t<author><![CDATA[".plxUtils::cdataCheck($content['author'])."]]></author>\n";
		$xml .= "\t<type>".$content['type']."</type>\n";
		$xml .= "\t<ip>".$content['ip']."</ip>\n";
		$xml .= "\t<mail><![CDATA[".plxUtils::cdataCheck($content['mail'])."]]></mail>\n";
		$xml .= "\t<site><![CDATA[".plxUtils::cdataCheck($content['site'])."]]></site>\n";
		$xml .= "\t<content><![CDATA[".plxUtils::cdataCheck($content['content'])."]]></content>\n";
		$xml .= "\t<parent><![CDATA[".plxUtils::cdataCheck($content['parent'])."]]></parent>\n";
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorAddCommentaireXml'));
		$xml .= "</comment>\n";
		# On ecrit ce contenu dans notre fichier XML
		return plxUtils::write($xml, PLX_ROOT.$this->aConf['racine_commentaires'].$content['filename']);
	}

	/**
	 * Méthode qui parse le fichier des tags et alimente
	 * le tableau aTags
	 *
	 * @param	filename	emplacement du fichier XML contenant les tags
	 * @return	null
	 * @author	Stephane F.
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
			$nb = sizeof($iTags['article']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				if(isset($values[ $iTags['article'][$i] ]['value']))
					$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['tags'] = trim($values[ $iTags['article'][$i] ]['value']);
				else
					$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['tags'] = '';
				$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['date'] = $values[ $iTags['article'][$i] ]['attributes']['date'];
				$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['active'] = $values[ $iTags['article'][$i] ]['attributes']['active'];
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

		if($url=='' OR $url=='?') {
			return $this->racine;
		}

		$args = parse_url($url);

		if($this->aConf['urlrewriting']) {
			$new_url = !empty($args['path']) ? strtr($args['path'], array(
				'index.php' => '',
				'feed.php' => 'feed/',
			)) : '';
			if(!empty($args['query'])) {
				$new_url .= $args['query'];
			}
			if(empty($new_url))	{
				$new_url = $this->path_url;
			}
			if(!empty($args['fragment'])) {
				$new_url .= '#'. $args['fragment'];
			}
		} else {
			if(empty($args['path']) AND !empty($args['query'])) {
				$args['path'] = 'index.php';
			}
			$new_url  = !empty($args['path']) ? $args['path'] : $this->path_url;
			if(!empty($args['query'])) {
				$new_url .= '?' . $args['query'];
			}
			if(!empty($args['fragment'])) {
				$new_url .= '#' . $args['fragment'];
			}
		}

		return $this->racine . $new_url;
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

		switch($select) {
			case 'all':
			case 'mod':
				$motif = '(?:draft,|pin,|\d{3},)*(home|\d{3})(?:,\d{3})*'; break;
			case 'published':
				$motif = '(?:pin,|\d{3},)*(home|\d{3})(?:,\d{3})*'; break;
			case 'draft':
				$motif = 'draft,(?:pin,|\d{3},)*(home|\d{3})(?:,\d{3})*'; break;
			default:
				$motif = $select;
		}

		if($arts = $this->plxGlob_arts->query('#^' . $mod . '\d{4}\.' . $motif . '\.'.$userId.'\.\d{12}.[\w-]+\.xml$#', 'art', '', 0, false, $publi)) {
			return sizeof($arts);
		}

		return 0;
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

		switch($select) {
			case 'all' : $motif = '#^_?\d{4}\.(.*)\.xml$#'; break;
			case 'offline' : $motif = '#^_\d{4}\.(.*)\.xml$#'; break;
			case 'online' : $motif = '#^\d{4}\.(.*)\.xml$#'; break;
			default : $motif = $select;
		}

		return ($coms = $this->plxGlob_coms->query($motif,'com','',0,false,$publi)) ? sizeof($coms) : 0;
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
				if(preg_match('#^(\d{4}).(?:pin,|\d{3},)*(?:' . $this->activeCats . ')(?:,\d{3})*\.\d{3}\.(\d{12})\.[\w-]+\.xml$#', $filename, $capture)) {
					if($capture[2]<=$datetime) { # on ne prends que les articles publiés
						$this->activeArts[$capture[1]]=1;
					}
				}
			}
		}
	}

	/**
	 * Méthode qui vérifie si PHPMailer est désactivé
	 *
	 * @return	boolean
	 * author	Jean-Pierre Pourrez "bazooka07"
	 **/
	public function isPHPMailerDisabled() {
		return
			empty($this->aConf['email_method']) or
			$this->aConf['email_method'] == 'sendmail' or
			!class_exists('PHPMailer') or
			!method_exists('plxUtils', 'sendMailPhpMailer');
	}

	public function getPlxThemes() {
		$homestatic = $this->aConf['homestatic'];
		$homepage = empty($homestatic) ? $this->aConf['hometemplate'] : $this->aStats[$homestatic]['template'];
		return new plxThemes(PLX_ROOT . $this->aConf['racine_themes'], $this->aConf['style'], $homepage);
	}

}

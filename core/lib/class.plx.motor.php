<?php

/**
 * Classe plxMotor responsable du traitement global du script
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
 **/
class plxMotor {

	public $version = false; # Version de PluXml
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

	public $aConf = array(); # Tableau de configuration
	public $aCats = array(); # Tableau de toutes les catégories
	public $aStats = array(); # Tableau de toutes les pages statiques
	public $aTags = array(); # Tableau des tags
	public $aUsers = array(); #Tableau des utilisateurs

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
	 * @return	objet			return une instance de la classe plxMotor
	 * @author	Stephane F
	 **/
	public static function getInstance(){
		if (!isset(self::$instance)) {
			self::$instance = false;
			self::$instance = new plxMotor(XMLFILE_PARAMETERS);
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

		# Version de PluXml
		if(!is_readable(PLX_ROOT.'version')) {
			header('Content-Type: text/plain charset=UTF-8');
			printf(utf8_decode(L_FILE_VERSION_REQUIRED, PLX_ROOT));
			exit;
		}
		$f = file(PLX_ROOT.'version');
		$this->version = $f['0'];

		$this->get = plxUtils::getGets();
		# On parse le fichier de configuration
		$this->getConfiguration($filename);
		# gestion du timezone
		date_default_timezone_set($this->aConf['timezone']);
		# On vérifie s'il faut faire une mise à jour
		if((!isset($this->aConf['version']) OR $this->version!=$this->aConf['version']) AND !defined('PLX_UPDATER')) {
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
		$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : $this->aConf['default_lang'];
		$this->plxPlugins = new plxPlugins(XMLFILE_PLUGINS, $lang);
		$this->plxPlugins->loadPlugins();
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorConstructLoadPlugins'));
		# Traitement sur les répertoires des articles et des commentaires
		$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles'],false,true,'arts');
		$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires']);
		# Récupération des données dans les autres fichiers xml
		$this->getCategories(XMLFILE_CATEGORIES);
		$this->getStatiques(XMLFILE_STATICS);
		$this->getTags(XMLFILE_TAGS);
		$this->getUsers(XMLFILE_USERS);
		# Récuperation des articles appartenant aux catégories actives
		$this->getActiveArts();
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorConstruct'));
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
		elseif(!$this->get OR preg_match('/^(blog|blog\/page[0-9]*|\/?page[0-9]*)$/',$this->get)) {
			$this->mode = 'home';
			$this->template = 'home.php';
			$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			# On regarde si on a des articles en mode "home"
			if($this->plxGlob_arts->query('/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/')) {
				$this->motif = '/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
			} else { # Sinon on recupere tous les articles
				$this->motif = '/^[0-9]{4}.(?:[0-9]|,)*(?:'.$this->homepageCats.')(?:[0-9]|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
			}
		}
		elseif($this->get AND preg_match('/^article([0-9]+)\/?([a-z0-9-]+)?/',$this->get,$capture)) {
			$this->mode = 'article'; # Mode article
			$this->template = 'article.php';
			$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT); # On complete sur 4 caracteres
			$this->motif = '/^'.$this->cible.'.((?:[0-9]|home|,)*(?:'.$this->activeCats.'|home)(?:[0-9]|home|,)*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/'; # Motif de recherche
			if($this->getArticles()) {
				# Redirection 301
				if($this->plxRecord_arts->f('url')!=$capture[2]) {
					header('Status: 301 Moved Permanently', false, 301);
					header('Location: '.$this->urlRewrite('?article'.intval($this->cible).'/'.$this->plxRecord_arts->f('url')));
					exit();
				}
			} else {
				$this->error404(L_UNKNOWN_ARTICLE);
			}
		}
		elseif($this->get AND preg_match('/^static([0-9]+)\/?([a-z0-9-]+)?/',$this->get,$capture)) {
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			if(!empty($this->aStats[$this->cible]) AND $this->aStats[$this->cible]['active'] AND $this->aStats[$this->cible]['url']==$capture[2]) {
				$this->mode = 'static'; # Mode static
				$this->template = $this->aStats[$this->cible]['template'];
			}
			elseif(isset($this->aStats[$this->cible])) { # Redirection 301
				if($this->aStats[$this->cible]['url']!=$capture[2]) {
					header('Status: 301 Moved Permanently', false, 301);
					header('Location: '.$this->urlRewrite('?static'.intval($this->cible).'/'.$this->aStats[$this->cible]['url']));
					exit();
				}
			} else {
				$this->error404(L_UNKNOWN_STATIC);
			}
		}
		elseif($this->get AND preg_match('/^categorie([0-9]+)\/?([a-z0-9-]+)?/',$this->get,$capture)) {
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			if(!empty($this->aCats[$this->cible]) AND $this->aCats[$this->cible]['active'] AND $this->aCats[$this->cible]['url']==$capture[2]) {
				$this->mode = 'categorie'; # Mode categorie
				$this->motif = '/^[0-9]{4}.(?:[0-9]|home|,)*(?:'.$this->cible.')(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/'; # Motif de recherche
				$this->template = $this->aCats[$this->cible]['template'];
				$this->tri = $this->aCats[$this->cible]['tri']; # Recuperation du tri des articles
				$this->bypage = $this->aCats[$this->cible]['bypage'] > 0 ? $this->aCats[$this->cible]['bypage'] : $this->bypage;
			}
			elseif(isset($this->aCats[$this->cible])) { # Redirection 301
				if($this->aCats[$this->cible]['url']!=$capture[2]) {
					header('Status: 301 Moved Permanently', false, 301);
					header('Location: '.$this->urlRewrite('?categorie'.intval($this->cible).'/'.$this->aCats[$this->cible]['url']));
					exit();
				}
			} else {
				$this->error404(L_UNKNOWN_CATEGORY);
			}
		}
        elseif($this->get AND preg_match('/^archives\/([0-9]{4})[\/]?([0-9]{2})?[\/]?([0-9]{2})?/',$this->get,$capture)) {
			$this->mode = 'archives';
			$this->template = 'archives.php';
			$this->bypage = $this->aConf['bypage_archives'];
			$this->cible = $search = $capture[1];
			if(!empty($capture[2])) $this->cible = ($search .= $capture[2]);
			else $search .= '[0-9]{2}';
			if(!empty($capture[3])) $search .= $capture[3];
			else $search .= '[0-9]{2}';
			$this->motif = '/^[0-9]{4}.(?:[0-9]|home|,)*(?:'.$this->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.'.$search.'[0-9]{4}.[a-z0-9-]+.xml$/';
        }
		elseif($this->get AND preg_match('/^tag\/([a-z0-9-]+)/',$this->get,$capture)) {
			$this->cible = $capture[1];
			$ids = array();
			$datetime = date('YmdHi');
			foreach($this->aTags as $idart => $tag) {
				if($tag['date']<=$datetime) {
					$tags = array_map("trim", explode(',', $tag['tags']));
					$tagUrls = array_map(array('plxUtils', 'title2url'), $tags);
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
				$this->motif = '/('.implode('|', $ids).').(?:[0-9]|home|,)*(?:'.$this->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
				$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			} else {
				$this->error404(L_ARTICLE_NO_TAG);
			}
		}
		elseif($this->get AND preg_match('/^preview\/?/',$this->get) AND isset($_SESSION['preview'])) {
			$this->mode = 'preview';
		}
		elseif($this->get AND preg_match('/^(telechargement|download)\/(.+)$/',$this->get,$capture)) {
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
	 * Méthode qui retourne une erreur 404 Document non trouvé
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function error404($msg) {
		header("Status : 404 Not Found");
		header("HTTP/1.0 404 Not Found");
		$this->plxErreur = new plxErreur($msg);
		$this->mode = 'erreur';
		$this->template = 'erreur.php';
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

		if($this->mode == 'home' OR $this->mode == 'categorie' OR $this->mode == 'archives' OR $this->mode == 'tags') {
			$this->getPage(); # Recuperation du numéro de la page courante
			if(!$this->getArticles()) { # Si aucun article
				$this->plxErreur = new plxErreur(L_NO_ARTICLE_PAGE);
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
			}
		}
		elseif($this->mode == 'article') {

			# On a validé le formulaire commentaire
			if(!empty($_POST) AND $this->plxRecord_arts->f('allow_com') AND $this->aConf['allow_com']) {
				# On récupère le retour de la création
				$retour = $this->newCommentaire($this->cible,plxUtils::unSlash($_POST));
				# Url de l'article
				$url = $this->urlRewrite('?article'.intval($this->plxRecord_arts->f('numero')).'/'.$this->plxRecord_arts->f('url'));
				eval($this->plxPlugins->callHook('plxMotorDemarrageNewCommentaire'));
				if($retour[0] == 'c') { # Le commentaire a été publié
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
					eval($this->plxPlugins->callHook('plxMotorDemarrageCommentSessionMessage'));
					header('Location: '.$url.'#form');
				}
				exit;
			}
			# Récupération des commentaires
			$this->getCommentaires('/^'.$this->cible.'.[0-9]{10}-[0-9]+.xml$/',$this->mapTri($this->tri_coms));
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
				if(isset($values[ $iTags['parametre'][$i] ]['value'])) # On a une valeur pour ce parametre
					$this->aConf[ $values[ $iTags['parametre'][$i] ]['attributes']['name'] ] = $values[ $iTags['parametre'][$i] ]['value'];
				else # On n'a pas de valeur
					$this->aConf[ $values[ $iTags['parametre'][$i] ]['attributes']['name'] ] = '';
			}
		}

		# On gère la non regression en cas d'ajout de paramètres sur une version de pluxml déjà installée
		$this->aConf['bypage_admin'] = plxUtils::getValue($this->aConf['bypage_admin'],10);
		$this->aConf['tri_coms'] = plxUtils::getValue($this->aConf['tri_coms'],$this->aConf['tri']);
		$this->aConf['bypage_admin_coms'] = plxUtils::getValue($this->aConf['bypage_admin_coms'],10);
		$this->aConf['bypage_archives'] = plxUtils::getValue($this->aConf['bypage_archives'],5);
		$this->aConf['userfolders'] = plxUtils::getValue($this->aConf['userfolders'],0);
		$this->aConf['meta_description'] = plxUtils::getValue($this->aConf['meta_description']);
		$this->aConf['meta_keywords'] = plxUtils::getValue($this->aConf['meta_keywords']);
		$this->aConf['default_lang'] = plxUtils::getValue($this->aConf['default_lang'],DEFAULT_LANG);
		$this->aConf['racine_plugins'] = plxUtils::getValue($this->aConf['racine_plugins'], 'plugins/');
		$this->aConf['racine_themes'] = plxUtils::getValue($this->aConf['racine_themes'], 'themes/');
		$this->aConf['mod_art'] = plxUtils::getValue($this->aConf['mod_art'],0);
		$this->aConf['display_empty_cat'] = plxUtils::getValue($this->aConf['display_empty_cat'],0);
		$this->aConf['timezone'] = plxUtils::getValue($this->aConf['timezone'],date_default_timezone_get());
		$this->aConf['thumbs'] = isset($this->aConf['thumbs']) ? $this->aConf['thumbs'] : 1;
		if(!defined('PLX_PLUGINS')) define('PLX_PLUGINS', PLX_ROOT.$this->aConf['racine_plugins']);

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

		$activeCats = array();
		$homepageCats = array();

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
				$this->aCats[$number]['name']=plxUtils::getValue($values[$iTags['name'][$i]]['value']);
				# Recuperation du nom de la description
				$this->aCats[$number]['description']=plxUtils::getValue($values[$iTags['description'][$i]]['value']);
				# Recuperation de la balise title
				$title_htmltag = plxUtils::getValue($iTags['title_htmltag'][$i]);
				$this->aCats[$number]['title_htmltag']=plxUtils::getValue($values[$title_htmltag]['value']);
				# Recuperation du meta description
				$meta_description = plxUtils::getValue($iTags['meta_description'][$i]);
				$this->aCats[$number]['meta_description']=plxUtils::getValue($values[$meta_description]['value']);
				# Recuperation du meta keywords
				$meta_keywords = plxUtils::getValue($iTags['meta_keywords'][$i]);
				$this->aCats[$number]['meta_keywords']=plxUtils::getValue($values[$meta_keywords]['value']);
				# Recuperation de l'url de la categorie
				$this->aCats[$number]['url']=strtolower($attributes['url']);
				# Recuperation du tri de la categorie si besoin est
				$this->aCats[$number]['tri']=isset($attributes['tri'])?$attributes['tri']:$this->aConf['tri'];
				# Recuperation du nb d'articles par page de la categorie si besoin est
				$this->aCats[$number]['bypage']=isset($attributes['bypage'])?$attributes['bypage']:$this->bypage;
				# Recuperation du fichier template
				$this->aCats[$number]['template']=isset($attributes['template'])?$attributes['template']:'categorie.php';
				# Récuperation état affichage de la catégorie dans le menu
				$this->aCats[$number]['menu']=isset($attributes['menu'])?$attributes['menu']:'oui';
				# Récuperation état activation de la catégorie dans le menu
				$this->aCats[$number]['active']=isset($attributes['active'])?$attributes['active']:'1';
				if($this->aCats[$number]['active']) $activeCats[]=$number;
				# Recuperation affichage en page d'accueil
				$this->aCats[$number]['homepage']=(isset($attributes['homepage']) AND in_array($attributes['homepage'],array('0','1'))?$attributes['homepage']:'1');
				if($this->aCats[$number]['active'] AND $this->aCats[$number]['homepage']) $homepageCats[]=$number;
				# Recuperation du nombre d'article de la categorie
				$motif = '/^[0-9]{4}.[home,|0-9,]*'.$number.'[0-9,]*.[0-9]{3}.[0-9]{12}.[A-Za-z0-9-]+.xml$/';
				$arts = $this->plxGlob_arts->query($motif,'art','',0,false,'before');
				$this->aCats[$number]['articles'] = ($arts?sizeof($arts):0);
				# Hook plugins
				eval($this->plxPlugins->callHook('plxMotorGetCategories'));
			}
		}
		$homepageCats [] = '000'; # on rajoute la catégorie 'Non classée'
		$activeCats[] = '000'; # on rajoute la catégorie 'Non classée'
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
				# Recuperation du nom de la page statique
				$this->aStats[$number]['name']=plxUtils::getValue($values[$iTags['name'][$i]]['value']);
				# Recuperation de la balise title
				$title_htmltag = plxUtils::getValue($iTags['title_htmltag'][$i]);
				$this->aStats[$number]['title_htmltag']=plxUtils::getValue($values[$title_htmltag]['value']);
				# Recuperation du meta description
				$meta_description = plxUtils::getValue($iTags['meta_description'][$i]);
				$this->aStats[$number]['meta_description']=plxUtils::getValue($values[$meta_description]['value']);
				# Recuperation du meta keywords
				$meta_keywords = plxUtils::getValue($iTags['meta_keywords'][$i]);
				$this->aStats[$number]['meta_keywords']=plxUtils::getValue($values[$meta_keywords]['value']);
				# Recuperation du groupe de la page statique
				$this->aStats[$number]['group']=plxUtils::getValue($values[$iTags['group'][$i]]['value']);
				# Recuperation de l'url de la page statique
				$this->aStats[$number]['url']=strtolower($attributes['url']);
				# Recuperation de l'etat de la page
				$this->aStats[$number]['active']=intval($attributes['active']);
				# On affiche la page statique dans le menu ?
				$this->aStats[$number]['menu']=isset($attributes['menu'])?$attributes['menu']:'oui';
				# recuperation du fichier template
				$this->aStats[$number]['template']=isset($attributes['template'])?$attributes['template']:'static.php';
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
				$this->aUsers[$number]['login']=plxUtils::getValue($values[$iTags['login'][$i]]['value']);
				$this->aUsers[$number]['name']=plxUtils::getValue($values[$iTags['name'][$i]]['value']);
				$this->aUsers[$number]['password']=plxUtils::getValue($values[$iTags['password'][$i] ]['value']);
				$salt = plxUtils::getValue($iTags['salt'][$i]);
				$this->aUsers[$number]['salt']=plxUtils::getValue($values[$salt]['value']);
				$this->aUsers[$number]['infos']=plxUtils::getValue($values[$iTags['infos'][$i]]['value']);
				$email = plxUtils::getValue($iTags['email'][$i]);
				$this->aUsers[$number]['email']=plxUtils::getValue($values[$email]['value']);
				$lang = isset($iTags['lang'][$i]) ? $values[$iTags['lang'][$i]]['value']:'';
				$this->aUsers[$number]['lang'] = $lang!='' ? $lang : $this->aConf['default_lang'];
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
	protected function mapTri($tri) {

		if($tri=='desc')
			return 'rsort';
		elseif($tri=='asc')
			return 'sort';
		elseif($tri=='alpha')
			return 'alpha';
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
		if(preg_match('/page([0-9]*)/',$this->get,$capture))
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

		# On fait notre traitement sur notre tri
		$ordre = $this->mapTri($this->tri);
		# On calcule la valeur start
		$start = $this->bypage*($this->page-1);
		# On recupere nos fichiers (tries) selon le motif, la pagination, la date de publication
		if($aFiles = $this->plxGlob_arts->query($this->motif,'art',$ordre,$start,$this->bypage,$publi)) {
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
		if(preg_match('/(_?[0-9]{4}).([0-9,|home|draft]*).([0-9]{3}).([0-9]{12}).([a-z0-9-]+).xml$/',$filename,$capture)) {
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
		# Informations obtenues en analysant le nom du fichier
		$art['filename'] = $filename;
		$tmp = $this->artInfoFromFilename($filename);
		$art['numero'] = $tmp['artId'];
		$art['author'] = $tmp['usrId'];
		$art['categorie'] = $tmp['catId'];
		$art['url'] = $tmp['artUrl'];
		$art['date'] = $tmp['artDate'];
		$art['nb_com'] = $this->getNbCommentaires('/^'.$art['numero'].'.[0-9]{10}.[0-9]+.xml$/');

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

		if($coms = $this->plxGlob_coms->query($motif,'com','sort',0,false,$publi))
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
		if(preg_match('/([[:punct:]]?)([0-9]{4}).([0-9]{10})-([0-9])+.xml$/',$filename,$capture)) {
			return array(
				'comStatus'	=> $capture[1],
				'artId'		=> $capture[2],
				'comDate'	=> plxDate::timestamp2Date($capture[3]),
				'comId'		=> $capture[3].'-'.$capture[4],
				'comIdx'	=> $capture[4]
			);
		}
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
		# Informations obtenues en analysant le nom du fichier
		$tmp = $this->comInfoFromFilename(basename($filename));
		$com['status'] = $tmp['comStatus'];
		$com['numero'] = $tmp['comId'];
		$com['article'] = $tmp['artId'];
		$com['date'] = $tmp['comDate'];
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorParseCommentaire'));
		# On retourne le tableau
		return $com;
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
	 * @return	null
	 * @author	Florent MONTHEL
	 **/
	public function getCommentaires($motif,$ordre='sort',$start=0,$limite=false,$publi='before') {

		# On recupère les fichiers des commentaires
		$aFiles = $this->plxGlob_coms->query($motif,'com',$ordre,$start,$limite,$publi);
		if($aFiles) { # On a des fichiers
			foreach($aFiles as $k=>$v) # On parcourt tous les fichiers
				$array[ $k ] = $this->parseCommentaire(PLX_ROOT.$this->aConf['racine_commentaires'].$v);
			# On stocke les enregistrements dans un objet plxRecord
			$this->plxRecord_coms = new plxRecord($array);
			return true;
		}
		else return false;
	}

	/**
	 * Méthode qui crée un nouveau commentaire pour l'article $artId
	 *
	 * @param	artId	identifiant de l'article en question
	 * @param	content	tableau contenant les valeurs du nouveau commentaire
	 * @return	string
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function newCommentaire($artId,$content) {
		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorNewCommentaire'))) return;
		# On verifie que le capcha est correct
		if($this->aConf['capcha'] == 0 OR $_SESSION['capcha'] == sha1($content['rep'])) {
			if(!empty($content['name']) AND !empty($content['content'])) { # Les champs obligatoires sont remplis
				$comment=array();
				$comment['type'] = 'normal';
				$comment['author'] = plxUtils::strCheck(trim($content['name']));
				$comment['content'] = plxUtils::strCheck(trim($content['content']));
				# On verifie le mail
				$comment['mail'] = (plxUtils::checkMail(trim($content['mail'])))?trim($content['mail']):'';
				# On verifie le site
				$comment['site'] = (plxUtils::checkSite($content['site'])?$content['site']:'');
				# On recupere l'adresse IP du posteur
				$comment['ip'] = plxUtils::getIp();
				# On genere le nom du fichier selon l'existence ou non d'un fichier du meme nom
				$time = time();
				$i = 0;
				do { # On boucle en testant l'existence du fichier (cas de plusieurs commentaires/sec pour un article)
					$i++;
					if($this->aConf['mod_com']) # On modere le commentaire => underscore
						$comment['filename'] = '_'.$artId.'.'.$time.'-'.$i.'.xml';
					else # On publie le commentaire directement
						$comment['filename'] =$artId.'.'.$time.'-'.$i.'.xml';
				} while(file_exists($comment['filename']));
				# On peut creer le commentaire
				if($this->addCommentaire($comment)) { # Commentaire OK
					if($this->aConf['mod_com']) # En cours de moderation
						return 'mod';
					else # Commentaire publie directement, on retourne son identifiant
						return 'c'.$time.'-'.$i;
				} else { # Erreur lors de la création du commentaire
					return L_NEWCOMMENT_ERR;
				}
			} else { # Erreur de remplissage des champs obligatoires
				return L_NEWCOMMENT_FIELDS_REQUIRED;
			}
		} else { # Erreur de verification capcha
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}
	}

	/**
	 * Méthode qui crée physiquement le fichier XML du commentaire
	 *
	 * @param	comment	array avec les données du commentaire à ajouter
	 * @return	booléen
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
	 * Méthode qui lance le téléchargement d'un document
	 *
	 * @param	cible	cible de téléchargement cryptée
	 * @return	booleen
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function sendTelechargement($cible) {

		# On décrypte le nom du fichier
		$file = PLX_ROOT.$this->aConf['documents'].plxEncrypt::decryptId($cible);
		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorSendDownload'))) return;
		# On lance le téléchargement et on check le répertoire documents
		if(file_exists($file) AND preg_match('#^'.str_replace('\\', '/', realpath(PLX_ROOT.$this->aConf['documents']).'#'), str_replace('\\', '/', realpath($file)))) {
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
	 * @author	Stéphane F
	 **/
	public function urlRewrite($url='') {

		if($url=='' OR $url=='?') return $this->racine;

		preg_match('/^([0-9a-z\_\-\.\/]+)?[\?]?([0-9a-z\_\-\.\/,&=%]+)?[\#]?(.*)$/i', $url, $args);

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
			return str_replace('&', '&amp;', $this->racine.$new_url);
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
	public function nbArticles($select='all', $userId='[0-9]{3}', $mod='_?', $publi='all') {

		$nb = 0;
		if($select == 'all')
			$motif = '[home|draft|0-9,]*';
		elseif($select=='published')
			$motif = '[home|0-9,]*';
		elseif($select=='draft')
			$motif = '[\w,]*[draft][\w,]*';
		else
			$motif = $select;

		if($arts = $this->plxGlob_arts->query('/^'.$mod.'[0-9]{4}.('.$motif.').'.$userId.'.[0-9]{12}.[a-z0-9-]+.xml$/', 'art', 'sort', 0, false, $publi))
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
			$motif = '/[^[:punct:]?][0-9]{4}.(.*).xml$/';
		elseif($select=='offline')
			$motif = '/^_[0-9]{4}.(.*).xml$/';
		elseif($select=='online')
			$motif = '/^[0-9]{4}.(.*).xml$/';
		else
			$motif = $select;

		if($coms = $this->plxGlob_coms->query($motif,'com','sort',0,false,$publi))
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
				if(preg_match('/^([0-9]{4}).(?:[0-9]|home|,)*(?:'.$this->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.([0-9]{12}).[a-z0-9-]+.xml$/', $filename, $capture)) {
					if($capture[2]<=$datetime) { # on ne prends que les articles publiés
						$this->activeArts[$capture[1]]=1;
					}
				}
			}
		}
	}

}
?>
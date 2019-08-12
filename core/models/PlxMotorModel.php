<?php

/**
 * PlxMotorModel is the PluXml core class
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F, Pedro "P3ter" CADETE
 **/

namespace models;

class PlxMotorModel extends PlxModel {

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

	public $aCats = array(); # Tableau de toutes les catégories
	public $aStats = array(); # Tableau de toutes les pages statiques
	public $aTags = array(); # Tableau des tags
	public $aUsers = array(); #Tableau des utilisateurs
	public $aTemplates = null; # Tableau des templates

	public $PlxGlobModel_arts = null; # Objet PlxGlobModel des articles
	public $PlxGlobModel_coms = null; # Objet PlxGlobModel des commentaires
	public $PlxRecordModel_arts = null; # Objet PlxRecordModel des articles
	public $PlxRecordModel_coms = null; # Objet PlxRecordModel des commentaires
	public $PlxCapchaModel = null; # Objet PlxCapchaModel
	public $PlxErrorModel = null; # Objet PlxErrorModel
	public $PlxPluginsModels = null; # Objet PlxPluginsModels
	
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
			self::$instance = new PlxMotorModel();
		}
		return self::$instance;
	}

	/**
	 * Constructeur qui initialise certaines variables de classe
	 * et qui lance le traitement initial
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F, Pedro "P3ter" CADETE
	 **/
	protected function __construct() {
        parent::__construct();

		# récupération des paramètres dans l'url
		$this->get = PlxUtilsModel::getGets();
		# gestion du timezone
		date_default_timezone_set($this->getPlxConfig()->getConfiguration('timezone'));
		
		# Chargement des variables
		$this->style = $this->getPlxConfig()->getConfiguration('style');
		$this->racine = $this->getPlxConfig()->getConfiguration('racine');
		$this->bypage = $this->getPlxConfig()->getConfiguration('bypage');
		$this->tri = $this->getPlxConfig()->getConfiguration('tri');
		$this->tri_coms = $this->getPlxConfig()->getConfiguration('tri_coms');
		# On récupère le chemin de l'url
		$var = parse_url($this->racine);
		$this->path_url = str_replace(ltrim($var['path'], '\/'), '', ltrim($_SERVER['REQUEST_URI'], '\/'));
		# Traitement des plugins
		# Détermination du fichier de langue (nb: la langue peut être modifiée par plugins via $_SESSION['lang'])
		$context = defined('PLX_ADMIN') ? 'admin_lang' : 'lang';
		$lang = isset($_SESSION[$context]) ? $_SESSION[$context] : $this->getPlxConfig()->getConfiguration('default_lang');
		#--
		$this->PlxPluginsModels = new PlxPluginsModels($lang);
		$this->PlxPluginsModels->loadPlugins();
		# Hook plugins
		eval($this->PlxPluginsModels->callHook('plxMotorConstructLoadPlugins'));
		# Traitement sur les répertoires des articles et des commentaires
		$this->PlxGlobModel_arts = PlxGlobModel::getInstance($this->getPlxConfig()->getConfiguration('racine_articles'),false,true,'arts');
		$this->PlxGlobModel_coms = PlxGlobModel::getInstance($this->getPlxConfig()->getConfiguration('racine_commentaires'));
		# Récupération des données dans les autres fichiers xml
		$this->getCategories($this->getPlxConfig()->getConfiguration('XMLFILE_CATEGORIES'));
		$this->getStatiques($this->getPlxConfig()->getConfiguration('XMLFILE_STATICS'));
		$this->getTags($this->getPlxConfig()->getConfiguration('XMLFILE_TAGS'));
		$this->getUsers($this->getPlxConfig()->getConfiguration('XMLFILE_USERS'));
		# Récuperation des articles appartenant aux catégories actives
		$this->getActiveArts();
		# Hook plugins
		eval($this->PlxPluginsModels->callHook('plxMotorConstruct'));
		# Traitement des templates
		$this->getTemplates($this->getPlxConfig()->getConfigIni('XMLDIR_TEMPLATES'));
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
		if(eval($this->PlxPluginsModels->callHook('plxMotorPreChauffageBegin'))) return;

		if(!$this->get AND $this->getPlxConfig()->getConfiguration('homestatic')!='' AND isset($this->aStats[$this->getPlxConfig()->getConfiguration('homestatic')]) AND $this->aStats[$this->getPlxConfig()->getConfiguration('homestatic')]['active']) {
			$this->mode = 'static'; # Mode static
			$this->cible = $this->getPlxConfig()->getConfiguration('homestatic');
			$this->template = $this->aStats[ $this->cible ]['template'];
		}
		elseif(empty($this->get) 
		    	OR preg_match('@^(blog|blog\/page[0-9]*|\/?page[0-9]*)$@', $this->get) 
		    	OR !preg_match('@^(?:article|static|categorie|archives|tag|preview|telechargement|download)[\b\d/]+@', $this->get)) {
			$this->mode = 'home';
			$this->template = $this->getPlxConfig()->getConfiguration('hometemplate');
			$this->bypage = $this->getPlxConfig()->getConfiguration('bypage'); # Nombre d'article par page
			# On regarde si on a des articles en mode "home"
			if($this->PlxGlobModel_arts->query('/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/')) {
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
				if(!isset($capture[2]) OR $this->PlxRecordModel_arts->f('url')!=$capture[2]) {
					$this->redir301($this->urlRewrite('?article'.intval($this->cible).'/'.$this->PlxRecordModel_arts->f('url')));
				}
			} else {
				$this->error404(L_UNKNOWN_ARTICLE);
			}
		}
		elseif($this->get AND preg_match('/^static([0-9]+)\/?([a-z0-9-]+)?/',$this->get,$capture)) {
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complète sur 3 caractères
			if(!isset($this->aStats[$this->cible]) OR !$this->aStats[$this->cible]['active']) {
				$this->error404(L_UNKNOWN_STATIC);
			} else {
				if(!empty($this->getPlxConfig()->getConfiguration('homestatic')) AND $capture[1]){
					if($this->getPlxConfig()->getConfiguration('homestatic')==$this->cible){
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
					$this->redir301($this->urlRewrite('?categorie'.intval($this->cible).'/'.$this->aCats[$this->cible]['url']));
				}
			} else {
				$this->error404(L_UNKNOWN_CATEGORY);
			}
		}
		elseif($this->get AND preg_match('/^archives\/([0-9]{4})[\/]?([0-9]{2})?[\/]?([0-9]{2})?/',$this->get,$capture)) {
			$this->mode = 'archives';
			$this->template = 'archives.php';
			$this->bypage = $this->getPlxConfig()->getConfiguration('bypage_archives');
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
					$tagUrls = array_map(array('PlxUtilsModel', 'title2url'), $tags);
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
				$this->bypage = $this->getPlxConfig()->getConfiguration('bypage_tags'); # Nombre d'article par page
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
		eval($this->PlxPluginsModels->callHook('plxMotorPreChauffageEnd'));
	}

	/**
	 * Méthode qui fait une redirection de type 301
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function redir301($url) {
		# Hook plugins
		eval($this->PlxPluginsModels->callHook('plxMotorRedir301'));
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
		$this->PlxErrorModel = new PlxErrorModel($msg);
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
		if(eval($this->PlxPluginsModels->callHook('plxMotorDemarrageBegin'))) return;

		if($this->mode == 'home' OR $this->mode == 'categorie' OR $this->mode == 'archives' OR $this->mode == 'tags') {
			$this->getPage(); # Recuperation du numéro de la page courante
			if(!$this->getArticles()) { # Si aucun article
				$this->error404(L_NO_ARTICLE_PAGE);
			}
		}
		elseif($this->mode == 'article') {

			# On a validé le formulaire commentaire
			if(!empty($_POST) AND $this->PlxRecordModel_arts->f('allow_com') AND $this->getPlxConfig()->getConfiguration('allow_com')) {
				# On récupère le retour de la création
				$retour = $this->newCommentaire($this->cible,PlxUtilsModel::unSlash($_POST));
				# Url de l'article
				$url = $this->urlRewrite('?article'.intval($this->PlxRecordModel_arts->f('numero')).'/'.$this->PlxRecordModel_arts->f('url'));
				eval($this->PlxPluginsModels->callHook('plxMotorDemarrageNewCommentaire'));
				if($retour[0] == 'c') { # Le commentaire a été publié
					$_SESSION['msgcom'] = L_COM_PUBLISHED;
					header('Location: '.$url.'#'.$retour);
				} elseif($retour == 'mod') { # Le commentaire est en modération
					$_SESSION['msgcom'] = L_COM_IN_MODERATION;
					header('Location: '.$url.'#form');
				} else {
					$_SESSION['msgcom'] = $retour;
					$_SESSION['msg']['name'] = PlxUtilsModel::unSlash($_POST['name']);
					$_SESSION['msg']['site'] = PlxUtilsModel::unSlash($_POST['site']);
					$_SESSION['msg']['mail'] = PlxUtilsModel::unSlash($_POST['mail']);
					$_SESSION['msg']['content'] = PlxUtilsModel::unSlash($_POST['content']);
					$_SESSION['msg']['parent'] = PlxUtilsModel::unSlash($_POST['parent']);
					eval($this->PlxPluginsModels->callHook('plxMotorDemarrageCommentSessionMessage'));
					header('Location: '.$url.'#form');
				}
				exit;
			}
			# Récupération des commentaires
			$this->getCommentaires('/^'.$this->cible.'.[0-9]{10}-[0-9]+.xml$/',$this->tri_coms);
			$this->template=$this->PlxRecordModel_arts->f('template');
			if($this->getPlxConfig()->getConfiguration('capcha')) $this->PlxCapchaModel = new PlxCapchaModel(); # Création objet captcha
		}
		elseif($this->mode == 'preview') {
			$this->mode='article';
			$this->PlxRecordModel_arts = new PlxRecordModel($_SESSION['preview']);
			$this->template=$this->PlxRecordModel_arts->f('template');
			if($this->getPlxConfig()->getConfiguration('capcha')) $this->PlxCapchaModel = new PlxCapchaModel(); # Création objet captcha
		}

		# Hook plugins
		eval($this->PlxPluginsModels->callHook('plxMotorDemarrageEnd'));
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
				$this->aCats[$number]['name']=PlxUtilsModel::getValue($values[$iTags['name'][$i]]['value']);
				# Recuperation du nom de la description
				$this->aCats[$number]['description']=PlxUtilsModel::getValue($values[$iTags['description'][$i]]['value']);
				# Recuperation de la balise title
				$title_htmltag = PlxUtilsModel::getValue($iTags['title_htmltag'][$i]);
				$this->aCats[$number]['title_htmltag']=PlxUtilsModel::getValue($values[$title_htmltag]['value']);
				# Recuperation du meta description
				$meta_description = PlxUtilsModel::getValue($iTags['meta_description'][$i]);
				$this->aCats[$number]['meta_description']=PlxUtilsModel::getValue($values[$meta_description]['value']);
				# Recuperation du meta keywords
				$meta_keywords = PlxUtilsModel::getValue($iTags['meta_keywords'][$i]);
				$this->aCats[$number]['meta_keywords']=PlxUtilsModel::getValue($values[$meta_keywords]['value']);
				# Recuperation de l'url de la categorie
				$this->aCats[$number]['url']=strtolower($attributes['url']);
				# Recuperation du tri de la categorie si besoin est
				$this->aCats[$number]['tri']=isset($attributes['tri'])?$attributes['tri']:$this->getPlxConfig()->getConfiguration('tri');
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
				$this->aCats[$number]['homepage'] = isset($attributes['homepage']) ? $attributes['homepage'] : 1;
				$this->aCats[$number]['homepage'] = in_array($this->aCats[$number]['homepage'],array('0','1')) ? $this->aCats[$number]['homepage'] : 1;
				if($this->aCats[$number]['active'] AND $this->aCats[$number]['homepage']) $homepageCats[]=$number;
				# Recuperation du nombre d'article de la categorie
				$motif = '/^[0-9]{4}.[home,|0-9,]*'.$number.'[0-9,]*.[0-9]{3}.[0-9]{12}.[A-Za-z0-9-]+.xml$/';
				$arts = $this->PlxGlobModel_arts->query($motif,'art','',0,false,'before');
				$this->aCats[$number]['articles'] = ($arts?sizeof($arts):0);
				# Hook plugins
				eval($this->PlxPluginsModels->callHook('plxMotorGetCategories'));
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
				# Récupération du nom de la page statique
				$this->aStats[$number]['name']=PlxUtilsModel::getValue($values[$iTags['name'][$i]]['value']);
				# Récupération de la balise title
				$title_htmltag = PlxUtilsModel::getValue($iTags['title_htmltag'][$i]);
				$this->aStats[$number]['title_htmltag']=PlxUtilsModel::getValue($values[$title_htmltag]['value']);
				# Récupération du meta description
				$meta_description = PlxUtilsModel::getValue($iTags['meta_description'][$i]);
				$this->aStats[$number]['meta_description']=PlxUtilsModel::getValue($values[$meta_description]['value']);
				# Récupération du meta keywords
				$meta_keywords = PlxUtilsModel::getValue($iTags['meta_keywords'][$i]);
				$this->aStats[$number]['meta_keywords']=PlxUtilsModel::getValue($values[$meta_keywords]['value']);
				# Récupération du groupe de la page statique
				$this->aStats[$number]['group']=PlxUtilsModel::getValue($values[$iTags['group'][$i]]['value']);
				# Récupération de l'url de la page statique
				$this->aStats[$number]['url']=strtolower($attributes['url']);
				# Récupération de l'etat de la page
				$this->aStats[$number]['active']=intval($attributes['active']);
				# On affiche la page statique dans le menu ?
				$this->aStats[$number]['menu']=isset($attributes['menu'])?$attributes['menu']:'oui';
				# Récupération du fichier template
				$this->aStats[$number]['template']=isset($attributes['template'])?$attributes['template']:'static.php';
				# Récupération de la date de création
				$date_creation = PlxUtilsModel::getValue($iTags['date_creation'][$i]);
				$this->aStats[$number]['date_creation']=PlxUtilsModel::getValue($values[$date_creation]['value']);
				# Récupération de la date de mise à jour
				$date_update = PlxUtilsModel::getValue($iTags['date_update'][$i]);
				$this->aStats[$number]['date_update']=PlxUtilsModel::getValue($values[$date_update]['value']);
				# On verifie que la page statique existe bien
				$file = PLX_ROOT.$this->getPlxConfig()->getConfiguration('racine_statiques').$number.'.'.$attributes['url'].'.php';
				# On test si le fichier est lisible
				$this->aStats[$number]['readable'] = (is_readable($file) ? 1 : 0);
				# Hook plugins
				eval($this->PlxPluginsModels->callHook('plxMotorGetStatiques'));
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
				$this->aUsers[$number]['login']=PlxUtilsModel::getValue($values[$iTags['login'][$i]]['value']);
				$this->aUsers[$number]['name']=PlxUtilsModel::getValue($values[$iTags['name'][$i]]['value']);
				$this->aUsers[$number]['password']=PlxUtilsModel::getValue($values[$iTags['password'][$i]]['value']);
				$salt = PlxUtilsModel::getValue($iTags['salt'][$i]);
				$this->aUsers[$number]['salt']=PlxUtilsModel::getValue($values[$salt]['value']);
				$this->aUsers[$number]['infos']=PlxUtilsModel::getValue($values[$iTags['infos'][$i]]['value']);
				$email = PlxUtilsModel::getValue($iTags['email'][$i]);
				$this->aUsers[$number]['email']=PlxUtilsModel::getValue($values[$email]['value']);
				$lang = isset($iTags['lang'][$i]) ? $values[$iTags['lang'][$i]]['value']:'';
				$this->aUsers[$number]['lang'] = $lang!='' ? $lang : $this->getPlxConfig()->getConfiguration('default_lang');
				$this->aUsers[$number]['password_token']=PlxUtilsModel::getValue($values[$iTags['password_token'][$i]]['value']);
				$this->aUsers[$number]['password_token_expiry']=PlxUtilsModel::getValue($values[$iTags['password_token_expiry'][$i]]['value']);
				# Hook plugins
				eval($this->PlxPluginsModels->callHook('plxMotorGetUsers'));
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

		# On calcule la valeur start
		$start = $this->bypage*($this->page-1);
		# On recupere nos fichiers (tries) selon le motif, la pagination, la date de publication
		if($aFiles = $this->PlxGlobModel_arts->query($this->motif,'art',$this->tri,$start,$this->bypage,$publi)) {
			# on mémorise le nombre total d'articles trouvés
			foreach($aFiles as $k=>$v) # On parcourt tous les fichiers
				$array[$k] = $this->parseArticle($this->getPlxConfig()->getConfiguration('racine_articles').$v);
			# On stocke les enregistrements dans un objet PlxRecordModel
			$this->PlxRecordModel_arts = new PlxRecordModel($array);
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
		$parser = xml_parser_create($this->getPlxConfig()->getConfigIni('PLX_CHARSET'));
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# Recuperation des valeurs de nos champs XML
		$art['title'] = PlxUtilsModel::getValue($values[$iTags['title'][0]]['value']);
		$art['allow_com'] = PlxUtilsModel::getValue($values[$iTags['allow_com'][0]]['value']);
		$art['template'] = PlxUtilsModel::getValue($values[$iTags['template'][0]]['value'],'article.php');
		$art['chapo'] = PlxUtilsModel::getValue($values[$iTags['chapo'][0]]['value']);
		$art['content'] = PlxUtilsModel::getValue($values[$iTags['content'][0]]['value']);
		$art['tags'] = PlxUtilsModel::getValue($values[ $iTags['tags'][0] ]['value']);
		$meta_description = PlxUtilsModel::getValue($iTags['meta_description'][0]);
		$art['meta_description'] = PlxUtilsModel::getValue($values[$meta_description]['value']);
		$meta_keywords = PlxUtilsModel::getValue($iTags['meta_keywords'][0]);
		$art['meta_keywords'] = PlxUtilsModel::getValue($values[$meta_keywords]['value']);
		$art['title_htmltag'] = isset($iTags['title_htmltag']) ? PlxUtilsModel::getValue($values[$iTags['title_htmltag'][0]]['value']) : '';
		$art['thumbnail'] = isset($iTags['thumbnail']) ? PlxUtilsModel::getValue($values[$iTags['thumbnail'][0]]['value']) : '';
		$art['thumbnail_title'] = isset($iTags['thumbnail_title']) ? PlxUtilsModel::getValue($values[$iTags['thumbnail_title'][0]]['value']) : '';
		$art['thumbnail_alt'] = isset($iTags['thumbnail_alt']) ? PlxUtilsModel::getValue($values[$iTags['thumbnail_alt'][0]]['value']) : '';
		# Informations obtenues en analysant le nom du fichier
		$art['filename'] = $filename;
		$tmp = $this->artInfoFromFilename($filename);
		$art['numero'] = $tmp['artId'];
		$art['author'] = $tmp['usrId'];
		$art['categorie'] = $tmp['catId'];
		$art['url'] = $tmp['artUrl'];
		$art['date'] = $tmp['artDate'];
		$art['nb_com'] = $this->getNbCommentaires('/^'.$art['numero'].'.[0-9]{10}.[0-9]+.xml$/');
		$art['date_creation'] = isset($iTags['date_creation']) ? PlxUtilsModel::getValue($values[$iTags['date_creation'][0]]['value']) : $art['date'];
		$art['date_update'] = isset($iTags['date_update']) ? PlxUtilsModel::getValue($values[$iTags['date_update'][0]]['value']) : $art['date'];
		# Hook plugins
		eval($this->PlxPluginsModels->callHook('plxMotorParseArticle'));
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

		if($coms = $this->PlxGlobModel_coms->query($motif,'com','',0,false,$publi))
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
		if(preg_match('/([[:punct:]]?)([0-9]{4}).([0-9]{10})-([0-9]+).xml$/',$filename,$capture)) {
			return array(
				'comStatus'	=> $capture[1],
				'artId'		=> $capture[2],
				'comDate'	=> PlxDateModel::timestamp2Date($capture[3]),
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
		$com['author'] = PlxUtilsModel::getValue($values[ $iTags['author'][0]]['value']);
		if(isset($iTags['type']))
			$com['type'] = PlxUtilsModel::getValue($values[ $iTags['type'][0]]['value'],'normal');
		else
			$com['type'] = 'normal';
		$com['ip'] = PlxUtilsModel::getValue($values[$iTags['ip'][0]]['value']);
		$com['mail'] = PlxUtilsModel::getValue($values[$iTags['mail'][0]]['value']);
		$com['site'] = PlxUtilsModel::getValue($values[$iTags['site'][0]]['value']);
		$com['content'] = trim($values[ $iTags['content'][0] ]['value']);
		$com['parent'] = isset($iTags['parent'])?PlxUtilsModel::getValue($values[$iTags['parent'][0]]['value']):'';
		# Informations obtenues en analysant le nom du fichier
		$tmp = $this->comInfoFromFilename(basename($filename));
		$com['status'] = $tmp['comStatus'];
		$com['numero'] = $tmp['comId'];
		$com['article'] = $tmp['artId'];
		$com['date'] = $tmp['comDate'];
		$com['index'] = $tmp['comIdx'];
		# Hook plugins
		eval($this->PlxPluginsModels->callHook('plxMotorParseCommentaire'));
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
	 * Méthode qui enregistre dans un objet PlxRecordModel tous les commentaires
	 * respectant le motif $motif et la limite $limite
	 *
	 * @param	motif	motif de recherche des commentaires
	 * @param	ordre	ordre du tri : sort ou rsort
	 * @param	start	commencement
	 * @param	limite	nombre de commentaires à retourner
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	null
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function getCommentaires($motif,$ordre='sort',$start=0,$limite=false,$publi='before') {

		# On récupère les fichiers des commentaires
		$aFiles = $this->PlxGlobModel_coms->query($motif,'com',$ordre,$start,$limite,$publi);
		if($aFiles) { # On a des fichiers
			foreach($aFiles as $k=>$v)
				$array[$k] = $this->parseCommentaire(PLX_ROOT.$this->getPlxConfig()->getConfiguration('racine_commentaires').$v);

			# hiérarchisation et indentation des commentaires seulement sur les écrans requis
			if( !(defined('PLX_ADMIN') OR defined('PLX_FEED')) OR preg_match('/comment_new/',basename($_SERVER['SCRIPT_NAME']))) {
				$array = $this->parentChildSort_r('index', 'parent', $array);
			}

			# On stocke les enregistrements dans un objet PlxRecordModel
			$this->PlxRecordModel_coms = new PlxRecordModel($array);

			return true;
		}
		else return false;
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
		if($dh = opendir(PLX_ROOT.$this->getPlxConfig()->getConfiguration('racine_commentaires'))) {
			$Idxs = array();
			while(false !== ($file = readdir($dh))) {
				if(preg_match("/_?".$idArt.".[0-9]+-([0-9]+).xml/", $file, $capture)) {
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
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function newCommentaire($artId,$content) {

		# Hook plugins
		if(eval($this->PlxPluginsModels->callHook('plxMotorNewCommentaire'))) return;

		if(strtolower($_SERVER['REQUEST_METHOD'])!= 'post' OR $this->getPlxConfig()->getConfiguration('capcha') AND (!isset($_SESSION["capcha_token"]) OR !isset($_POST['capcha_token']) OR ($_SESSION["capcha_token"]!=$_POST['capcha_token']))) {
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}

		# On vérifie que le capcha est correct
		if($this->getPlxConfig()->getConfiguration('capcha') == 0 OR $_SESSION['capcha'] == sha1($content['rep'])) {
			if(!empty($content['name']) AND !empty($content['content'])) { # Les champs obligatoires sont remplis
				$comment=array();
				$comment['type'] = 'normal';
				$comment['author'] = PlxUtilsModel::strCheck(trim($content['name']));
				$comment['content'] = PlxUtilsModel::strCheck(trim($content['content']));
				# On vérifie le mail
				$comment['mail'] = (PlxUtilsModel::checkMail(trim($content['mail'])))?trim($content['mail']):'';
				# On vérifie le site
				$comment['site'] = (PlxUtilsModel::checkSite($content['site'])?$content['site']:'');
				# On récupère l'adresse IP du posteur
				$comment['ip'] = PlxUtilsModel::getIp();
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
				if($this->getPlxConfig()->getConfiguration('mod_com')) # On modère le commentaire => underscore
					$comment['filename'] = '_'.$artId.'.'.$time.'-'.$idx.'.xml';
				else # On publie le commentaire directement
					$comment['filename'] = $artId.'.'.$time.'-'.$idx.'.xml';
				# On peut créer le commentaire
				if($this->addCommentaire($comment)) { # Commentaire OK
					if($this->getPlxConfig()->getConfiguration('mod_com')) # En cours de modération
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
		# Hook plugins
		if(eval($this->PlxPluginsModels->callHook('plxMotorAddCommentaire'))) return;
		# On genere le contenu de notre fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<comment>\n";
		$xml .= "\t<author><![CDATA[".PlxUtilsModel::cdataCheck($content['author'])."]]></author>\n";
		$xml .= "\t<type>".$content['type']."</type>\n";
		$xml .= "\t<ip>".$content['ip']."</ip>\n";
		$xml .= "\t<mail><![CDATA[".PlxUtilsModel::cdataCheck($content['mail'])."]]></mail>\n";
		$xml .= "\t<site><![CDATA[".PlxUtilsModel::cdataCheck($content['site'])."]]></site>\n";
		$xml .= "\t<content><![CDATA[".PlxUtilsModel::cdataCheck($content['content'])."]]></content>\n";
		$xml .= "\t<parent><![CDATA[".PlxUtilsModel::cdataCheck($content['parent'])."]]></parent>\n";
		# Hook plugins
		eval($this->PlxPluginsModels->callHook('plxMotorAddCommentaireXml'));
		$xml .= "</comment>\n";
		# On ecrit ce contenu dans notre fichier XML
		return PlxUtilsModel::write($xml, PLX_ROOT.$this->getPlxConfig()->getConfiguration('racine_commentaires').$content['filename']);
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
	 * Method in charge of making the template list in $_aTemplate array
	 *
	 * @param  string      the template's folder
	 * @return	
	 * @author	Pedro "P3ter" CADETE
	 **/
	public function getTemplates($templateFolder) {
	    
	    $files = array_diff(scandir($templateFolder), array('..', '.'));
	    foreach ($files as $file) {
	        $this->aTemplates[$file] = new PlxTemplateModel($templateFolder, $file);
	    }
	    
	    return;
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
		$file = PLX_ROOT.$this->getPlxConfig()->getConfiguration('medias').plxEncrypt::decryptId($cible);
		# Hook plugins
		if(eval($this->PlxPluginsModels->callHook('plxMotorSendDownload'))) return;
		# On lance le téléchargement et on check le répertoire medias
		if(file_exists($file) AND preg_match('#^'.str_replace('\\', '/', realpath(PLX_ROOT.$this->getPlxConfig()->getConfiguration('medias')).'#'), str_replace('\\', '/', realpath($file)))) {
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

		preg_match('/^([0-9a-z\_\-\.\/]+)?[\?]?([0-9a-z\_\-\.\/,&=%]+)?[\#]?(.*)$/i', $url, $args);

		if($this->getPlxConfig()->getConfiguration('urlrewriting')) {
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

		if($arts = $this->PlxGlobModel_arts->query('/^'.$mod.'[0-9]{4}.('.$motif.').'.$userId.'.[0-9]{12}.[a-z0-9-]+.xml$/', 'art', '', 0, false, $publi))
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

		if($coms = $this->PlxGlobModel_coms->query($motif,'com','',0,false,$publi))
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
		if($this->PlxGlobModel_arts->aFiles) {
			$datetime=date('YmdHi');
			foreach($this->PlxGlobModel_arts->aFiles as $filename) {
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

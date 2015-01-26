<?php

/**
 * Classe plxShow responsable de l'affichage sur stdout
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/
class plxShow {

	public $plxMotor = false; # Objet plxMotor
	private $lang; # fichier de traduction du theme

	private static $instance = null;

	/**
	 * Méthode qui se charger de créer le Singleton plxShow
	 *
	 * @return	objet			return une instance de la classe plxShow
	 * @author	Stephane F
	 **/
	public static function getInstance(){
		if (!isset(self::$instance))
			self::$instance = new plxShow();
		return self::$instance;
	}

	/**
	 * Constructeur qui initialise l'objet plxMotor par référence
	 *
	 * @param	plxMotor	objet plxMotor passé par référence
	 * @return	null
	 * @author	Florent MONTHEL
	 **/
	protected function __construct() {

		$this->plxMotor = plxMotor::getInstance();

		# Chargement du fichier de lang du theme
		$langfile = PLX_ROOT.$this->plxMotor->aConf['racine_themes'].$this->plxMotor->style.'/lang/'.$this->plxMotor->aConf['default_lang'].'.php';
		if(is_file($langfile)) {
			include($langfile);
			$this->lang = $LANG;
		}
		# Hook Plugins
		eval($this->plxMotor->plxPlugins->callHook('plxShowConstruct'));
	}

	/**
	 * Méthode qui affiche les urls réécrites
	 *
	 * @param	url			url à réécrire
	 * @return	stdout
	 * @author	Stéphane F
	 **/
	public function urlRewrite($url='') {

		echo $this->plxMotor->urlRewrite($url);
	}

	/**
	 * Méthode qui affiche le type de compression http
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function httpEncoding() {

		$encoding = plxUtils::httpEncoding();
		if($this->plxMotor->aConf['gzip'] AND $encoding)
			printf(L_HTTPENCODING, strtoupper($encoding));

	}

	/**
	 * Méthode qui affiche l'URL du site
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function racine() {

		echo $this->plxMotor->racine;
	}

	/**
	 * Méthode qui retourne le mode d'affichage
	 *
	 * @return	string	mode d'affichage (home, article, categorie, static ou erreur)
	 * @scope	global
	 * @author	Stephane F.
	 **/
	public function mode() {

		return $this->plxMotor->mode;
	}

	/**
	 * Méthode qui affiche le charset selon la casse $casse
	 *
	 * @param	casse	casse min ou maj
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function charset($casse='min') {

		if($casse != 'min') # En majuscule
			echo strtoupper(PLX_CHARSET);
		else # En minuscule
			echo strtolower(PLX_CHARSET);
	}

	/**
	 * Méthode qui affiche la version de PluXml
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function version() {

		echo $this->plxMotor->version;
	}

	/**
	 * Méthode qui affiche ou renvoie la langue par défaut
	 *
	 * @param	echo		si à VRAI affichage à l'écran
	 * @return	stdout/string
	 * @author	Stéphane F
	 **/
	public function defaultLang($echo=true) {
		if($echo)
			echo $this->plxMotor->aConf['default_lang'];
		else
			return $this->plxMotor->aConf['default_lang'];
	}


	/**
	 * Méthode qui affiche la variable get de l'objet plxMotor (variable $_GET globale)
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function get() {

		echo $this->plxMotor->get;
	}

	/**
	 * Méthode qui affiche le temps d'exécution de la page
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function chrono() {

		echo round(getMicrotime()-PLX_MICROTIME,3).'s';
	}

	/**
	 * Méthode qui affiche le dossier de stockage du style actif
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function template() {

		echo $this->plxMotor->urlRewrite($this->plxMotor->aConf['racine_themes'].$this->plxMotor->style);

	}

	/**
	 * Méthode qui affiche le titre de la page selon le mode
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function pageTitle() {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowPageTitle'))) return;

		if($this->plxMotor->mode == 'home') {
			if(!empty($this->plxMotor->aConf['description']))
				echo plxUtils::strCheck($this->plxMotor->aConf['title'].' - '.$this->plxMotor->aConf['description']);
			else
				echo plxUtils::strCheck($this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'categorie') {
			$title_htmltag = $this->plxMotor->aCats[$this->plxMotor->cible ]['title_htmltag'];
			if($title_htmltag!='')
				echo plxUtils::strCheck($title_htmltag.' - '.$this->plxMotor->aConf['title']);
			else
				echo plxUtils::strCheck($this->plxMotor->aCats[$this->plxMotor->cible ]['name'].' - '.$this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'article') {
			$title_htmltag = trim($this->plxMotor->plxRecord_arts->f('title_htmltag'));
			if($title_htmltag!='')
				echo plxUtils::strCheck($title_htmltag.' - '.$this->plxMotor->aConf['title']);
			else
				echo plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title').' - '.$this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'static') {
			$title_htmltag = $this->plxMotor->aStats[$this->plxMotor->cible ]['title_htmltag'];
			if($title_htmltag!='')
				echo plxUtils::strCheck($title_htmltag.' - '.$this->plxMotor->aConf['title']);
			else
				echo plxUtils::strCheck($this->plxMotor->aStats[$this->plxMotor->cible ]['name'].' - '.$this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'archives') {
			preg_match('/^(\d{4})(\d{2})?(\d{2})?/',$this->plxMotor->cible, $capture);
			$year = !empty($capture[1]) ? ' '.$capture[1] : '';
			$month = !empty($capture[2]) ? ' '.plxDate::getCalendar('month', $capture[2]) : '';
			$day = !empty($capture[3]) ? ' '.plxDate::getCalendar('day', $capture[3]) : '';
			echo plxUtils::strCheck(L_PAGETITLE_ARCHIVES.$day.$month.$year.' - '.$this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'tags') {
			echo plxUtils::strCheck(L_PAGETITLE_TAG.' '.$this->plxMotor->cibleName.' - '.$this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'erreur') {
			echo plxUtils::strCheck($this->plxMotor->plxErreur->getMessage().' - '.$this->plxMotor->aConf['title']);
			return;
		}
	}

	/**
	 * Méthode qui affiche le meta passé en paramètre
	 *
	 * @param	meta	nom du meta à afficher (description, keywords,author)
	 * @return	stdout
	 * @scope	global
	 * @author	Stéphane F
	 **/
	public function meta($meta='') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowMeta'))) return;

		if(!in_array($meta, array('description','keywords','author')))
			return;

		$meta=strtolower($meta);

		if($this->plxMotor->mode == 'home') {
			if(!empty($this->plxMotor->aConf['meta_'.$meta]))
				echo '<meta name="'.$meta.'" content="'.plxUtils::strCheck($this->plxMotor->aConf['meta_'.$meta]).'" />'."\n";
			return;
		}
		if($this->plxMotor->mode == 'article') {
			if($meta=='author')
				echo '<meta name="author" content="'.$this->artAuthor(false).'" />'."\n";
			else {
				$meta_content=trim($this->plxMotor->plxRecord_arts->f('meta_'.$meta));
				if(!empty($meta_content))
					echo '<meta name="'.$meta.'" content="'.plxUtils::strCheck($meta_content).'" />'."\n";
				elseif(!empty($this->plxMotor->aConf['meta_'.$meta]))
					echo '<meta name="'.$meta.'" content="'.plxUtils::strCheck($this->plxMotor->aConf['meta_'.$meta]).'" />'."\n";
			}
			return;
		}
		if($this->plxMotor->mode == 'static') {
			if(!empty($this->plxMotor->aStats[ $this->plxMotor->cible ]['meta_'.$meta]))
				echo '<meta name="'.$meta.'" content="'.plxUtils::strCheck($this->plxMotor->aStats[ $this->plxMotor->cible ]['meta_'.$meta]).'" />'."\n";
			elseif(!empty($this->plxMotor->aConf['meta_'.$meta]))
				echo '<meta name="'.$meta.'" content="'.plxUtils::strCheck($this->plxMotor->aConf['meta_'.$meta]).'" />'."\n";
			return;
		}
		if($this->plxMotor->mode == 'categorie') {
			if(!empty($this->plxMotor->aCats[ $this->plxMotor->cible ]['meta_'.$meta]))
				echo '<meta name="'.$meta.'" content="'.plxUtils::strCheck($this->plxMotor->aCats[ $this->plxMotor->cible ]['meta_'.$meta]).'" />'."\n";
			elseif(!empty($this->plxMotor->aConf['meta_'.$meta]))
				echo '<meta name="'.$meta.'" content="'.plxUtils::strCheck($this->plxMotor->aConf['meta_'.$meta]).'" />'."\n";
			return;
		}
	}

	/**
	 * Méthode qui affiche le titre du blog linké (variable $type='link') ou non
	 *
	 * @param	type	type d'affichage: texte ou sous forme de lien
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function mainTitle($type='') {

		$title = plxUtils::strCheck($this->plxMotor->aConf['title']);
		if($type == 'link') # Type lien
			echo '<a class="maintitle" href="'.$this->plxMotor->urlRewrite().'" title="'.$title.'">'.$title.'</a>';
		else # Type normal
			echo $title;
	}

	/**
	 * Méthode qui affiche le sous-titre du blog
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function subTitle() {

		echo plxUtils::strCheck($this->plxMotor->aConf['description']);
	}

	/**
	 * Méthode qui affiche la liste des catégories actives.
	 * Si la variable $extra est renseignée, un lien vers la
	 * page d'accueil (nommé $extra) sera mis en place en première
	 * position.
	 *
	 * @param	extra	nom du lien vers la page d'accueil
	 * @param	format	format du texte pour chaque catégorie (variable : #cat_id, #cat_status, #cat_url, #cat_name, #art_nb)
	 * @param	include	liste des catégories à afficher séparées par le caractère | (exemple: 001|003)
	 * @param	exclude	liste des catégories à ne pas afficher séparées par le caractère | (exemple: 002|003)
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function catList($extra='', $format='<li id="#cat_id" class="#cat_status"><a href="#cat_url" title="#cat_name">#cat_name</a></li>', $include='', $exclude='') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowLastCatList'))) return;

		# Si on a la variable extra, on affiche un lien vers la page d'accueil (avec $extra comme nom)
		if($extra != '') {
			$name = str_replace('#cat_id','cat-home',$format);
			$name = str_replace('#cat_url',$this->plxMotor->urlRewrite(),$name);
			$name = str_replace('#cat_name',plxUtils::strCheck($extra),$name);
			$name = str_replace('#cat_status',($this->catId()=='home'?'active':'noactive'), $name);
			$name = str_replace('#art_nb','',$name);
			echo $name;
		}
		# On verifie qu'il y a des categories
		if($this->plxMotor->aCats) {
			foreach($this->plxMotor->aCats as $k=>$v) {
				$in = (empty($include) OR preg_match('/('.$include.')/', $k));
				$ex = (!empty($exclude) AND preg_match('/('.$exclude.')/', $k));
				if($in AND !$ex) {
					if(($v['articles']>0 OR $this->plxMotor->aConf['display_empty_cat']) AND ($v['menu']=='oui') AND $v['active']) { # On a des articles
						# On modifie nos motifs
						$name = str_replace('#cat_id','cat-'.intval($k),$format);
						$name = str_replace('#cat_url',$this->plxMotor->urlRewrite('?categorie'.intval($k).'/'.$v['url']),$name);
						$name = str_replace('#cat_name',plxUtils::strCheck($v['name']),$name);
						$name = str_replace('#cat_status',($this->catId()==intval($k)?'active':'noactive'), $name);
						$name = str_replace('#art_nb',$v['articles'],$name);
						echo $name;
					}
				}
			} # Fin du while
		}
	}

	/**
	 * Méthode qui retourne l'id de la catégorie en question (sans les 0 supplémentaires)
	 *
	 * @return	int ou string
	 * @scope	home,categorie,article,tags,archives
	 * @author	Florent MONTHEL
	 **/
	public function catId() {

		# On va verifier que la categorie existe en mode categorie
		if($this->plxMotor->mode == 'categorie' AND isset($this->plxMotor->aCats[ $this->plxMotor->cible ]))
			return intval($this->plxMotor->cible);
		# On va verifier que la categorie existe en mode article
		if($this->plxMotor->mode == 'article' AND isset($this->plxMotor->aCats[ $this->plxMotor->plxRecord_arts->f('categorie') ]))
			return intval($this->plxMotor->plxRecord_arts->f('categorie'));
		# On va vérifier si c'est la catégorie home
		if($this->plxMotor->mode == 'categorie' OR $this->plxMotor->mode == 'home' OR $this->plxMotor->mode == 'article')
			return 'home';
	}

	/**
	 * Méthode qui affiche le contenu de la description d'une catégorie
	 *
	 * @param	format	format du texte à afficher (variable: #cat_description)
	 * @return	stdout
	 * @scope	categorie
	 * @author	Stephane F.
	 **/
	public function catDescription($format='<div class="infos">#cat_description</div>') {

		$desc = plxUtils::getValue($this->plxMotor->aCats[$this->plxMotor->cible]['description']);
		if($this->plxMotor->mode AND $desc)
			echo str_replace('#cat_description',$desc, $format);
	}

	/**
	 * Méthode qui retourne l'url d'une catégorie
	 *
	 * @param	id			id de la categorie sous la forme numérique ou formatée (ex: 1 ou 001)
	 * @return	string
	 * @author	Stephane F.
	 **/
	public function catUrl($id) {

		$id=str_pad($id,3,'0',STR_PAD_LEFT);
		if(isset($this->plxMotor->aCats[$id])) {
			return $this->plxMotor->urlRewrite('?categorie'.intval($id).'/'.$this->plxMotor->aCats[$id]['url']);
		}
	}

	/**
	 * Méthode qui affiche le nom de la catégorie active (linké ou non)
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function catName($type='') {

		# On va verifier que la categorie existe en mode categorie
		if($this->plxMotor->mode == 'categorie' AND isset($this->plxMotor->aCats[$this->plxMotor->cible])) {
			# On recupere les infos de la categorie
			$id = $this->plxMotor->cible;
			$name = plxUtils::strCheck($this->plxMotor->aCats[$id]['name']);
			$url = $this->catUrl($id);
			# On effectue l'affichage
			if($type == 'link')
				echo '<a href="'.$url.'" title="'.$name.'">'.$name.'</a>';
			else
				echo $name;
		}
		# On va verifier que la categorie existe en mode article
		elseif($this->plxMotor->mode == 'article' AND isset($this->plxMotor->aCats[$this->plxMotor->plxRecord_arts->f('categorie')])) {
			# On recupere les infos de la categorie
			$id = $this->plxMotor->plxRecord_arts->f('categorie');
			$name = plxUtils::strCheck($this->plxMotor->aCats[ $id ]['name']);
			$url = $this->catUrl($id);
			# On effectue l'affichage
			if($type == 'link')
				echo '<a href="'.$url.'" title="'.$name.'">'.$name.'</a>';
			else
				echo $name;
		}
		# Mode home
		elseif($this->plxMotor->mode == 'home') {
			if($type == 'link')
				echo '<a href="'.$this->plxMotor->urlRewrite().'" title="'.plxUtils::strCheck($this->plxMotor->aConf['title']).'">'.L_HOMEPAGE.'</a>';
			else
				echo L_HOMEPAGE;
		} else {
			echo L_UNCLASSIFIED;
		}
	}

	/**
	 * Méthode qui retourne l'identifiant de l'article en question (sans les 0 supplémentaires)
	 *
	 * @return	int
	 * @scope	home,categorie,article,tags,archives
	 * @author	Florent MONTHEL
	 **/
	public function artId() {

		return intval($this->plxMotor->plxRecord_arts->f('numero'));
	}

	/**
	 * Méthode qui affiche l'url de l'article de type relatif ou absolu
	 *
	 * @param	type (deprecated)	type de lien : relatif ou absolu
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function artUrl($type='') {

		# On affiche l'URL
		$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
		$url = $this->plxMotor->plxRecord_arts->f('url');
		echo $this->plxMotor->urlRewrite('?article'.$id.'/'.$url);
	}

	/**
	 * Méthode qui affiche le titre de l'article linké (variable $type='link') ou non
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function artTitle($type='') {

		if($type == 'link') { # Type lien
			$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
			$url = $this->plxMotor->plxRecord_arts->f('url');
			# On effectue l'affichage
			echo '<a href="'.$this->plxMotor->urlRewrite('?article'.$id.'/'.$url).'" title="'.$title.'">'.$title.'</a>';
		} else { # Type normal
			echo plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
		}
	}

	/**
	 * Méthode qui affiche ou renvoie l'auteur de l'article
	 *
	 * @param echo si à VRAI affichage à l'écran
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Stephane F
	 **/
	public function artAuthor($echo=true) {

		if(isset($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['name']))
			$author = plxUtils::strCheck($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['name']);
		else
			$author = L_ARTAUTHOR_UNKNOWN;
		if($echo)
			echo $author;
		else
			return $author;
	}

	/**
	 * Méthode qui affiche l'adresse email de l'auteur de l'article
	 *
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Stephane F
	 **/
	public function artAuthorEmail() {

		if(isset($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['email']))
			echo plxUtils::strCheck($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['email']);
	}

	/**
	 * Méthode qui affiche les informations sur l'auteur de l'article
	 *
	 * @param	format	format du texte à afficher (variable: #art_authorinfos, #art_author)
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Stephane F
	 **/

	public function artAuthorInfos($format='<div class="infos">#art_authorinfos</div>') {

		$infos = plxUtils::getValue($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['infos']);
		if(trim($infos)!='') {
			$txt = str_replace('#art_authorinfos', $infos, $format);
			$txt = str_replace('#art_author', $this->artAuthor(false), $txt);
			echo $txt;
		}
	}

	/**
	 * Méthode qui affiche la date de publication de l'article selon le format choisi
	 *
	 * @param	format	format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2))
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Stephane F.
	 **/
	public function artDate($format='#day #num_day #month #num_year(4)') {

		echo plxDate::formatDate($this->plxMotor->plxRecord_arts->f('date'),$format);
	}

	/**
	 * Méthode qui retourne la liste des catégories de l'article séparées par des virgules
	 *
	 * @return	string
	 * @scope	home,categorie,article,tags,archives
	 * @author	Stephane F
	 **/
	public function artCatIds() {

		return $this->plxMotor->plxRecord_arts->f('categorie');
	}

	/**
	 * Méthode qui retourne un tableau contenant les numéros des catégories actives de l'article
	 *
	 * @return	array
	 * @scope	home,categorie,article,tags,archives
	 * @author	Stephane F
	 **/
	public function artActiveCatIds() {

		$artCatIds = explode(',', $this->plxMotor->plxRecord_arts->f('categorie'));
		$activeCats = explode('|',$this->plxMotor->activeCats);
		return array_intersect($artCatIds,$activeCats);

	}

	/**
	 * Méthode qui affiche la liste des catégories l'article sous forme de lien
	 * ou la chaîne de caractère 'Non classé' si la catégorie
	 * de l'article n'existe pas
	 *
	 * @param	separator	caractère de séparation entre les catégories affichées
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function artCat($separator=', ') {

		$cats = array();
		# Initialisation de notre variable interne
		$catIds = $this->artActiveCatIds();
		foreach ($catIds as $idx => $catId) {
			# On verifie que la categorie n'est pas "home"
			if($catId != 'home') {
				# On va verifier que la categorie existe
				if(isset($this->plxMotor->aCats[ $catId ])) {
					# On recupere les infos de la categorie
					$name = plxUtils::strCheck($this->plxMotor->aCats[ $catId ]['name']);
					$url = $this->plxMotor->aCats[ $catId ]['url'];
					if(isset($this->plxMotor->aCats[ $this->plxMotor->cible ]['url']))
						$active = $this->plxMotor->aCats[ $this->plxMotor->cible ]['url']==$url?"active":"noactive";
					else
						$active = "noactive";
					# On effectue l'affichage
					$cats[] = '<a class="'.$active.'" href="'.$this->plxMotor->urlRewrite('?categorie'.intval($catId).'/'.$url).'" title="'.$name.'">'.$name.'</a>';
				} else { # La categorie n'existe pas
					$cats[] =  L_UNCLASSIFIED;
				}
			} else { # Categorie "home"
				$cats[] = '<a class="active" href="'.$this->plxMotor->urlRewrite().'" title="'.L_HOMEPAGE.'">'.L_HOMEPAGE.'</a>';
			}
		}
		echo implode($separator, $cats);
	}

	/**
	 * Méthode qui affiche la liste des tags l'article sous forme de lien
	 *
	 * @param	format	format du texte pour chaque tag (variable : #tag_status, #tag_url, #tag_name)
	 * @param	separator	caractère de séparation entre les tags affichées
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Stephane F
	 **/
	public function artTags($format='<a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a>', $separator=',') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowArtTags'))) return;

		# Initialisation de notre variable interne
		$taglist = $this->plxMotor->plxRecord_arts->f('tags');
		if(!empty($taglist)) {
			$tags = array_map('trim', explode(',', $taglist));
			foreach($tags as $idx => $tag) {
				$t = plxUtils::title2url($tag);
				$name = str_replace('#tag_url',$this->plxMotor->urlRewrite('?tag/'.$t),$format);
				$name = str_replace('#tag_name',plxUtils::strCheck($tag),$name);
				$name = str_replace('#tag_status',(($this->plxMotor->mode=='tags' AND $this->plxMotor->cible==$t)?'active':'noactive'), $name);
				echo $name;
				if ($idx!=sizeof($tags)-1) echo $separator.' ';
			}
		}
		else echo L_ARTTAGS_NONE;
	}

	/**
	 * Méthode qui affiche le lien "Lire la suite" si le chapô de l'article est renseigné
	 *
	 * @param	format	format d'affichage du lien pour lire la suite de l'article (#art_url, #art_title)
	 * @return	stdout
	 * @scope	home,categorie,tags,archives
	 * @author	Stephane F
	 **/
	public function artReadMore($format='') {

		# Affichage du lien "Lire la suite" si un chapo existe
		if($this->plxMotor->plxRecord_arts->f('chapo') != '') {
			$format = ($format=='' ? '<p class="more"><a href="#art_url" title="#art_title">'.L_ARTCHAPO.'</a></p>' : $format);
			if($format) {
				# On recupere les infos de l'article
				$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
				$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
				$url = $this->plxMotor->plxRecord_arts->f('url');
				# Formatage de l'affichage
				$row = str_replace("#art_url", $this->plxMotor->urlRewrite('?article'.$id.'/'.$url), $format);
				$row = str_replace("#art_title", $title, $row);
				echo $row;
			}
		}
	}

	/**
	 * Méthode qui affiche le châpo de l'article ainsi qu'un lien
	 * pour lire la suite de l'article. Si l'article n'a pas de chapô,
	 * le contenu de l'article est affiché (selon paramètres)
	 *
	 * @param	format	format d'affichage du lien pour lire la suite de l'article (#art_title)
	 * @param	content	affichage oui/non du contenu si le chapô est vide
	 * @param 	anchor ancre dans l'article vers laquelle pointer le lien 
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function artChapo($format=L_ARTCHAPO, $content=true, $anchor='') {

		# On verifie qu'un chapo existe
		if($this->plxMotor->plxRecord_arts->f('chapo') != '') {
			# On récupère les infos de l'article
			$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
			$url = $this->plxMotor->plxRecord_arts->f('url');
			# On effectue l'affichage
			echo $this->plxMotor->plxRecord_arts->f('chapo')."\n";
			if($format) {
				$title = str_replace("#art_title", $title, $format);
				echo '<p class="more"><a href="'.$this->plxMotor->urlRewrite('?article'.$id.'/'.$url).($anchor!=''?'#'.$anchor:'').'" title="'.$title.'">'.$title.'</a></p>'."\n";
			}
		} else { # Pas de chapo, affichage du contenu
			if($content === true) {
				echo $this->plxMotor->plxRecord_arts->f('content')."\n";
			}
		}
	}

	/**
	 * Méthode qui affiche le chapô (selon paramètres) suivi du contenu de l'article
	 *
	 * @param	chapo	affichage oui/non du chapo
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Stephane F
	 **/
	public function artContent($chapo=true) {

		if($chapo === true)
			echo $this->plxMotor->plxRecord_arts->f('chapo')."\n"; # Chapo
		echo $this->plxMotor->plxRecord_arts->f('content')."\n";

	}

	/**
	 * Méthode qui affiche un lien vers le fil Rss des articles
	 * d'une catégorie précise (si $categorie renseigné) ou du site tout entier
	 *
	 * @param	type		type de flux (obsolete)
	 * @param	categorie	identifiant (sans les 0) d'une catégorie
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function artFeed($type='rss', $categorie='') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowArtFeed'))) return;

		if($categorie != '' AND is_numeric($categorie)) {
			# Fil Rss des articles d'une catégorie
			$id=str_pad($categorie,3,'0',STR_PAD_LEFT);
			if(isset($this->plxMotor->aCats[$id])) {
				echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss/categorie'.$categorie.'/'.$this->plxMotor->aCats[$id]['url']).'" title="'.L_ARTFEED_RSS_CATEGORY.'">'.L_ARTFEED_RSS_CATEGORY.'</a>';
			}
		} else {
			# Fil Rss des articles
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss').'" title="'.L_ARTFEED_RSS.'">'.L_ARTFEED_RSS.'</a>';
		}
	}

	/**
	 * Méthode qui affiche le nombre de commentaires (sous forme de lien ou non selon le mode) d'un article
	 *
	 * @param	f1		format d'affichage si nombre de commentaire = 0 (#nb pour afficher le nombre de commentaire)
	 * @param	f2		format d'affichage si nombre de commentaire = 1 (#nb pour afficher le nombre de commentaire)
	 * @param	f3		format d'affichage si nombre de commentaire > 1 (#nb pour afficher le nombre de commentaire)
	 * @return	stdout
	 * @scope	home,categorie,article,tags,archives
	 * @author	Stephane F
	 **/
	public function artNbCom($f1='L_NO_COMMENT',$f2='#nb L_COMMENT',$f3='#nb L_COMMENTS') {

		# A t'on besoin d'afficher le nb de commentaires ?
		if(!$this->plxMotor->aConf['allow_com'] OR !$this->plxMotor->plxRecord_arts->f('allow_com'))
			return;

		$nb = intval($this->plxMotor->plxRecord_arts->f('nb_com'));
		$num = intval($this->plxMotor->plxRecord_arts->f('numero'));
		$url = $this->plxMotor->plxRecord_arts->f('url');

		if($nb==0) {
			$txt = str_replace('L_NO_COMMENT', L_NO_COMMENT, $f1);
			$title = $nb.' '.L_NO_COMMENT;
		}
		elseif($nb==1) {
			$txt = str_replace('L_COMMENT', L_COMMENT, $f2);
			$title = $nb.' '.L_COMMENT;
		}
		else {
			$txt = str_replace('L_COMMENTS', L_COMMENTS, $f3);
			$title = $nb.' '.L_COMMENTS;
		}
		$txt = str_replace('#nb',$nb,$txt);

		if($this->plxMotor->mode == 'article')
			echo $txt;
		else
			echo '<a href="'.$this->plxMotor->urlRewrite('?article'.$num.'/'.$url).'#comments" title="'.$title.'">'.$txt.'</a>';

	}

	/**
	 * Méthode qui affiche le nombre total d'articles publiés sur le site.
	 *
	 * @param	f1		format d'affichage si nombre d'article = 0 (#nb pour afficher le nombre de commentaire)
	 * @param	f2		format d'affichage si nombre d'article = 1 (#nb pour afficher le nombre de commentaire)
	 * @param	f3		format d'affichage si nombre d'article > 1 (#nb pour afficher le nombre de commentaire)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function nbAllArt($f1='L_NO_ARTICLE',$f2='#nb L_ARTICLE',$f3='#nb L_ARTICLES') {

		$nb = $this->plxMotor->nbArticles('published', '[0-9]{3}', '', 'before');

		if($nb==0)
			$txt = str_replace('L_NO_ARTICLE', L_NO_ARTICLE, $f1);
		elseif($nb==1)
			$txt = str_replace('L_ARTICLE', L_ARTICLE, $f2);
		else
			$txt = str_replace('L_ARTICLES', L_ARTICLES, $f3);

		$txt = str_replace('#nb',$nb,$txt);

		echo $txt;
	}

	/**
	 * Méthode qui affiche la liste des $max derniers articles.
	 * Si la variable $cat_id est renseignée, seuls les articles de cette catégorie sont retournés.
	 * On tient compte si la catégorie est active
	 *
	 * @param	format	format du texte pour chaque article
	 * @param	max		nombre d'articles maximum
	 * @param	cat_id	ids des catégories cible
	 * @param   ending	texte à ajouter en fin de ligne
	 * @param	sort	tri de l'affichage des articles (sort|rsort|alpha)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function lastArtList($format='<li><a href="#art_url" title="#art_title">#art_title</a></li>',$max=5,$cat_id='',$ending='', $sort='rsort') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowLastArtList'))) return;
		# Génération de notre motif
		if(empty($cat_id))
			$motif = '/^[0-9]{4}.(?:[0-9]|home|,)*(?:'.$this->plxMotor->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		else
			$motif = '/^[0-9]{4}.((?:[0-9]|home|,)*(?:'.str_pad($cat_id,3,'0',STR_PAD_LEFT).')(?:[0-9]|home|,)*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';

		# Nouvel objet plxGlob et récupération des fichiers
		$plxGlob_arts = clone $this->plxMotor->plxGlob_arts;
		if($aFiles = $plxGlob_arts->query($motif,'art',$sort,0,$max,'before')) {
			foreach($aFiles as $v) { # On parcourt tous les fichiers
				$art = $this->plxMotor->parseArticle(PLX_ROOT.$this->plxMotor->aConf['racine_articles'].$v);
				$num = intval($art['numero']);
				$date = $art['date'];
				if(($this->plxMotor->mode == 'article') AND ($art['numero'] == $this->plxMotor->cible))
					$status = 'active';
				else
					$status = 'noactive';
				# Mise en forme de la liste des catégories
				$catList = array();
				$catIds = explode(',', $art['categorie']);
				foreach ($catIds as $idx => $catId) {
					if(isset($this->plxMotor->aCats[$catId])) { # La catégorie existe
						$catName = plxUtils::strCheck($this->plxMotor->aCats[$catId]['name']);
						$catUrl = $this->plxMotor->aCats[$catId]['url'];
						$catList[] = '<a title="'.$catName.'" href="'.$this->plxMotor->urlRewrite('?categorie'.intval($catId).'/'.$catUrl).'">'.$catName.'</a>';
					} else {
						$catList[] = L_UNCLASSIFIED;
					}
				}
				# On modifie nos motifs
				$row = str_replace('#art_id',$num,$format);
				$row = str_replace('#cat_list', implode(', ',$catList), $row);
				$row = str_replace('#art_url',$this->plxMotor->urlRewrite('?article'.$num.'/'.$art['url']),$row);
				$row = str_replace('#art_status',$status,$row);
				$author = plxUtils::getValue($this->plxMotor->aUsers[$art['author']]['name']);
				$row = str_replace('#art_author',plxUtils::strCheck($author),$row);
				$row = str_replace('#art_title',plxUtils::strCheck($art['title']),$row);
				$strlength = preg_match('/#art_chapo\(([0-9]+)\)/',$row,$capture) ? $capture[1] : '100';
				$chapo = plxUtils::truncate($art['chapo'],$strlength,$ending,true,true);
				$row = str_replace('#art_chapo('.$strlength.')','#art_chapo', $row);
				$row = str_replace('#art_chapo',$chapo,$row);
				$strlength = preg_match('/#art_content\(([0-9]+)\)/',$row,$capture) ? $capture[1] : '100';
				$content = plxUtils::truncate($art['content'],$strlength,$ending,true,true);
				$row = str_replace('#art_content('.$strlength.')','#art_content', $row);
				$row = str_replace('#art_content',$content, $row);
				$row = str_replace('#art_date',plxDate::formatDate($date,'#num_day/#num_month/#num_year(4)'),$row);
				$row = str_replace('#art_hour',plxDate::formatDate($date,'#hour:#minute'),$row);
				$row = plxDate::formatDate($date,$row);
				$row = str_replace('#art_nbcoms',$art['nb_com'], $row);
				# On genère notre ligne
				echo $row;
			}
		}
	}

	/**
	 * Méthode qui affiche l'id du commentaire précédé de la lettre 'c'
	 *
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comId() {

		echo 'c'.$this->plxMotor->plxRecord_coms->f('numero');
	}

	/**
	 * Méthode qui affiche l'url du commentaire de type relatif ou absolu
	 *
	 * @param	type	type de lien : relatif ou absolu (URL complète) DEPRECATED
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function comUrl($type='relatif') {

		# On affiche l'URL
		$id = $this->plxMotor->plxRecord_coms->f('numero');
		$artId = $this->plxMotor->plxRecord_coms->f('article');
		$artInfo = $this->plxMotor->artInfoFromFilename($this->plxMotor->plxGlob_arts->aFiles[$artId]);
		echo $this->urlRewrite('?article'.intval($artId).'/'.$artInfo['artUrl'].'#c'.$id);
	}

	/**
	 * Méthode qui affiche le nombre total de commentaires publiés sur le site.
	 *
	 * @param	f1		format d'affichage si nombre de commentaire = 0 (#nb pour afficher le nombre de commentaire)
	 * @param	f2		format d'affichage si nombre de commentaire = 1 (#nb pour afficher le nombre de commentaire)
	 * @param	f3		format d'affichage si nombre de commentaire > 1 (#nb pour afficher le nombre de commentaire)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function nbAllCom($f1='L_NO_COMMENT',$f2='#nb L_COMMENT',$f3='#nb L_COMMENTS') {

		$nb = $this->plxMotor->nbComments('online', 'before');

		if($nb==0)
			$txt = str_replace('L_NO_COMMENT', L_NO_COMMENT, $f1);
		elseif($nb==1)
			$txt = str_replace('L_COMMENT', L_COMMENT, $f2);
		else
			$txt = str_replace('L_COMMENTS', L_COMMENTS, $f3);

		$txt = str_replace('#nb',$nb,$txt);

		echo $txt;
	}

	/**
	 * Méthode qui affiche l'auteur du commentaires linké ou non
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	article
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Stephane F.
	 **/
	public function comAuthor($type='') {

		# Initialisation de nos variables interne
		$author = $this->plxMotor->plxRecord_coms->f('author');
		$site = $this->plxMotor->plxRecord_coms->f('site');
		if($type == 'link' AND $site != '') # Type lien
			echo '<a rel="nofollow" href="'.$site.'" title="'.$author.'">'.$author.'</a>';
		else # Type normal
			echo $author;
	}

	/**
	 * Méthode qui affiche le type du commentaire (admin ou normal)
	 *
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comType() {

		echo $this->plxMotor->plxRecord_coms->f('type');
	}

	/**
	 * Méthode qui affiche la date de publication d'un commentaire selon le format choisi
	 *
	 * @param	format	format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(2), #num_year(4))
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL et Stephane F
	 **/
	public function comDate($format='#day #num_day #month #num_year(4) &agrave; #hour:#minute') {

		echo plxDate::formatDate($this->plxMotor->plxRecord_coms->f('date'),$format);
	}

	/**
	 * Méthode qui affiche le contenu d'un commentaire
	 *
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comContent() {

		echo nl2br($this->plxMotor->plxRecord_coms->f('content'));
	}

	/**
	 * Méthode qui affiche si besoin le message généré par le système
	 * suite à la création d'un commentaire
	 * @param			format  format du texte à afficher (variable: #com_message)
	 * @return		stdout
	 * @scope			article
	 * @author		Stephane F.
	**/
	public function comMessage($format='#com_message') {

		if(isset($_SESSION['msgcom']) AND !empty($_SESSION['msgcom'])) {
			$row = str_replace('#com_message',$_SESSION['msgcom'],$format);
			echo $row;
			$_SESSION['msgcom']='';
		}

	}

	/**
	 * Méthode qui affiche si besoin la variable $_GET[$key] suite au dépôt d'un commentaire
	 *
	 * @param	key		clé du tableau GET
	 * @param	defaut	valeur par défaut si variable vide
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comGet($key,$defaut='') {

		if(isset($_SESSION['msg'][$key]) AND !empty($_SESSION['msg'][$key])) {
			echo plxUtils::strCheck($_SESSION['msg'][$key]);
			$_SESSION['msg'][$key]='';
		}
		else echo $defaut;

	}

	/**
	 * Méthode qui affiche un lien vers le fil Rss des commentaires
	 * d'un article précis (si $article renseigné) ou du site tout entier
	 *
	 * @param	type		type de flux (obsolete)
	 * @param	article	identifiant (sans les 0) d'un article
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function comFeed($type='rss', $article='') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowComFeed'))) return;

		if($article != '' AND is_numeric($article)) # Fil Rss des commentaires d'un article
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss/commentaires/article'.$article).'" title="'.L_COMFEED_RSS_ARTICLE.'">'.L_COMFEED_RSS_ARTICLE.'</a>';
		else # Fil Rss des commentaires global
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss/commentaires').'" title="'.L_COMFEED_RSS.'">'.L_COMFEED_RSS.'</a>';
	}

	/**
	 * Méthode qui affiche la liste des $max derniers commentaires.
	 * Si la variable $art_id est renseignée, seuls les commentaires de cet article sont retournés.
	 *
	 * @param	format	format du texte pour chaque commentaire
	 * @param	max		nombre de commentaires maximum
	 * @param	art_id	id de l'article cible (24,3)
	 * @param	cat_ids	liste des categories pour filtrer les derniers commentaires (sous la forme 001|002)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function lastComList($format='<li><a href="#com_url">#com_author L_SAID :</a><br/>#com_content(50)</li>',$max=5,$art_id='',$cat_ids='') {

		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowLastComList'))) return;

		# Génération de notre motif
		if(empty($art_id))
			$motif = '/^[0-9]{4}.[0-9]{10}-[0-9]+.xml$/';
		else
			$motif = '/^'.str_pad($art_id,4,'0',STR_PAD_LEFT).'.[0-9]{10}-[0-9]+.xml$/';

		$count=1;
		$datetime=date('YmdHi');
		# Nouvel objet plxGlob et récupération des fichiers
		$plxGlob_coms = clone $this->plxMotor->plxGlob_coms;
		if($aFiles = $plxGlob_coms->query($motif,'com','rsort',0,false,'before')) {
			$aComArtTitles = array(); # tableau contenant les titres des articles
			$isComArtTitle = (strpos($format, '#com_art_title')!=FALSE) ? true : false;
			# On parcourt les fichiers des commentaires
			foreach($aFiles as $v) {
				# On filtre si le commentaire appartient à un article d'une catégorie inactive
				if(isset($this->plxMotor->activeArts[substr($v,0,4)])) {
					$com = $this->plxMotor->parseCommentaire(PLX_ROOT.$this->plxMotor->aConf['racine_commentaires'].$v);
					$artInfo = $this->plxMotor->artInfoFromFilename($this->plxMotor->plxGlob_arts->aFiles[$com['article']]);
					if($artInfo['artDate']<=$datetime) { # on ne prends que les commentaires pour les articles publiés
						if(empty($cat_ids) OR preg_match('/('.$cat_ids.')/', $artInfo['catId'])) {
							$url = '?article'.intval($com['article']).'/'.$artInfo['artUrl'].'#c'.$com['numero'];
							$date = $com['date'];
							$content = strip_tags($com['content']);
							# On modifie nos motifs
							$row = str_replace('L_SAID', L_SAID, $format);
							$row = str_replace('#com_id',$com['numero'],$row);
							$row = str_replace('#com_url',$this->plxMotor->urlRewrite($url),$row);
							$row = str_replace('#com_author',$com['author'],$row);
							while(preg_match('/#com_content\(([0-9]+)\)/',$row,$capture)) {
								if($com['author'] == 'admin')
									$row = str_replace('#com_content('.$capture[1].')',plxUtils::strCut($content,$capture[1]),$row);
								else
									$row = str_replace('#com_content('.$capture[1].')',plxUtils::strCheck(plxUtils::strCut(plxUtils::strRevCheck($content),$capture[1])),$row);
							}
							$row = str_replace('#com_content',$content,$row);
							$row = str_replace('#com_date',plxDate::formatDate($date,'#num_day/#num_month/#num_year(4)'),$row);
							$row = str_replace('#com_hour',plxDate::formatDate($date,'#hour:#minute'),$row);
							$row = plxDate::formatDate($date,$row);
							# récupération du titre de l'article
							if($isComArtTitle) {
								if(isset($aComArtTitles[$com['article']])) {
									$row = str_replace('#com_art_title',$aComArtTitles[$com['article']],$row);
								}
								else {
									if($file = $this->plxMotor->plxGlob_arts->query('/^'.$com['article'].'.(.*).xml$/')) {
										$art = $this->plxMotor->parseArticle(PLX_ROOT.$this->plxMotor->aConf['racine_articles'].$file[0]);
										$aComArtTitles[$com['article']] = $art_title = $art['title'];
										$row = str_replace('#com_art_title',$art_title,$row);
									}
								}
							}
							# On genère notre ligne
							echo $row;
							$count++;
						}
					}
				}
				if($count>$max) break;
			}
		}
	}

	/**
	 * Méthode qui affiche la liste des pages statiques.
	 *
	 * @param	extra			si renseigné: nom du lien vers la page d'accueil affiché en première position
	 * @param	format			format du texte pour chaque page (variable : #static_id, #static_status, #static_url, #static_name, #group_id, #group_class, #group_name)
	 * @param	format_group	format du texte pour chaque groupe (variable : #group_class, #group_name)
	 * @param	menublog		position du menu Blog (si non renseigné le menu n'est pas affiché)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function staticList($extra='', $format='<li id="#static_id" class="#static_class"><a href="#static_url" class="#static_status" title="#static_name">#static_name</a></li>', $format_group='<span class="#group_class">#group_name</span>', $menublog=false) {

		$menus = array();
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowStaticListBegin'))) return;
		$home = ((empty($this->plxMotor->get) OR preg_match('/^page[0-9]*/',$this->plxMotor->get)) AND basename($_SERVER['SCRIPT_NAME'])=="index.php");
		# Si on a la variable extra, on affiche un lien vers la page d'accueil (avec $extra comme nom)
		if($extra != '') {
			$stat = str_replace('#static_id','static-home',$format);
			$stat = str_replace('#static_class','static-group',$stat);
			$stat = str_replace('#static_url',$this->plxMotor->urlRewrite(),$stat);
			$stat = str_replace('#static_name',plxUtils::strCheck($extra),$stat);
			$stat = str_replace('#static_status',($home==true?"active":"noactive"), $stat);
			$menus[][] = $stat;
		}
		if($this->plxMotor->aStats) {
			foreach($this->plxMotor->aStats as $k=>$v) {
				if($v['active'] == 1 AND $v['menu'] == 'oui') { # La page  est bien active et dispo ds le menu
					$stat = str_replace('#static_id','static-'.intval($k),$format);
					$stat = str_replace('#static_class','static-menu',$stat);
					if($v['url'][0]=='?') # url interne commençant par ?
						$stat = str_replace('#static_url',$this->plxMotor->urlRewrite($v['url']),$stat);
					elseif(plxUtils::checkSite($v['url'],false)) # url externe en http ou autre
						$stat = str_replace('#static_url',$v['url'],$stat);
					else # url page statique
						$stat = str_replace('#static_url',$this->plxMotor->urlRewrite('?static'.intval($k).'/'.$v['url']),$stat);
					$stat = str_replace('#static_name',plxUtils::strCheck($v['name']),$stat);
					$stat = str_replace('#static_status',(($home===false AND $this->staticId()==intval($k))?'static active':'noactive'), $stat);
					if($v['group']=='')
						$menus[][] =  $stat;
					else
						$menus[$v['group']][] =  $stat;
				}
			}
		}
		if($menublog) {
			if($this->plxMotor->aConf['homestatic']!='' AND isset($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']])) {
				if($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']]['active']) {
					$menu = str_replace('#static_id','page-blog',$format);
					if ($this->plxMotor->get AND preg_match('/^(blog|categorie|archives|tag|article)/', $_SERVER['QUERY_STRING'])) {
						$menu = str_replace('#static_status','active',$menu);
					} else {
						$menu = str_replace('#static_status','noactive',$menu);
					}
					$menu = str_replace('#static_url', $this->plxMotor->urlRewrite('?blog'),$menu);
					$menu = str_replace('#static_name',L_PAGEBLOG_TITLE,$menu);
					$menu = str_replace('#static_class','',$menu);
					array_splice($menus, (intval($menublog)-1), 0, array($menu));
				}
			}
		}

		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowStaticListEnd'))) return;

		# Affichage des pages statiques + menu Accueil et Blog
		if($menus) {
			foreach($menus as $k=>$v) {
				if(is_numeric($k)) {
					echo "\n".(is_array($v) ? $v[0] : $v);
				}
				else {
					$group = str_replace('#group_id','static-group-'.plxUtils::title2url($k),$format_group);
					$group = str_replace('#group_class','static group',$group);
					$group = str_replace('#group_name',plxUtils::strCheck($k),$group);
					echo "\n<li>\n\t".$group."\n\t<ul id=\"static-".plxUtils::title2url($k)."\">\t\t";
					foreach($v as $kk => $vv) {
						echo "\n\t\t".$vv;
					}
					echo "\n\t</ul>\n</li>\n";
				}
			}
			echo "\n";
		}

	}

	/**
	 * Méthode qui retourne l'id de la page statique active
	 *
	 * @return	int
	 * @scope	static
	 * @author	Florent MONTHEL
	 **/
	public function staticId() {

		# On va verifier que la categorie existe en mode categorie
		if($this->plxMotor->mode == 'static' AND isset($this->plxMotor->aStats[ $this->plxMotor->cible ]))
			return intval($this->plxMotor->cible);
	}

	/**
	 * Méthode qui affiche l'url de la page statique de type relatif ou absolu
	 *
	 * @param	type	type de lien : relatif ou absolu (URL complète)
	 * @return	stdout
	 * @scope	static
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function staticUrl($type='relatif') {

		# Recupération ID URL
		$staticId = $this->staticId();
		$staticIdFill = str_pad($staticId,3,'0',STR_PAD_LEFT);
		if(!empty($staticId) AND isset($this->plxMotor->aStats[ $staticIdFill ]))
			echo $this->plxMotor->urlRewrite('?static'.$staticId.'/'.$this->plxMotor->aStats[ $staticIdFill ]['url']);
	}

	/**
	 * Méthode qui affiche le titre de la page statique
	 *
	 * @return	stdout
	 * @scope	static
	 * @author	Florent MONTHEL
	 **/
	public function staticTitle() {

		echo plxUtils::strCheck($this->plxMotor->aStats[ $this->plxMotor->cible ]['name']);
	}

	/**
	 * Méthode qui affiche le groupe de la page statique
	 *
	 * @return	stdout
	 * @scope	static
	 * @author	Stéphane F.
	 **/
	public function staticGroup() {

		echo plxUtils::strCheck($this->plxMotor->aStats[ $this->plxMotor->cible ]['group']);
	}

	/**
	 * Méthode qui affiche la date de la dernière modification de la page statique selon le format choisi
	 *
	 * @param	format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2))
	 * @return	stdout
	 * @scope	static
	 * @author	Anthony T.
	 **/
	public function staticDate($format='#day #num_day #month #num_year(4)') {

		# On genere le nom du fichier dont on veux récupérer la date
		$file = PLX_ROOT.$this->plxMotor->aConf['racine_statiques'].$this->plxMotor->cible;
		$file .= '.'.$this->plxMotor->aStats[ $this->plxMotor->cible ]['url'].'.php';
		# Test de l'existence du fichier
		if(!file_exists($file)) return;
		# On récupère la date de la dernière modification du fichier qu'on formate
		echo plxDate::formatDate(date('YmdHi', filemtime($file)), $format);
	}

	/**
	 * Méthode qui inclut le code source de la page statique
	 *
	 * @return	stdout
	 * @scope	static
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function staticContent() {

		# On va verifier que la page a inclure est lisible
		if($this->plxMotor->aStats[ $this->plxMotor->cible ]['readable'] == 1) {
			# On genere le nom du fichier a inclure
			$file = PLX_ROOT.$this->plxMotor->aConf['racine_statiques'].$this->plxMotor->cible;
			$file .= '.'.$this->plxMotor->aStats[ $this->plxMotor->cible ]['url'].'.php';
			# Inclusion du fichier
			ob_start();
			require $file;
			$output = ob_get_clean();
			eval($this->plxMotor->plxPlugins->callHook('plxShowStaticContent'));
			echo $output;
		} else {
			echo '<p>'.L_STATICCONTENT_INPROCESS.'</p>';
		}

	}

	/**
	 * Méthode qui affiche une page statique en lui passant son id (si cette page est active ou non)
	 *
	 * @param	id	id numérique de la page statique
	 * @return	stdout
	 * @scope	global
	 * @author	Stéphane F
	 **/
	public function staticInclude($id) {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowStaticInclude'))) return ;
		# On génère un nouvel objet plxGlob
		$plxGlob_stats = plxGlob::getInstance(PLX_ROOT.$this->plxMotor->aConf['racine_statiques']);
		if($files = $plxGlob_stats->query('/^'.str_pad($id,3,'0',STR_PAD_LEFT).'.[a-z0-9-]+.php$/')) {
			include(PLX_ROOT.$this->plxMotor->aConf['racine_statiques'].$files[0]);
		}
	}

	/**
	 * Méthode qui affiche la pagination
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function pagination() {

		$plxGlob_arts = clone $this->plxMotor->plxGlob_arts;
		$aFiles = $plxGlob_arts->query($this->plxMotor->motif,'art','',0,false,'before');

		if($aFiles AND $this->plxMotor->bypage AND sizeof($aFiles)>$this->plxMotor->bypage) {

			# on supprime le n° de page courante dans l'url
			$arg_url = $this->plxMotor->get;
			if(preg_match('/(\/?page[0-9]+)$/',$arg_url,$capture)) {
				$arg_url = str_replace($capture[1], '', $arg_url);
			}
			# Calcul des pages
			$prev_page = $this->plxMotor->page - 1;
			$next_page = $this->plxMotor->page + 1;
			$last_page = ceil(sizeof($aFiles)/$this->plxMotor->bypage);
			# Generation des URLs
			$f_url = $this->plxMotor->urlRewrite('?'.$arg_url); # Premiere page
			$arg = (!empty($arg_url) AND $prev_page>1) ? $arg_url.'/' : $arg_url;
			$p_url = $this->plxMotor->urlRewrite('?'.$arg.($prev_page<=1?'':'page'.$prev_page)); # Page precedente
			$arg = !empty($arg_url) ? $arg_url.'/' : $arg_url;
			$n_url = $this->plxMotor->urlRewrite('?'.$arg.'page'.$next_page); # Page suivante
			$l_url = $this->plxMotor->urlRewrite('?'.$arg.'page'.$last_page); # Derniere page

			# Hook Plugins
			if(eval($this->plxMotor->plxPlugins->callHook('plxShowPagination'))) return;

			# On effectue l'affichage
			if($this->plxMotor->page > 2) # Si la page active > 2 on affiche un lien 1ere page
				echo '<span class="p_first"><a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'">'.L_PAGINATION_FIRST.'</a></span>&nbsp;';
			if($this->plxMotor->page > 1) # Si la page active > 1 on affiche un lien page precedente
				echo '<span class="p_prev"><a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'">'.L_PAGINATION_PREVIOUS.'</a></span>&nbsp;';
			# Affichage de la page courante
			printf('<span class="p_page p_current">'.L_PAGINATION.'</span>',$this->plxMotor->page,$last_page);
			if($this->plxMotor->page < $last_page) # Si la page active < derniere page on affiche un lien page suivante
				echo '&nbsp;<span class="p_next"><a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'">'.L_PAGINATION_NEXT.'</a></span>';
			if(($this->plxMotor->page + 1) < $last_page) # Si la page active++ < derniere page on affiche un lien derniere page
				echo '&nbsp;<span class="p_last"><a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'">'.L_PAGINATION_LAST.'</a></span>';
		}
	}

	/**
	 * Méthode qui affiche la question du capcha
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F.
	 **/
	public function capchaQ() {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowCapchaQ'))) return;
		echo $this->plxMotor->plxCapcha->q();
	}

	/**
	 * Méthode qui affiche la réponse du capcha cryptée en sha1
	 * DEPRECATED
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F.
	 **/
	public function capchaR() {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowCapchaR'))) return;
		echo $this->plxMotor->plxCapcha->r();

	}

	/**
	 * Méthode qui affiche le message d'erreur de l'objet plxErreur
	 *
	 * @return	stdout
	 * @scope	erreur
	 * @author	Florent MONTHEL
	 **/
	public function erreurMessage() {

		echo $this->plxMotor->plxErreur->getMessage();
	}

	/**
	 * Méthode qui affiche le nom du tag (linké ou non)
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	tags
	 * @author	Stephane F
	 **/
	public function tagName($type='') {

		if($this->plxMotor->mode == 'tags') {
			$tag = plxUtils::strCheck($this->plxMotor->cible);
			$tagName = plxUtils::strCheck($this->plxMotor->cibleName);
			# On effectue l'affichage
			if($type == 'link')
				echo '<a href="'.$this->plxMotor->urlRewrite('?tag/'.$tag).'" title="'.$tagName.'">'.$tagName.'</a>';
			else
				echo $tagName;
		}
	 }

	/**
	 * Méthode qui affiche un lien vers le fil Rss des articles d'un tag
	 *
	 * @param	type		type de flux (obsolete)
	 * @param	tag			nom du tag
	 * @return				stdout
	 * @scope					home,categorie,article,tags,archives
	 * @author				Stephane F
	 **/

	public function tagFeed($type='rss', $tag='') {

		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowTagFeed'))) return;

		if($tag=='' AND $this->plxMotor->mode == 'tags')
			$tag = $this->plxMotor->cible;

		echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss/tag/'.plxUtils::strCheck($tag)).'" title="'.L_ARTFEED_RSS_TAG.'">'.L_ARTFEED_RSS_TAG.'</a>';

	}

	/**
	 * Méthode qui affiche la liste de tous les tags.
	 *
	 * @param	format	format du texte pour chaque tag (variable : #tag_size #tag_status, #tag_url, #tag_name, #nb_art)
	 * @param	max		nombre maxi de tags à afficher
	 * @param	order	tri des tags (random, alpha, '')
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function tagList($format='<li><a class="#tag_size #tag_status" href="#tag_url" title="#tag_name">#tag_name</a></li>', $max='', $order='') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowTagList'))) return;

		$datetime = date('YmdHi');
		$array=array();
		$alphasort=array();
		# On verifie qu'il y a des tags
		if($this->plxMotor->aTags) {
			# On liste les tags sans créer de doublon
			foreach($this->plxMotor->aTags as $idart => $tag) {
				if(isset($this->plxMotor->activeArts[$idart]) AND $tag['date']<=$datetime AND $tag['active']) {
					if($tags = array_map('trim', explode(',', $tag['tags']))) {
						foreach($tags as $tag) {
							if($tag!='') {
								$t = plxUtils::title2url($tag);
								if(!isset($array['_'.$tag])) {
									$array['_'.$tag]=array('name'=>$tag,'url'=>$t,'count'=>1);
								}
								else
									$array['_'.$tag]['count']++;
								if(!in_array($t, $alphasort))
									$alphasort[] = $t; # pour le tri alpha
							}
						}
					}
				}
			}
			# limite sur le nombre de tags à afficher
			if($max!='') $array=array_slice($array, 0, intval($max), true);
			# tri des tags
			switch($order) {
				case 'alpha':
					if($alphasort) array_multisort($alphasort, SORT_ASC, $array);
					break;
				case 'random':
					$arr_elem = array();
					$keys = array_keys($array);
					shuffle($keys);
					foreach ($keys as $key) {
						$arr_elem[$key] = $array[$key];
					}
					$array = $arr_elem;
					break;
			}
		}
		# On affiche la liste
		$size=0;
		foreach($array as $tagname => $tag) {
			$name = str_replace('#tag_id','tag-'.$size++,$format);
			$name = str_replace('#tag_size','tag-size-'.($tag['count']>10?'max':$tag['count']),$name);
			$name = str_replace('#tag_url',$this->plxMotor->urlRewrite('?tag/'.$tag['url']),$name);
			$name = str_replace('#tag_name',plxUtils::strCheck($tag['name']),$name);
			$name = str_replace('#nb_art',$tag['count'],$name);
			$name = str_replace('#tag_status',(($this->plxMotor->mode=='tags' AND $this->plxMotor->cible==$tag['url'])?'active':'noactive'), $name);
			echo $name;
		}
	}

	/**
	 * Méthode qui affiche la liste des archives
	 *
	 * @param	format	format du texte pour l'affichage (variable : #archives_id, #archives_status, #archives_nbart, #archives_url, #archives_name, #archives_month, #archives_year)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function archList($format='<li id="#archives_id"><a class="#archives_status" href="#archives_url" title="#archives_name">#archives_name</a></li>'){
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowArchList'))) return;

		$curYear=date('Y');
		$array = array();

		$plxGlob_arts = clone $this->plxMotor->plxGlob_arts;

		if($files = $plxGlob_arts->query('/^[0-9]{4}.(?:[0-9]|home|,)*(?:'.$this->plxMotor->activeCats.'|home)(?:[0-9]|home|,)*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/','art','rsort',0,false,'before')) {
			foreach($files as $id => $filename){
				if(preg_match('/([0-9]{4}).((?:[0-9]|home|,)*(?:'.$this->plxMotor->activeCats.'|home)(?:[0-9]|home|,)*).[0-9]{3}.([0-9]{4})([0-9]{2})([0-9]{6}).([a-z0-9-]+).xml$/',$filename,$capture)){
					if($capture[3]==$curYear) {
						if(!isset($array[$capture[3]][$capture[4]])) $array[$capture[3]][$capture[4]]=1;
						else $array[$capture[3]][$capture[4]]++;
					} else {
						if(!isset($array[$capture[3]])) $array[$capture[3]]=1;
						else $array[$capture[3]]++;
					}
				}
			}
			krsort($array);
			# Affichage pour l'année en cours
			if(isset($array[$curYear])) {
				foreach($array[$curYear] as $month => $nbarts){
					$name = str_replace('#archives_id','archives-'.$curYear.$month,$format);
					$name = str_replace('#archives_name',plxDate::getCalendar('month', $month).' '.$curYear,$name);
					$name = str_replace('#archives_year',$curYear,$name);
					$name = str_replace('#archives_month',plxDate::getCalendar('month', $month),$name);
					$name = str_replace('#archives_url', $this->plxMotor->urlRewrite('?archives/'.$curYear.'/'.$month), $name);
					$name = str_replace('#archives_nbart',$nbarts,$name);
					$name = str_replace('#archives_status',(($this->plxMotor->mode=="archives" AND $this->plxMotor->cible==$curYear.$month)?'active':'noactive'), $name);
					echo $name;
				}
			}
			# Affichage pour les années précédentes
			unset($array[$curYear]);
			foreach($array as $year => $nbarts){
				$name = str_replace('#archives_id','archives-'.$year,$format);
				$name = str_replace('#archives_name',$year,$name);
				$name = str_replace('#archives_year',$year,$name);
				$name = str_replace('#archives_month',$year,$name);
				$name = str_replace('#archives_url', $this->plxMotor->urlRewrite('?archives/'.$year), $name);
				$name = str_replace('#archives_nbart',$nbarts,$name);
				$name = str_replace('#archives_status',(($this->plxMotor->mode=="archives" AND $this->plxMotor->cible==$year)?'active':'noactive'), $name);
				echo $name;
			}
		}
	}

	/**
	 * Méthode qui affiche un lien vers la page blog.php
	 *
	 * @param	format	format du texte pour l'affichage (variable : #page_id, #page_status, #page_url, #page_name)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function pageBlog($format='<li id="#page_id"><a class="#page_status" href="#page_url" title="#page_name">#page_name</a></li>') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowPageBlog'))) return;

		if($this->plxMotor->aConf['homestatic']!='' AND isset($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']])) {
			if($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']]['active']) {
				$name = str_replace('#page_id','page-blog',$format);
				if ($this->plxMotor->get AND preg_match('/^(blog|categorie|archives|tag|article)/', $_SERVER['QUERY_STRING'])) {
					$name = str_replace('#page_status','active',$name);
				} else {
					$name = str_replace('#page_status','noactive',$name);
				}
				$name = str_replace('#page_url', $this->plxMotor->urlRewrite('?blog'),$name);
				$name = str_replace('#page_name',L_PAGEBLOG_TITLE,$name);
				echo $name;
			}
		}
	}

	/**
	 * Méthode qui ajoute, s'il existe, le fichier css associé à un template
	 *
	 * @param	css_dir     répertoire de stockage des fichiers css (avec un / à la fin)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function templateCss($css_dir='') {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowTemplateCss'))) return;

		$theme = $this->plxMotor->aConf['racine_themes'].$this->plxMotor->style.'/';
		$css = str_replace('php','css',$this->plxMotor->template);
		if(is_file($theme.$css_dir.$css))
			echo '<link rel="stylesheet" type="text/css" href="'.$this->plxMotor->urlRewrite($theme.$css_dir.$css).'" media="screen" />'."\n";
	}

	/**
	 * Méthode qui ajoute, s'il existe, le fichier css associé aux plugins
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function pluginsCss() {
		# Hook Plugins
		if(eval($this->plxMotor->plxPlugins->callHook('plxShowPluginsCss'))) return;

		$filename = $this->plxMotor->aConf['racine_plugins'].'site.css';
		if(is_file(PLX_ROOT.$filename))
			echo '<link rel="stylesheet" type="text/css" href="'.$this->plxMotor->urlRewrite($filename).'" media="screen" />'."\n";
	}

	/**
	 * Méthode qui affiche une clé de traduction appelée à partir du thème
	 *
	 * @param	$lang	clé de traduction à afficher
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function lang($key='') {
		if(isset($this->lang[$key]))
			echo $this->lang[$key];
		else
			echo $key;
	}

	/**
	 * Méthode qui renvoie une clé de traduction appelée à partir du thème
	 *
	 * @param	$lang	clé de traduction à afficher
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function getLang($key='') {
		if(isset($this->lang[$key]))
			return $this->lang[$key];
		else
			return $key;
	}

	/**
	 * Méthode qui appel un hook à partir du thème
	 *
	 * @param	hookName		nom du hook
	 * @param	parms			parametre ou liste de paramètres sous forme de array
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 *
	 * Exemple:
	 *		# sans return, passage d'un paramètre
	 *		eval($plxShow->callHook('MyPluginFunction', 'AZERTY'));
	 *		# avec return, passage de 2 paramètres à faire sous forme de tableau
	 *		$b = $plxShow->callHook('MyPluginFunction', array('AZERTY', 'QWERTY'));
	 **/
	public function callHook($hookName, $parms=null) {
		$return = $this->plxMotor->plxPlugins->callHook($hookName, $parms);
		if(is_array($return)) {
			ob_start();
			eval($return[0]);
			echo ob_get_clean();
			return $return[1];
		} else {
			return $return;
		}
	}

}
?>
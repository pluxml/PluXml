<?php

/**
 * Classe plxShow responsable de l'affichage sur stdout
 *
 * @package PLX
 * @author    Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE, Jean-Pierre Pourrez @bazooka07
 **/

const PLX_SHOW = true;

class plxShow
{

	const AUTHOR_PATTERN = '<li id="#user_id"><a href="#user_url" class="#user_status" title="#user_name">#user_name</a> (#art_nb)</li>';
	const RSS_FORMAT = '<a class="rss" href="#feedUrl" title="#feedTitle" download>#feedName</a>';
	const STATIC_LIST_FORMAT = '<li class="#static_class #static_status" id="#static_id"><a href="#static_url" title="#static_name">#static_name</a></li>';
	const STATIC_LIST_FORMAT_GROUP = '<span class="#group_class #group_status">#group_name</span>';
	public $plxMotor = false; # Objet plxMotor
	private $lang; # fichier de traduction du theme

	private static $instance = null;

	/**
	 * Méthode qui se charger de créer le Singleton plxShow
	 *
	 * @return    self            return une instance de la classe plxShow
	 * @author    Stephane F
	 **/
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new plxShow();
		return self::$instance;
	}

	/**
	 * Constructeur qui initialise l'objet plxMotor par référence
	 *
	 * @param plxMotor    objet plxMotor passé par référence
	 * @return    null
	 * @author    Florent MONTHEL
	 **/
	protected function __construct()
	{

		$this->plxMotor = plxMotor::getInstance();
		$plxThemes = $this->plxMotor->getPlxThemes();
		$themes = array_values($plxThemes->aThemes);
		if(empty($themes)) {
			header('Content-Type: text/plain; charset=' . PLX_CHARSET);
			echo L_ERR_THEME_NOTFOUND.' (' . PLX_ROOT . $this->plxMotor->aConf['racine_themes'] . $this->plxMotor->style . ') !';
			exit;
		}

		if(!in_array($this->plxMotor->style, $themes)) {
			$this->plxMotor->style = in_array('defaut', $themes) ? 'defaut' : array_values($themes)[0];
		}

		# Chargement du fichier de lang du theme
		$racine_themes = PLX_ROOT . $this->plxMotor->aConf['racine_themes'];
		$langfile = $racine_themes . $this->plxMotor->style . '/lang/' . PLX_SITE_LANG . '.php';
		include $langfile;
		$this->lang = $LANG; # $LANG = tableau contenant les traductions présentes dans le fichier de langue

		# Hook Plugins
		eval($this->plxMotor->plxPlugins->callHook('plxShowConstruct'));

	}

	/**
	 * Méthode qui affiche les urls réécrites
	 *
	 * @param url            url à réécrire
	 * @author    Stéphane F
	 **/
	public function urlRewrite($url = '')
	{

		echo $this->plxMotor->urlRewrite($url);
	}

	/**
	 * Méthode qui affiche le type de compression http
	 *
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function httpEncoding()
	{

		$encoding = plxUtils::httpEncoding();
		if ($this->plxMotor->aConf['gzip'] and $encoding)
			printf(L_HTTPENCODING, strtoupper($encoding));

	}

	/**
	 * Méthode qui affiche l'URL du site
	 *
	 * @scope    global
	 * @author    Florent MONTHEL
	 **/
	public function racine()
	{

		echo $this->plxMotor->racine;
	}

	/**
	 * Méthode qui affiche ou retourne le mode d'affichage
	 *
	 * @param echo    si à VRAI affichage écran (à FAUX par défaut pour gérer la non régression PluXml < 5.6)
	 * @return    string    mode d'affichage (home, article, categorie, static ou erreur)
	 * @scope    global
	 * @author    Stephane F.
	 **/
	public function mode($echo = false)
	{
		if ($echo)
			echo $this->plxMotor->mode;
		else
			return $this->plxMotor->mode;
	}

	/**
	 * Méthode qui affiche le charset selon la casse $casse
	 *
	 * @param casse    casse min ou maj
	 * @scope    global
	 * @author    Florent MONTHEL
	 **/
	public function charset($casse = 'min')
	{
		echo (strtolower($casse) === 'min') ? strtolower(PLX_CHARSET) : strtoupper(PLX_CHARSET);
	}

	/**
	 * Méthode qui affiche la version de PluXml
	 *
	 * @scope    global
	 * @author    Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function version()
	{

		echo PLX_VERSION;
	}

	/**
	 * Méthode qui affiche ou renvoie la langue par défaut
	 *
	 * @param echo        si à VRAI affichage à l'écran
	 * @return    stdout/string
	 * @author    Stéphane F
	 **/
	public function defaultLang($echo = true)
	{
		if ($echo)
			echo PLX_SITE_LANG;
		else
			return PLX_SITE_LANG;
	}


	/**
	 * Méthode qui affiche la variable get de l'objet plxMotor (variable $_GET globale)
	 *
	 * @scope    global
	 * @author    Florent MONTHEL
	 **/
	public function get()
	{

		echo $this->plxMotor->get;
	}

	/**
	 * Méthode qui affiche le temps d'exécution de la page
	 *
	 * @scope    global
	 * @author    Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function chrono()
	{

		echo round(getMicrotime() - PLX_MICROTIME, 3) . 's';
	}

	/**
	 * Méthode qui affiche le dossier de stockage du style actif
	 *
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function template()
	{

		echo $this->plxMotor->urlRewrite($this->plxMotor->aConf['racine_themes'] . $this->plxMotor->style);

	}

	/**
	 * Méthode qui affiche le titre de la page selon le mode
	 *
	 * @parm    format        format d'affichage (ex: home=#title - #subtitle;article=#title)
	 *                        paramètres: home, categorie, article, static, archives, tags, erreur
	 * @parm    sep            caractère de séparation dans le format d'affichage entre les paramètres
	 * @scope    global
	 * @author    Stéphane F
	 **/
	public function pageTitle($format = '', $sep = ";")
	{

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowPageTitle'))) return;

		# valeur par défaut
			$subtitle = $this->plxMotor->aConf['title'];

		switch($this->plxMotor->mode) {
			case 'article':
				$title_htmltag = trim($this->plxMotor->plxRecord_arts->f('title_htmltag'));
				$title = !empty($title_htmltag) ? $title_htmltag : $this->plxMotor->plxRecord_arts->f('title');
				break;
			case 'categorie':
				$title_htmltag = trim($this->plxMotor->aCats[$this->plxMotor->cible]['title_htmltag']);
				$title = !empty($title_htmltag) ? $title_htmltag : $this->plxMotor->aCats[$this->plxMotor->cible]['name'];
				break;
			case 'tags':
			$title = L_PAGETITLE_TAG . ' ' . $this->plxMotor->cibleName;
				break;
			case 'user':
				$title = $this->plxMotor->aUsers[$this->plxMotor->cible]['name'];
				break;
			case 'home':
				$title = $this->plxMotor->aConf['title'];
				$subtitle = $this->plxMotor->aConf['description'];
				break;
			case 'static':
				$title_htmltag = trim($this->plxMotor->aStats[$this->plxMotor->cible]['title_htmltag']);
				$title = !empty($title_htmltag) ? $title_htmltag : $this->plxMotor->aStats[$this->plxMotor->cible]['name'];
				break;
			case 'archives':
				preg_match('/^(\d{4})(\d{2})?(\d{2})?/', $this->plxMotor->cible, $captures);
				$year = !empty($captures[1]) ? ' ' . $captures[1] : '';
				$month = !empty($captures[2]) ? ' ' . plxDate::getCalendar('month', $captures[2]) : '';
				$day = !empty($captures[3]) ? ' ' . plxDate::getCalendar('day', $captures[3]) : '';
				$title = L_PAGETITLE_ARCHIVES . $day . $month . $year;
				break;
			case 'erreur':
				$title = $this->plxMotor->plxErreur->getMessage();
				break;
			default:
				$title = $this->plxMotor->aConf['title'];
				$subtitle = $this->plxMotor->aConf['description'];
		}

		if (preg_match('/' . $this->plxMotor->mode . '\s*=\s*(.*?)\s*(' . $sep . '|$)/i', $format, $captures)) {
			$format = trim($captures[1]);
		} else {
			$format = '#title - #subtitle';
		}
		$txt = strtr($format, array(
			'#title'    => strip_tags(trim($title)),
			'#subtitle' => strip_tags(trim($subtitle)),
		));
		echo plxUtils::strCheck(trim($txt, ' - '));
	}

	/**
	 * Méthode qui retourne l'url cannonique de la page selon le mode
	 *
	 * https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls?hl=fr
	 * @scope	global
	 * @return	string
	 * @author	Jean-Pierre Pourrez
	 **/
	public function pageUrl() {
		return !empty($this->plxMotor->get) ? $this->plxMotor->racine . 'index.php?' . $this->plxMotor->get : $this->plxMotor->racine;
	}

	/**
	 * Méthode qui affiche le meta passé en paramètre
	 *
	 * @param meta    nom du meta à afficher (description, keywords,author)
	 * @param echo    affiche le résultat ou le renvoie
	 * @scope    global
	 * @author    Stéphane F, Pedro "P3ter" CADETE, Philippe M., Jean-Pierre Pourrez "bazooka07"
	 **/
	public function meta($meta, $echo = true)
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowMeta'))) return;

		$meta = strtolower($meta);
		if (
			!in_array($meta, array('description', 'keywords', 'author')) or
			($meta == 'author' and $this->plxMotor->mode != 'article')
		) {
			return;
		}

		$k = 'meta_' . $meta;
		switch($this->plxMotor->mode) {
			case 'home':
				$content = $this->plxMotor->aConf[$k];
				break;
			case 'tags':
				$content = $this->plxMotor->aConf[$k];
				if($meta == 'keywords') {
					$content = $this->plxMotor->cible;
					if(!empty(trim($content))) {
						$content .= ',' . $this->plxMotor->aConf[$k];
					}
				}
				break;
			case 'article':
				if ($meta == 'author') {
					$content = $this->artAuthor(false);
				} else {
					$content = $this->plxMotor->plxRecord_arts->f($k);
				}
				break;
			case 'static':
				$content = $this->plxMotor->aStats[$this->plxMotor->cible][$k];
				break;
			case 'categorie':
				$content = $this->plxMotor->aCats[$this->plxMotor->cible][$k];
				break;
			default:
				return;
		}

		if (!empty($content)) {
			$content = plxUtils::strCheck($content);
			if($echo) {
?>
	<meta name="<?= $meta ?>" content="<?= $content ?>" />
<?php
			} else {
				return $content;
			}
		} elseif ($echo) {
			return '';
		}
	}

	function meta_all() {
		foreach(['description', 'keywords', 'author'] as $meta) {
			$this->meta($meta);
		}
	}

	/**
	 * Méthode qui affiche le titre du blog linké (variable $type='link') ou non
	 *
	 * @param type    type d'affichage: texte ou sous forme de lien
	 * @scope    global
	 * @author    Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function mainTitle($type = '')
	{

		$title = plxUtils::strCheck($this->plxMotor->aConf['title']);
		if ($type == 'link') {
			# Type lien
?>
<a class="maintitle" href="<?= $this->plxMotor->urlRewrite() ?>" title="<?= $title ?>"><?= $title ?></a>
<?php
		} else {
			# Type normal
			echo $title;
		}
	}

	/**
	 * Méthode qui affiche le sous-titre du blog
	 *
	 * @scope    global
	 * @author    Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function subTitle()
	{

		echo plxUtils::strCheck($this->plxMotor->aConf['description']);
	}

	/**
	 * Méthode qui affiche la liste des catégories actives.
	 * Si la variable $extra est renseignée, un lien vers la
	 * page d'accueil (nommé $extra) sera mis en place en première
	 * position.
	 *
	 * @param string $extra nom du lien vers la page d'accueil
	 * @param string $format format du texte pour chaque catégorie (variable : #cat_id, #cat_status, #cat_url, #cat_name, #cat_description, #art_nb)
	 * @param string $include liste des n° de catégories à afficher, séparés par un ou plusieurs caractères (exemple: '001 |003 5, 45|50')
	 * @param string $exclude liste des catégories à ne pas afficher
	 * @scope    global
	 * @author    Anthony GUÉRIN, Florent MONTHEL, Stephane F, Jean-Pierre Pourrez "bazooka07"
	 **/
	public function catList($extra = '', $format = '<li id="#cat_id" class="#cat_status"><a href="#cat_url" title="#cat_name">#cat_name</a></li>', $include = '', $exclude = '')
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowLastCatList'))) return;

		# Si on a la variable extra, on affiche un lien vers la page d'accueil (avec $extra comme nom)
		if (!empty($extra)) {
			echo strtr($format, array(
				'#cat_id' => 'cat-home',
				'#cat_url' => $this->plxMotor->urlRewrite(),
				'#cat_name' => plxUtils::strCheck($extra),
				'#cat_status' => ($this->catId() == 'home') ? 'active' : 'noactive',
				'#art_nb' => '',
			));
		}

		# On verifie qu'il y a des categories
		if ($this->plxMotor->aCats) {
			$currentCats = $this->catId(true);
			foreach ($this->plxMotor->aCats as $idCatStr => $v) {
				# On vérifie qu'on peut afficher cette catégorie et qu'elle est active
				if (in_array($v['menu'], array('oui', 1)) && $v['active']) {
					$idCatNum = intval($idCatStr);
					$pattern = '@\b0*' . $idCatNum . '\b@';
					if (empty($include) or preg_match($pattern, $include)) {
						if (empty($exclude) || !preg_match($pattern, $exclude)) {
							if ($v['articles'] > 0 || $this->plxMotor->aConf['display_empty_cat']) {
								# on a des articles pour cette catégorie ou on affiche les catégories sans article
								# On modifie nos motifs
								echo strtr($format, array(
									'#cat_id' => 'cat-' . $idCatNum,
									'#cat_url' => $this->plxMotor->urlRewrite('?' . L_CATEGORY_URL . $idCatNum . '/' . $v['url']),
									'#cat_name' => plxUtils::strCheck($v['name']),
									'#cat_status' => !empty($currentCats) && in_array($idCatStr, $currentCats) ? 'active' : 'noactive',
									'#cat_description' => plxUtils::strCheck($v['description']),
									'#art_nb' => $v['articles'],
								));
							}
						}
					}
				}
			} # Fin du while
		}
	}

	/**
	 * Méthode qui retourne les id de catégorie pour la categorie ou l'article en cours
	 *
	 * @param bool asArray retourne le resultat sous forme d'un tableau de chaines au lieu d'une ".CSV" chaine
	 * @return    string or array
	 * @scope    home,categorie,article,tags,archives
	 * @author    Florent MONTHEL, Jean-Pierre Pourrez "bazooka07"
	 **/
	public function catId($asArray = false)
	{

		switch ($this->plxMotor->mode) {
			case 'categorie':
				# On vérifie que la categorie
				if ($asArray) {
					return isset($this->plxMotor->aCats[$this->plxMotor->cible]) ? array($this->plxMotor->cible) : array('home');
				} else {
					return isset($this->plxMotor->aCats[$this->plxMotor->cible]) ? $this->plxMotor->cible : 'home';
				}
				break;
			case 'article':
				$artCatsStr = $this->plxMotor->plxRecord_arts->f('categorie');
				if (empty($artCatsStr)) {
					return $asArray ? array('home') : 'home';
				}
				$aCats = $this->plxMotor->aCats;
				# On vérifie que les categories de l'article existe et sont actives
				$activeCats = array_filter(explode(',', $artCatsStr), function ($idCat) use ($aCats) {
					return array_key_exists($idCat, $aCats) && in_array($aCats[$idCat]['active'], array('oui', 1));
				});
				# categorie 'home' par défaut si échec
				if (empty($activeCats)) {
					$aCats = array('home');
				}
				return $asArray ? $activeCats : implode(',', $activeCats);
				break;
			case 'home':
				return $asArray ? array('home') : 'home';
				break;
			default:
				# Pas categorie pour ce mode
				return false;
		}
	}

	/**
	 * Méthode qui affiche le contenu de la description d'une catégorie
	 *
	 * @param format    format du texte à afficher (variable: #cat_description)
	 * @scope    categorie
	 * @author    Stephane F.
	 **/
	public function catDescription($format = '<div class="description">#cat_description</div>')
	{
		if($this->plxMotor->mode == 'categorie') {
			$desc = plxUtils::getValue($this->plxMotor->aCats[$this->plxMotor->cible]['description']);
			if ($this->plxMotor->mode and $desc) {
				echo str_replace('#cat_description', $desc, $format);
			}
		}
	}

	/**
	 * Méthode qui retourne l'url d'une catégorie
	 *
	 * @param id            id de la categorie sous la forme numérique ou formatée (ex: 1 ou 001)
	 * @return    string
	 * @author    Stephane F.
	 **/
	public function catUrl($id)
	{

		$id = str_pad($id, 3, '0', STR_PAD_LEFT);
		if (isset($this->plxMotor->aCats[$id])) {
			return $this->plxMotor->urlRewrite('?' . L_CATEGORY_URL . intval($id) . '/' . $this->plxMotor->aCats[$id]['url']);
		}
	}

	/**
	 * Méthode qui affiche le nom de la catégorie active (linké ou non)
	 *
	 * @param type    type d'affichage : link => sous forme de lien, '' affichage direct, autre valeur retourne le nom
	 * @scope    home,categorie,article,tags,archives
	 * @author    Florent MONTHEL, Stephane F
	 **/
	public function catName($type = '')
	{

		switch($this->plxMotor->mode) {
			case 'categorie':
				if (isset($this->plxMotor->aCats[$this->plxMotor->cible])) {
					# On recupere les infos de la categorie
					$id = $this->plxMotor->cible;
					$name = plxUtils::strCheck($this->plxMotor->aCats[$id]['name']);
					$href = $this->catUrl($id);
				}
				break;
			case 'article':
				if (isset($this->plxMotor->aCats[$this->plxMotor->plxRecord_arts->f('categorie')])) {
					# On recupere les infos de la categorie
					$id = $this->plxMotor->plxRecord_arts->f('categorie');
					$name = plxUtils::strCheck($this->plxMotor->aCats[$id]['name']);
					$href = $this->catUrl($id);
				}
				break;
			case 'home':
				$name = plxUtils::strCheck($this->plxMotor->aConf['title']);
				$href = $this->plxMotor->urlRewrite();
				$caption = L_HOMEPAGE;
				break;
			default:
		}

		if (empty($href)) {
			$caption = L_UNCLASSIFIED;
		} elseif (empty($caption)) {
			$caption = $name;
		}

		if (empty(trim($type))) {
			echo $caption;
		} elseif(strtolower($type) == 'link') {
			if (!empty($href)) {
?>
<a href="<?= $href ?>" title="<?= $title ?></a>"><?= $caption ?></a>
<?php
			} else {
				echo $caption;
			}
		} else {
			return !empty($href) ? $caption : '';
		}
	}

	/**
	 * Méthode qui affiche l'image d'accroche d'une catégorie
	 *
	 * @param format    format d'affichage (variables: #img_url, #img_thumb_url, #img_alt, #img_title)
	 * @param echo    si à VRAI affichage à l'écran
	 * @return    string
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F, Philippe-M
	 **/
	public function catThumbnail($format = '<p><a href="#img_url"><img class="cat_thumbnail" src="#img_thumb_url" alt="#img_alt" title="#img_title" /></a></p>', $echo = true)
	{
		$filename = plxUtils::getValue($this->plxMotor->aCats[$this->plxMotor->cible]['thumbnail']);
		if (!empty($filename)) {
			$img_url = $this->plxMotor->urlRewrite($filename);
			$img_thumb = plxUtils::thumbName($filename);
			$replaces = [
				'#img_url' => $img_url, # #img_url
				'#img_thumb_url' => (file_exists(PLX_ROOT . $img_thumb)) ? $this->plxMotor->urlRewrite($img_thumb) : $img_url, # #img_thumb_url
				'#img_title' => plxUtils::strCheck(plxUtils::getValue($this->plxMotor->aCats[$this->plxMotor->cible]['thumbnail_title'])), # #img_title
				'#img_alt' => plxUtils::strCheck(plxUtils::getValue($this->plxMotor->aCats[$this->plxMotor->cible]['thumbnail_alt'])) # #img_alt
			];

			if ($echo)
				echo strtr($format, $replaces);
			else
				return strtr($format, $replaces);
		} elseif (!$echo) {
			return false;
		}
	}

	/**
	 * Méthode qui retourne l'identifiant de l'article en question (sans les 0 supplémentaires)
	 *
	 * @return    int
	 * @scope    home,categorie,article,tags,archives
	 * @author    Florent MONTHEL
	 **/
	public function artId()
	{

		return intval($this->plxMotor->plxRecord_arts->f('numero'));
	}

	/**
	 * Méthode qui affiche ou retourne l'url de l'article
	 *
	 * @param echo    si à VRAI affichage à l'écran
	 * @param extra    paramètres supplémentaires pouvant être rajoutés à la fin de l'url de l'atricle
	 * @scope    home,categorie,article,tags,archives
	 * @author    Florent MONTHEL, Stephane F
	 **/
	public function artUrl($echo = true, $extra = '')
	{

		# On affiche l'URL
		$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
		$url = $this->plxMotor->urlRewrite('?' . L_ARTICLE_URL . $id . '/' . $this->plxMotor->plxRecord_arts->f('url') . $extra);
		if ($echo)
			echo $url;
		else
			return $url;

	}

	/**
	 * Méthode qui affiche le titre de l'article linké (variable $type='link') ou non
	 *
	 * @param type    type d'affichage : link => sous forme de lien
	 * @scope    home,categorie,article,tags,archives
	 * @author    Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function artTitle($type = '')
	{

		if ($type == 'link') { # Type lien
			$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
			$url = $this->plxMotor->plxRecord_arts->f('url');
			# On effectue l'affichage
?>
<a href="<?= $this->plxMotor->urlRewrite('?' . L_ARTICLE_URL . $id . '/' . $url) ?>" title="<?= $title ?>"><?= $title ?></a>
<?php
		} else { # Type normal
			echo plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
		}
	}

	/**
	 * Méthode qui affiche l'image d'accroche d'un article avec un lien vers l'article ou vers l'image
	 *
	 * @param string $format format d'affichage (variables: #img_url, #img_thumb_url, #img_alt, #img_title)
	 * @param bool $echo si à VRAI affichage à l'écran
	 * @param bool $article si vrai, #img_url pointe sur l'article à la place de l'image
	 * @return    bool|string
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F, Thatoo, J.P. Pourrez (bazooka07))
	 **/
	public function artThumbnail($format = '<a href="#img_url"><img class="art_thumbnail" src="#img_thumb_url" alt="#img_alt" title="#img_title" /></a>', $echo = true, $article = false)
	{

		$filename = trim($this->plxMotor->plxRecord_arts->f('thumbnail'));

		if (!empty($filename)) {
			$imgUrl = $this->plxMotor->urlRewrite($filename);
			$imgThumb = plxUtils::thumbName($filename);
		} else {
			$imgUrl = '';
			$echo = false;
		}

		if ($article) {
			$artId = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$artUrl = $this->plxMotor->plxRecord_arts->f('url');
			$url = $this->plxMotor->urlRewrite('?' . L_ARTICLE_URL . $artId . '/' . $artUrl);
		} else {
			$url = $imgUrl;
		}

		$result = strtr($format, array(
			'#img_url' => $url,
			'#img_thumb_url' => (!empty($imgThumb) and file_exists(PLX_ROOT . $imgThumb)) ? $this->plxMotor->urlRewrite($imgThumb) : $imgUrl,
			'#img_title' => plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('thumbnail_title')),
			'#img_alt' => $this->plxMotor->plxRecord_arts->f('thumbnail_alt')
		));

		if ($echo)
			echo $result;
		else
			return $result;
	}

	/**
	 * Méthode qui affiche ou renvoie l'auteur de l'article
	 *
	 * @param echo si à VRAI affichage à l'écran
	 * @param link affiche un lien vers tous les articles de l'auteur si vrai
	 * @scope    home,categorie,article,tags,archives
	 * @author    Anthony GUÉRIN, Florent MONTHEL, Stephane F, Jean-Pierre Pourrez "bazooka07"
	 **/
	public function artAuthor($echo = true, $link=true)
	{
		$authorId = $this->plxMotor->plxRecord_arts->f('author');
		if (isset($this->plxMotor->aUsers[$authorId]['name'])) {
			$author = $this->plxMotor->aUsers[$authorId];
			$authorName = plxUtils::strCheck($author['name']);
		} else {
			$authorName = L_ARTAUTHOR_UNKNOWN;
		}

		if (!$echo) {
			return $authorName;
		}

		if($link and !empty($author)) {
			$href = 'index.php?' . L_USER_URL . $authorId . '/' . plxUtils::urlify($authorName);
?>
<a href="<?= $this->plxMotor->urlRewrite($href) ?>"><?= $authorName ?></a>
<?php
		} else {
			echo $authorName;
		}
	}

	/**
	 * Méthode qui affiche l'adresse email de l'auteur de l'article
	 *
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F
	 **/
	public function artAuthorEmail()
	{

		if (isset($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['email']))
			echo plxUtils::strCheck($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['email']);
	}

	/**
	 * Méthode qui affiche les informations sur l'auteur de l'article
	 *
	 * @param format    format du texte à afficher (variable: #art_authorinfos, #art_author)
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F
	 **/

	public function artAuthorInfos($format = '<div class="infos">#art_authorinfos</div>')
	{

		$infos = plxUtils::getValue($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['infos']);
		if (trim($infos) != '') {
			echo strtr($format, array(
				'#art_authorinfos'    => $infos,
				'#art_author'        => $this->artAuthor(false),
			));
		}
	}

	/**
	 * Méthode qui affiche la date de publication de l'article selon le format choisi
	 *
	 * @param format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2), #time)
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F.
	 **/
	public function artDate($format = '#day #num_day #month #num_year(4)')
	{

		echo plxDate::formatDate($this->plxMotor->plxRecord_arts->f('date'), $format);
	}

	/**
	 * Méthode qui retourne la liste des catégories de l'article séparées par des virgules
	 *
	 * @return    string
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F
	 **/
	public function artCatIds()
	{

		return $this->plxMotor->plxRecord_arts->f('categorie');
	}

	/**
	 * Méthode qui retourne un tableau contenant les numéros des catégories actives de l'article
	 *
	 * @return    array
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F
	 **/
	public function artActiveCatIds()
	{

		$artCatIds = explode(',', $this->plxMotor->plxRecord_arts->f('categorie'));
		$activeCats = explode('|', $this->plxMotor->activeCats);
		return array_intersect($artCatIds, $activeCats);

	}

	/**
	 * Méthode qui affiche la liste des catégories l'article sous forme de lien
	 * ou la chaîne de caractère 'Non classé' si la catégorie
	 * de l'article n'existe pas
	 *
	 * @param separator    caractère de séparation entre les catégories affichées
	 * @scope    home,categorie,article,tags,archives
	 * @author    Anthony GUÉRIN, Florent MONTHEL, Stephane F, J.P. Pourrez "bazooka07"
	 **/
	public function artCat($separator = ', ')
	{

		$cats = array();
		foreach ($this->artActiveCatIds() as $idx => $catId) {
			# On valide si la categorie est "home"
			if ($catId == 'home') {
				$name = L_HOMEPAGE;
				$href = '';
				$active = ($this->plxMotor->mode == 'home');
			} elseif(isset($this->plxMotor->aCats[$catId])) {
				# La catégorie existe. On en récupère les infos
				$name = plxUtils::strCheck($this->plxMotor->aCats[$catId]['name']);
				$url = $this->plxMotor->aCats[$catId]['url'];
				$href = '?' . L_CATEGORY_URL . intval($catId) . '/' . $url;
				$active = (
					$this->plxMotor->mode == 'categorie' and
					isset($this->plxMotor->aCats[$this->plxMotor->cible]['url']) and
					$url == $this->plxMotor->aCats[$this->plxMotor->cible]['url']
				);
			} else {
				# Rien à faire
				continue;
			}

			# On mémorise pour afficher
			$className = $active ? 'active' : 'noactive';
			$cats[] = '<a class="' . $className . '" href="' . $this->plxMotor->urlRewrite($href) . '" title="' . $name . '">' . $name . '</a>';
		}

		# si $cats est vide, on n'a trouvé aucune catégorie valide
		echo !empty($cats) ? implode($separator, $cats) : L_UNCLASSIFIED;
	}

	/**
	 * Méthode qui vérifie si  un article est épinglé
	 *
	 * @param	  string name of the class to add if pinned article
	 * @return    string 'pin' or ''
	 * @author    J.P. Pourrez "bazooka07"
	 **/
	public function artPinClass($value='pin') {
		return preg_match('#^pin,#', $this->artCatIds()) ? $value : '';
	}

	/**
	 * Méthode qui affiche la liste des tags l'article sous forme de lien
	 *
	 * @param format    format du texte pour chaque tag (variable : #tag_status, #tag_url, #tag_name)
	 * @param separator    caractère de séparation entre les tags affichées
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F
	 **/
	public function artTags($format = '<a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a>', $separator = ',')
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowArtTags'))) return;

		# Initialisation de notre variable interne
		$taglist = $this->plxMotor->plxRecord_arts->f('tags');
		if (!empty($taglist)) {
			$tags = array_map('trim', explode(',', $taglist));
			foreach ($tags as $idx=>$tag) {
				$t = plxUtils::urlify($tag);
				$replaces = array(
					'#tag_url'    => $this->plxMotor->urlRewrite('?' . L_TAG_URL . '/' . $t),
					'#tag_name'   => plxUtils::strCheck($tag),
					'#tag_status' => (($this->plxMotor->mode == 'tags' and $this->plxMotor->cible == $t) ? 'active' : 'noactive'),
				);
				echo strtr($format, $replaces);
				if ($idx != sizeof($tags) - 1) echo $separator . ' ';
			}
		} else echo L_ARTTAGS_NONE;
	}

	/**
	 * Méthode qui affiche le lien "Lire la suite" si le chapô de l'article est renseigné
	 *
	 * @param format    format d'affichage du lien pour lire la suite de l'article (#art_url, #art_title)
	 * @scope    home,categorie,tags,archives
	 * @author    Stephane F
	 **/
	public function artReadMore($format = '')
	{

		# Affichage du lien "Lire la suite" si un chapo existe
		if ($this->plxMotor->plxRecord_arts->f('chapo') != '') {
			$format = ($format == '' ? '<p class="more"><a href="#art_url" title="#art_title">' . L_ARTCHAPO . '</a></p>' : $format);
			if ($format) {
				# On recupere les infos de l'article
				$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
				$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
				$url = $this->plxMotor->plxRecord_arts->f('url');
				# Formatage de l'affichage
				echo strtr($format, [
					'#art_url' => $this->plxMotor->urlRewrite('?' . L_ARTICLE_URL . $id . '/' . $url),
					'#art_title' => $title,
				]);
			}
		}
	}

	/**
	 * Méthode qui affiche le châpo de l'article ainsi qu'un lien
	 * pour lire la suite de l'article. Si l'article n'a pas de chapô,
	 * le contenu de l'article est affiché (selon paramètres)
	 *
	 * @param format    format d'affichage du lien pour lire la suite de l'article (#art_title)
	 * @param content    affichage oui/non du contenu si le chapô est vide
	 * @param anchor    ancre dans l'article pour faire pointer le lien "Lire la suite" quand on clic dessus
	 * @scope    home,categorie,article,tags,archives
	 * @author    Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function artChapo($format = L_ARTCHAPO, $content = true, $anchor = '')
	{

		# On verifie qu'un chapo existe
		if ($this->plxMotor->plxRecord_arts->f('chapo') != '') {
			# On récupère les infos de l'article
			$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
			$url = $this->plxMotor->plxRecord_arts->f('url');
			# On effectue l'affichage
			echo $this->plxMotor->plxRecord_arts->f('chapo') . PHP_EOL;
			if ($format) {
				$title = str_replace("#art_title", $title, $format);
?>
<p class="more"><a href="<?= $this->plxMotor->urlRewrite('?' . L_ARTICLE_URL . $id . '/' . $url) . ($anchor != '' ? '#' . $anchor : '') ?>" title="<?= $title ?>"><?= $title ?></a></p>
<?php
			}
		} else { # Pas de chapo, affichage du contenu
			if ($content === true) {
				echo $this->plxMotor->plxRecord_arts->f('content') . PHP_EOL;
			}
		}
	}

	/**
	 * Méthode qui affiche le chapô (selon paramètres) suivi du contenu de l'article
	 *
	 * @param chapo    affichage oui/non du chapo
	 * @scope    home,categorie,article,tags,archives
	 * @author    Anthony GUÉRIN, Florent MONTHEL et Stephane F
	 **/
	public function artContent($chapo = true)
	{

		if ($chapo === true) {
			echo $this->plxMotor->plxRecord_arts->f('chapo') . PHP_EOL; # Chapo
		}
		echo $this->plxMotor->plxRecord_arts->f('content') . PHP_EOL;

	}

	/**
	 * Méthode qui affiche la date de creation d'un article selon le format choisi
	 *
	 * @param format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2), #time)
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F.
	 **/
	public function artCreationDate($format = '#num_day/#num_month/#num_year(4) #time')
	{

		echo plxDate::formatDate($this->plxMotor->plxRecord_arts->f('date_creation'), $format);
	}

	/**
	 * Méthode qui affiche la date de mise à jour d'un article selon le format choisi
	 *
	 * @param format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2), #time)
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F.
	 **/
	public function artUpdateDate($format = '#num_day/#num_month/#num_year(4) #time')
	{

		echo plxDate::formatDate($this->plxMotor->plxRecord_arts->f('date_update'), $format);
	}

	/**
	 * Méthode qui affiche un lien vers le fil Rss des articles
	 * d'une catégorie précise (si $categorie renseigné) ou du site tout entier
	 *
	 * @param type   type de flux (obsolete)
	 * @param idstr  identifiant d'une catégorie ou d'un user
	 * @param format format du code HTML pour l'affichage du lien (variable : #feedUrl, #feedTitle, #feedName)
	 * @container    balise HTML externe pour encadrer le lien. Peut contenir une class, ...
	 * @scope        home,categorie,article,tags,archives
	 * @author       Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE, J.P. Pourrez "bazooka07"
	 **/
	public function artFeed($type = false, $idstr = '', $format = self::RSS_FORMAT, $container='')
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowArtFeed')))
			return;

		if ($this->plxMotor->aConf ['enable_rss']) {
			if (trim($idstr) != '') {
				# Fil Rss des articles d'une catégorie
				if(is_numeric($idstr)) {
					$idStr = str_pad($idstr, 3, '0', STR_PAD_LEFT);
				}
				switch($this->plxMotor->mode) {
					case 'categorie':
						if (!empty($idStr) and isset ($this->plxMotor->aCats[$idStr])) {
							$caption = sprintf(L_ARTFEED_RSS_CATEGORY, $this->plxMotor->aCats[$idStr]['name']);
							$replaces = array(
								'#feedUrl'      => $this->plxMotor->urlRewrite('feed.php?rss/' . L_CATEGORY_URL . intval($idstr)),
								'#feedTitle'    => $caption,
								'#feedName'     => $caption,
							);
						} else {
							return;
						}
						break;
					case 'user':
						if (!empty($idStr) and isset ($this->plxMotor->aUsers[$idStr])) {
							$caption = sprintf(L_ARTFEED_RSS_USER, $this->plxMotor->aUsers[$idStr]['name']);
							$replaces = array(
								'#feedUrl'      => $this->plxMotor->urlRewrite('feed.php?rss/' . L_USER_URL . intval($idstr)),
								'#feedTitle'    => $caption,
								'#feedName'     => $caption,
							);
						} else {
							return;
						}
						break;
					case 'tags':
						$caption = sprintf(L_ARTFEED_RSS_TAG, $this->plxMotor->cible);
						$replaces = array(
							'#feedUrl'		=> $this->plxMotor->urlRewrite('feed.php?rss/' . L_TAG_URL . '/' . plxUtils::strCheck($idstr)),
							'#feedTitle'	=> $caption,
							'#feedName'		=> $caption,
						);
						break;
					default:
						return;
				}
			} else {
				# Fil Rss des articles
				$replaces = array(
					'#feedUrl'      => $this->plxMotor->urlRewrite('feed.php?rss'),
					'#feedTitle'    => L_ARTFEED_RSS,
					'#feedName'     => L_ARTFEED_RSS,
				);
			}

			if(!empty($replaces)) {
				if(empty($container)) {
					echo strtr($format, $replaces);
				} else {
					$container = trim($container, '<>');
					$tag = preg_replace('#(\w+).*#', '$1', $container);
?>
<<?= $container ?>>
	<?= strtr($format, $replaces) ?>
</<?= $tag ?>>
<?php
				}
			}
		}
	}

	/**
	 * Méthode qui vérifie que les commentaires sont autorisés pour l'article courant
	 *
	 * @return	bool
	 * @author	Jean-Pierre Pourrez "bazooka07"
	 **/
	public function articleAllowComs()
	{
		return $this->plxMotor->articleAllowComs();
	}

	/**
	 * Méthode qui vérifie si la publication d'un commentaire pour article est réservé aux abonnés
	 *
	 * @return	bool
	 * @author	Jean-Pierre Pourrez "bazooka07"
	 **/
	public function articleComLoginRequired() {
		return $this->plxMotor->articleComLoginRequired();
	}

	/**
	 * Méthode qui affiche le nombre de commentaires (sous forme de lien ou non selon le mode) d'un article
	 *
	 * @param f1        format d'affichage si nombre de commentaire = 0 (#nb pour afficher le nombre de commentaire)
	 * @param f2        format d'affichage si nombre de commentaire = 1 (#nb pour afficher le nombre de commentaire)
	 * @param f3        format d'affichage si nombre de commentaire > 1 (#nb pour afficher le nombre de commentaire)
	 * @scope    home,categorie,article,tags,archives
	 * @author    Stephane F
	 **/
	public function artNbCom($f1 = 'L_NO_COMMENT', $f2 = '#nb L_COMMENT', $f3 = '#nb L_COMMENTS')
	{

		$nb = intval($this->plxMotor->plxRecord_arts->f('nb_com'));
		$num = intval($this->plxMotor->plxRecord_arts->f('numero'));
		$url = $this->plxMotor->plxRecord_arts->f('url');

		if ($nb == 0) {
			$txt = str_replace('L_NO_COMMENT', L_NO_COMMENT, $f1);
			$title = $nb . ' ' . L_NO_COMMENT;
		} elseif ($nb == 1) {
			$txt = str_replace('L_COMMENT', L_COMMENT, $f2);
			$title = $nb . ' ' . L_COMMENT;
		} else {
			$txt = str_replace('L_COMMENTS', L_COMMENTS, $f3);
			$title = $nb . ' ' . L_COMMENTS;
		}
		$txt = str_replace('#nb', $nb, $txt);

		if ($this->plxMotor->mode == 'article') {
			echo $txt;
		} else {
?>
<a href="<?= $this->plxMotor->urlRewrite('?' . L_ARTICLE_URL . $num . '/' . $url) ?>#comments" title="<?= $title ?>"><?= $txt ?></a>
<?php
		}

	}

	/**
	 * Méthode qui affiche le nombre total d'articles publiés sur le site.
	 *
	 * @param f1        format d'affichage si nombre d'article = 0 (#nb pour afficher le nombre de commentaire)
	 * @param f2        format d'affichage si nombre d'article = 1 (#nb pour afficher le nombre de commentaire)
	 * @param f3        format d'affichage si nombre d'article > 1 (#nb pour afficher le nombre de commentaire)
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function nbAllArt($f1 = 'L_NO_ARTICLE', $f2 = '#nb L_ARTICLE', $f3 = '#nb L_ARTICLES')
	{

		$nb = $this->plxMotor->nbArticles('published', '[0-9]{3}', '', 'before');

		if ($nb == 0)
			$txt = str_replace('L_NO_ARTICLE', L_NO_ARTICLE, $f1);
		elseif ($nb == 1)
			$txt = str_replace('L_ARTICLE', L_ARTICLE, $f2);
		else
			$txt = str_replace('L_ARTICLES', L_ARTICLES, $f3);

		$txt = str_replace('#nb', $nb, $txt);

		echo $txt;
	}

	/**
	 * Méthode qui affiche la liste des $max derniers articles.
	 * Si la variable $cat_id est renseignée, seuls les articles de cette catégorie sont retournés.
	 * On tient compte si la catégorie est active
	 *
	 * @param format    format du texte pour chaque article
	 * @param max        nombre d'articles maximum
	 * @param cat_id    ids des catégories cible (numérique ou urls) : séparés par des |
	 * @param ending    texte à ajouter en fin de ligne
	 * @param sort    tri de l'affichage des articles (sort|rsort|alpha|random)
	 * @scope    global
	 * @author    Florent MONTHEL, Stephane F, Cyril MAGUIRE, Thomas Ingles
	 **/
	public function lastArtList($format = '<li><a href="#art_url" title="#art_title">#art_title</a></li>', $max = 5, $cat_id = '', $ending = '', $sort = 'rsort')
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowLastArtList'))) return;

		# Génération de notre motif
		$all = isset($all) ? $all : empty($cat_id); # pour le hook : si $all = TRUE, n'y passe pas
		# Notice : 000 an home are always in activeCats
		$cats = $this->plxMotor->activeCats; # toutes les categories active
		if (!$all) {
			if (is_numeric($cat_id)) # inclusion à partir de l'id de la categorie
				$cats = str_pad($cat_id, 3, '0', STR_PAD_LEFT);
			else { # inclusion à partir de url de la categorie
				$cat_id .= '|';
				foreach ($this->plxMotor->aCats as $key => $value) {
					if (strpos($cat_id, $value['url'] . '|') !== false) {
						$cats = explode('|', $cat_id);
						if (in_array($value['url'], $cats)) {
							$cat_id = str_replace($value['url'] . '|', $key . '|', $cat_id);
						}
					}
				}
				$cat_id = substr($cat_id, 0, -1);
				if (empty($cat_id)) {
					$all = true;
				} else {
					$cats = $cat_id;
				}
			}
		}
		if (empty($motif)) {# pour le hook. motif par defaut s'il n'a point créé cette variable
			if ($all)
				$motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*(?:' . $cats . ')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
			else
				$motif = '#^\d{4}\.((?:pin,|home,|\d{3})*(?:' . $cats . ')(?:,\d{3},)*)\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
		}

		# Nouvel objet plxGlob et récupération des fichiers
		$plxGlob_arts = clone $this->plxMotor->plxGlob_arts;
		$extra = (!empty($this->plxMotor->plxRecord_arts)) ? count($this->plxMotor->plxRecord_arts->result) : 0;
		if ($aFiles = $plxGlob_arts->query($motif, 'art', $sort, 0, $max + $extra, 'before')) {

			$pattern = '~#art_chapo\((\d+)\)~';
			if(preg_match($pattern, $format, $matches)) {
				$lengthChapo = $matches[1];
				$format = preg_replace($pattern, '#art_chapo', $format);
			} else {
				$lengthChapo = '100';
			}

			$pattern = '~#art_content\((\d+)\)~';
			if(preg_match($pattern, $format, $matches)) {
				$lengthContent = $matches[1];
				$format = preg_replace($pattern, '#art_content', $format);
			} else {
				$lengthContent = '100';
			}

			if ($extra > 0) {
				# On affiche des articles
				$excludedArtIds = array_map(
					function($art) {
						return $art['numero'];
					},
					$this->plxMotor->plxRecord_arts->result
				);
				sort($excludedArtIds);
			}
			$cnt = $max;
			$displayCats = preg_match('~\b#cat_list\b~', $format);
			foreach ($aFiles as $v) { # On parcourt tous les fichiers
				if($cnt == 0) {
					break;
				}

				$art = $this->plxMotor->parseArticle(PLX_ROOT . $this->plxMotor->aConf['racine_articles'] . $v);
				if(!is_array($art)) {
					continue;
				}

				$num = intval($art['numero']);
				if($extra > 0 and in_array($num, $excludedArtIds)) {
					# on exclue de la liste les articles affichés sur la page courante
					continue;
				}
				$date = $art['date'];
				$status = ($this->plxMotor->mode == 'article' and $art['numero'] == $this->plxMotor->cible) ? 'active' : 'noactive';

				# Mise en forme de la liste des catégories
				$catList = array();
				if($displayCats) {
					# on affiche les catégories
					$catIds = array_unique(explode(',', $art['categorie']));
					foreach ($catIds as $catId) {
						if (isset($this->plxMotor->aCats[$catId])) { # La catégorie existe
							$catName = plxUtils::strCheck($this->plxMotor->aCats[$catId]['name']);
							$catUrl = $this->plxMotor->aCats[$catId]['url'];
							$catList[] = '<a title="' . $catName . '" href="' . $this->plxMotor->urlRewrite('?' . L_CATEGORY_URL . intval($catId) . '/' . $catUrl) . '">' . $catName . '</a>';
						} else {
							$catList[] = L_UNCLASSIFIED;
						}
					}
				}

				# On modifie nos motifs
				$author = plxUtils::getValue($this->plxMotor->aUsers[$art['author']]['name']);

				$row = strtr($format, [
					'#art_id'			=> $num,
					'#cat_list'			=> implode(', ', $catList),
					'#art_url'			=> $this->plxMotor->urlRewrite('?' . L_ARTICLE_URL . $num . '/' . $art['url']),
					'#art_status'		=> $status,
					'#art_author'		=> plxUtils::strCheck($author),
					'#art_title'		=> plxUtils::strCheck($art['title']),
					'#art_chapo'		=> plxUtils::truncate($art['chapo'], $lengthChapo, $ending, true, true),
					'#art_content'		=> plxUtils::truncate($art['content'], $lengthContent, $ending, true, true),
					'#art_date'			=> plxDate::formatDate($date, '#num_day/#num_month/#num_year(4)'),
					'#art_hour'			=> plxDate::formatDate($date, '#hour:#minute'),
					'#art_time'			=> plxDate::formatDate($date, '#time'),
					'#art_nbcoms'		=> $art['nb_com'],
					'#art_thumbnail'	=> '<img class="art_thumbnail" src="#img_url" alt="#img_alt" title="#img_title" />',
					'#img_url'			=> $this->plxMotor->urlRewrite($art['thumbnail']),
					'#img_title'		=> $art['thumbnail_title'],
					'#img_alt'			=> $art['thumbnail_alt'],
				]);

				# Hook plugin
				eval($this->plxMotor->plxPlugins->callHook('plxShowLastArtListContent'));

				# On genère notre ligne
				echo $row;
				$cnt--;
			}
		}
	}

	/**
	 * Méthode qui affiche l'id du commentaire précédé de la lettre 'c'
	 *
	 * @param echo        (boolean) fait un affichage si valeur à TRUE
	 * @return    stdout/id    sortie stdout ou retourne l'id du commentaire
	 * @scope    article
	 * @author    Florent MONTHEL
	 **/
	public function comId($echo = true)
	{
		$id = 'c' . $this->plxMotor->plxRecord_coms->f('index');
		if ($echo)
			echo $id;
		else
			return $id;
	}

	/**
	 * Méthode qui affiche l'url du commentaire de type relatif ou absolu
	 *
	 * @param type    type de lien : relatif ou absolu (URL complète) DEPRECATED
	 * @scope    article
	 * @author    Florent MONTHEL, Stephane F
	 **/
	public function comUrl($type = 'relatif')
	{

		# On affiche l'URL
		$artId = $this->plxMotor->plxRecord_coms->f('article');
		$artInfo = $this->plxMotor->artInfoFromFilename($this->plxMotor->plxGlob_arts->aFiles[$artId]);
		echo $this->urlRewrite('?article' . intval($artId) . '/' . $artInfo['artUrl'] . '#' . $this->ComId(false));
	}

	/**
	 * Méthode qui affiche l'index d'un commentaire
	 *
	 * @scope    article
	 * @author    Stephane F.
	 **/
	public function comIndex()
	{

		echo $this->plxMotor->plxRecord_coms->f('index');
	}

	/**
	 * Get comment indent number
	 * @return    integer        level number for comment indentation
	 * @author    Stephane F.
	 **/
	public function comNumLevel()
	{
		return $this->plxMotor->plxRecord_coms->f('level');
	}

	/**
	 * Add CSS class fort comments indentation
	 * @param class    css class used to indent comments
	 * @author    Stephane F., Jerry Wham, Pedro "P3ter" CADETE
	 **/
	public function comLevel($class = 'level')
	{
		$numLevel = $this->comNumLevel();
		if ($numLevel > 5)
			echo $class . '-' . $numLevel . ' ' . $class . '-max';
		else
			echo $class . '-' . $numLevel;
	}

	/**
	 * Méthode qui affiche le nombre total de commentaires publiés sur le site.
	 *
	 * @param f1        format d'affichage si nombre de commentaire = 0 (#nb pour afficher le nombre de commentaire)
	 * @param f2        format d'affichage si nombre de commentaire = 1 (#nb pour afficher le nombre de commentaire)
	 * @param f3        format d'affichage si nombre de commentaire > 1 (#nb pour afficher le nombre de commentaire)
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function nbAllCom($f1 = 'L_NO_COMMENT', $f2 = '#nb L_COMMENT', $f3 = '#nb L_COMMENTS')
	{

		$nb = $this->plxMotor->nbComments('online', 'before');

		if ($nb == 0)
			$txt = str_replace('L_NO_COMMENT', L_NO_COMMENT, $f1);
		elseif ($nb == 1)
			$txt = str_replace('L_COMMENT', L_COMMENT, $f2);
		else
			$txt = str_replace('L_COMMENTS', L_COMMENTS, $f3);

		$txt = str_replace('#nb', $nb, $txt);

		echo $txt;
	}

	/**
	 * Méthode qui affiche l'auteur du commentaires linké ou non
	 *
	 * @param type    type d'affichage : link => sous forme de lien
	 * @scope    article
	 * @author    Anthony GUÉRIN, Florent MONTHEL et Stephane F.
	 **/
	public function comAuthor($type = '')
	{

		# Initialisation de nos variables interne
		$author = $this->plxMotor->plxRecord_coms->f('author');
		$site = $this->plxMotor->plxRecord_coms->f('site');
		if ($type == 'link' and $site != '') {
			# Type lien
?>
<a rel="nofollow" href="<?= $site ?>" title="<?= $author ?>"><?= $author ?></a>
<?php
		} else {
			# Type normal
			echo $author;
		}
	}

	/**
	 * Méthode qui affiche le type du commentaire (admin ou normal)
	 *
	 * @scope    article
	 * @author    Florent MONTHEL
	 **/
	public function comType()
	{

		echo $this->plxMotor->plxRecord_coms->f('type');
	}

	/**
	 * Méthode qui affiche la date de publication d'un commentaire selon le format choisi
	 *
	 * @param format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(2), #num_year(4), #time)
	 * @scope    article
	 * @author    Florent MONTHEL et Stephane F
	 **/
	public function comDate($format = '#day #num_day #month #num_year(4) @ #time')
	{

		echo plxDate::formatDate($this->plxMotor->plxRecord_coms->f('date'), $format);
	}

	/**
	 * Méthode qui affiche le contenu d'un commentaire
	 *
	 * @scope    article
	 * @author    Florent MONTHEL
	 **/
	public function comContent()
	{

		echo nl2br($this->plxMotor->plxRecord_coms->f('content'));
	}

	/**
	 * Méthode qui affiche si besoin le message généré par le système
	 * suite à la création d'un commentaire
	 * @param format  format du texte à afficher (variable: #com_message, #com_class)
	 * @return        true si un commentaire est validé ou en attente de modération (Pas nécessaire d'afficher le formulaire)
	 * @scope        article
	 * @author        Stephane F, J.P Pourrez.
	 * @version        2017-12-28
	 **/
	public function comMessage($format = '<p id="com_message" class="#com_class"><strong>#com_message</strong></p>')
	{

		if (!empty($_SESSION['msgcom'])) {
			switch ($_SESSION['msgcom']) {
				case L_COM_IN_MODERATION:
					$color = 'orange';
					break;
				case L_COM_PUBLISHED:
					$color = 'green';
					break;
				default:
					$color = 'red';
			}
			echo strtr($format, [
				'#com_message'	=> $_SESSION['msgcom'],
				'#com_class'	=> 'alert ' . $color,
			]);
			unset($_SESSION['msgcom']);

			return empty($_SESSION['msg']);
		}
		return false;
	}

	/**
	 * Méthode qui affiche si besoin la variable $_GET[$key] suite au dépôt d'un commentaire
	 *
	 * @param key        clé du tableau GET
	 * @param defaut    valeur par défaut si variable vide
	 * @scope    article
	 * @author    Florent MONTHEL
	 **/
	public function comGet($key, $defaut = '')
	{

		if (isset($_SESSION['msg'][$key]) and !empty($_SESSION['msg'][$key])) {
			echo plxUtils::strCheck($_SESSION['msg'][$key]);
			$_SESSION['msg'][$key] = '';
		} else {
			echo $defaut;
		}

	}

	/**
	 * Méthode qui affiche un lien vers le fil Rss des commentaires
	 * d'un article précis (si $article renseigné) ou du site tout entier
	 *
	 * @param type        type de flux (obsolete)
	 * @param article        identifiant (sans les 0) d'un article
	 * @param format        format du code HTML pour l'affichage du lien (variable : #feedUrl, #feedTitle, #feedName)
	 * @scope    global
	 * @author    Anthony GUÉRIN, Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
	 **/
	public function comFeed($type = 'rss', $article = '', $format = self::RSS_FORMAT)
	{
		# Hook Plugins
		if (eval ($this->plxMotor->plxPlugins->callHook('plxShowComFeed')))
			return;

		if ($this->plxMotor->aConf ['enable_rss_comment']) {
			if ($article != '' and is_numeric($article)) { # Fil Rss des commentaires d'un article
				$replaces =array(
					'#feedUrl'		=> $this->plxMotor->urlRewrite('feed.php?rss/' . L_COMMENTS_URL . '/' . L_ARTICLE_URL . $article),
					'#feedTitle'	=> L_COMFEED_RSS_ARTICLE,
					'#feedName'		=> L_COMFEED_RSS_ARTICLE,
				);
			} else { # Fil Rss des commentaires global
				$replaces = array(
					'#feedUrl'		=> $this->plxMotor->urlRewrite('feed.php?rss/' . L_COMMENTS_URL),
					'#feedTitle'	=> L_COMFEED_RSS,
					'#feedName'		=> L_COMFEED_RSS,
				);
			}
			echo strtr($format, $replaces);
		}
	}

	/**
	 * Méthode qui affiche la liste des $max derniers commentaires.
	 * Si la variable $art_id est renseignée, seuls les commentaires de cet article sont retournés.
	 *
	 * @param format    format du texte pour chaque commentaire
	 * @param max        nombre de commentaires maximum
	 * @param art_id    id de l'article cible (24,3)
	 * @param cat_ids    liste des categories pour filtrer les derniers commentaires (sous la forme 001|002)
	 * @scope    global
	 * @author    Florent MONTHEL, Stephane F
	 **/
	public function lastComList($format = '<li><a href="#com_url">#com_author L_SAID :</a><br/>#com_content(50)</li>', $max = 5, $art_id = '', $cat_ids = '')
	{

		$capture = '';

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowLastComList'))) return;

		# Génération de notre motif
		$id = empty($art_id) ? '\d{4}' : str_pad($art_id, 4, '0', STR_PAD_LEFT);
		$motif = '~^' . $id . '\.\d{10}-\d+\.xml$~';

		$excludeArtId = (empty($art_id) and $this->plxMotor->mode == 'article') ? $this->plxMotor->cible : '';

		$count = 1;
		$datetime = date('YmdHi');
		# Nouvel objet plxGlob et récupération des fichiers
		$plxGlob_coms = clone $this->plxMotor->plxGlob_coms;
		if ($aFiles = $plxGlob_coms->query($motif, 'com', 'rsort', 0, false, 'before')) {
			$aComArtTitles = array(); # tableau contenant les titres des articles
			$isComArtTitle = (strpos($format, '#com_art_title') != FALSE) ? true : false;
			$pattern = '~#com_content\((\d+)\)~';
			if(preg_match($pattern, $format, $matches)) {
				$lengthContent = $matches[1];
				$format = preg_replace($pattern, '#com_content()', $format);
			}
			# On parcourt les fichiers des commentaires
			foreach ($aFiles as $v) {
				$artId = substr($v, 0, 4);
				if($artId == $excludeArtId) {
					continue;
				}

				# On filtre si le commentaire appartient à un article d'une catégorie inactive
				if (isset($this->plxMotor->activeArts[$artId])) {
					$com = $this->plxMotor->parseCommentaire(PLX_ROOT . $this->plxMotor->aConf['racine_commentaires'] . $v);
					$artInfo = $this->plxMotor->artInfoFromFilename($this->plxMotor->plxGlob_arts->aFiles[$com['article']]);
					if ($artInfo['artDate'] <= $datetime) { # on ne prends que les commentaires pour les articles publiés
						if (empty($cat_ids) or preg_match('/(' . $cat_ids . ')/', $artInfo['catId'])) {
							$url = '?' . L_ARTICLE_URL . intval($com['article']) . '/' . $artInfo['artUrl'] . '#c' . $com['index'];
							$date = $com['date'];
							$content = strip_tags($com['content']);
							# On modifie nos motifs
							$replaces = [
								'L_SAID'		=> L_SAID,
								'#com_id'		=> $com['index'],
								'#com_url'		=> $this->plxMotor->urlRewrite($url),
								'#com_author'	=> $com['author'],
								'#com_content'	=> $content,
								'#com_date'		=> plxDate::formatDate($date, '#num_day/#num_month/#num_year(4)'),
								'#com_hour'		=> plxDate::formatDate($date, '#time'),
							];
							if(!empty($lengthContent)) {
								if($com['author'] == 'admin') {
									$content = plxUtils::strRevCheck($content);
								}
								$replaces['#com_content()'] = plxUtils::strCut($content, $lengthContent);
							}
							# récupération du titre de l'article
							if ($isComArtTitle) {
								$artId = $com['article'];
								if (!isset($aComArtTitles[$artId])) {
									if ($file = $this->plxMotor->plxGlob_arts->query('/^' . $artId . '\.(.*)\.xml$/')) {
										$art = $this->plxMotor->parseArticle(PLX_ROOT . $this->plxMotor->aConf['racine_articles'] . $file[0]);
										$aComArtTitles[$artId] = $art['title'];
									} else {
										$aComArtTitles[$artId] = '';
									}
								}
								$replaces['#com_art_title'] = $aComArtTitles[$com['article']];
							}
							# On genère notre ligne
							echo strtr($format, $replaces);
							$count++;
						}
					}
				}
				if ($count > $max) break;
			}
		}
	}

	/**
	 * Méthode qui affiche la liste des pages statiques.
	 *
	 * @param extra           si renseigné: nom du lien vers la page d'accueil affiché en première position
	 * @param format          format du texte pour chaque page (variable : #static_id, #static_status, #static_url, #static_name, #group_id, #group_class, #group_name)
	 * @param format_group    format du texte pour chaque groupe (variable : #group_class, #group_name, #group_status)
	 * @param menublog        position du menu Blog (si non renseigné le menu n'est pas affiché). Si égal à 0 affiché en fin de menu.
	 * @param link_homepage   affiche toujours un lien vers la page d'accueil dans le menu avec la valeur true. False par défaut
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function staticList($extra = '', $format = NULL, $format_group = NULL, $menublog = NULL, $link_homepage = false)
	{

		$menus = array();
		if (is_null($format)) {
			$format = SELF::STATIC_LIST_FORMAT;
		}
		if (is_null($format_group)) {
			$format_group = SELF::STATIC_LIST_FORMAT_GROUP;
		}

		$homestaticId = $this->plxMotor->aConf['homestatic'];
		if(array_key_exists($homestaticId, $this->plxMotor->aStats) and !$this->plxMotor->aStats[$homestaticId]['active']) {
			# la page static pour la page d'accueil est inactive
			$homestaticId = NULL;
		}

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowStaticListBegin'))) return;

		$home = ((empty($this->plxMotor->get) or preg_match('/^page\d*/', $this->plxMotor->get)) and basename($_SERVER['SCRIPT_NAME']) == 'index.php');

		# Si on a la variable extra, on affiche un lien vers la page d'accueil (avec $extra comme nom)
		if (is_string($extra) and trim($extra) != '') {
			# tester si $home === true et si $link_homepage === true
			if(
				$link_homepage or
				($this->plxMotor->mode == 'home' and (intval($this->plxMotor->page) > 1 or array_key_exists($homestaticId, $this->plxMotor->aStats))) or
				(in_array($this->plxMotor->mode, array('article', 'categorie', 'tags', 'user', 'archives'))) or
				($this->plxMotor->mode == 'static' and $this->plxMotor->cible != $homestaticId)
			) {
				$menus[][] = strtr($format, [
					'#static_id'	=> 'static-home',
					'#static_class'	=> 'static menu',
					'#static_url'	=> $this->plxMotor->urlRewrite(),
					'#static_name'	=> plxUtils::strCheck($extra),
					'#static_status'	=> $home ? 'active' : 'noactive',
				]);
			}
		}

		$group_active = '';
		if ($this->plxMotor->aStats) {
			foreach ($this->plxMotor->aStats as $k => $v) {
				if ($v['active'] == 1 and $v['menu'] == 'oui') { # La page  est bien active et dispo ds le menu
					if ($v['url'][0] == '?') # url interne commençant par ?
						$url = $this->plxMotor->urlRewrite($v['url']);
					elseif (plxUtils::checkSite($v['url'], false)) # url externe en http ou autre
						$url = $v['url'];
					else # url page statique
						$url = $this->plxMotor->urlRewrite('?' . L_STATIC_URL . intval($k) . '/' . $v['url']);

					$stat = strtr($format, [
						'#static_id'		=> 'static-' . intval($k),
						'#static_class'		=> 'static menu',
						'#static_name'		=> plxUtils::strCheck($v['name']),
						'#static_status'	=> ($this->staticId() == intval($k)) ? 'active' : 'noactive',
						'#static_url'		=> $url,
					]);

					if (empty($v['group']))
						$menus[][] = $stat;
					else
						$menus[$v['group']][] = $stat;
					if ($group_active == '' and $home === false and $this->staticId() == intval($k) and $v['group'] != '')
						$group_active = $v['group'];
				}
			}
		}

		if (is_integer($menublog)) {
			if (array_key_exists($homestaticId, $this->plxMotor->aStats)) {
				if ($this->plxMotor->aStats[$homestaticId]['active']) {
					$menu = strtr($format, [
					'#static_id'	=> 'static-blog',
					'#static_status'=> (
						$this->plxMotor->get and
						preg_match('#^(?:blog|categorie|archives|tag|article)#', $_SERVER['QUERY_STRING'] . $this->plxMotor->mode)
					) ? 'active' : 'noactive',
					'#static_url'	=> $this->plxMotor->urlRewrite('?' . L_BLOG_URL),
					'#static_name'	=> L_PAGEBLOG_TITLE,
					'#static_class'	=> 'static menu',
					]);
					array_splice($menus, (intval($menublog) - 1), 0, array($menu));
				}
			}
		}

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowStaticListEnd'))) return;

		# Affichage des pages statiques + menu Accueil et Blog
		if ($menus) {
			foreach ($menus as $k => $v) {
				if (is_numeric($k)) {
					echo PHP_EOL . (is_array($v) ? $v[0] : $v);
				} else {
					$group = strtr($format_group, [
						'#group_id'		=> 'static-group-' . plxUtils::urlify($k),
						'#group_class'	=> 'static group',
						'#group_status'	=> ($group_active == $k) ? 'active' : 'noactive',
						'#group_name'	=> plxUtils::strCheck($k),
					]);
?>
	<li class="menu">
		<?= $group ?>
		<ul id="static-<?= plxUtils::urlify($k) ?>" class="sub-menu">
		<?= implode("\t\t\n", $v) ?>
		</ul>
	</li>
<?php
				}
			}
		}

	}

	/**
	 * Méthode qui retourne l'id de la page statique active
	 *
	 * @return    int
	 * @scope    static
	 * @author    Florent MONTHEL, Stéphane F.
	 **/
	public function staticId()
	{

		# On va vérifier que la catégorie existe en mode catégorie
		if ($this->plxMotor->mode == 'static' and isset($this->plxMotor->aStats[$this->plxMotor->cible]))
			return intval($this->plxMotor->cible);
		else
			return plxUtils::strCheck($this->plxMotor->mode);
	}

	/**
	 * Méthode qui affiche ou retourne l'url de la page statique
	 *
	 * @param echo    si à VRAI affichage à l'écran
	 * @param extra    paramètres supplémentaires pouvant être rajoutés à la fin de l'url de l'article
	 * @return    string
	 * @scope    static
	 * @author    Florent MONTHEL, Stéphane F
	 **/
	public function staticUrl($echo = true, $extra = '')
	{

		# Recupération ID URL
		$staticId = $this->staticId();
		$staticIdFill = str_pad($staticId, 3, '0', STR_PAD_LEFT);
		if (!empty($staticId) and isset($this->plxMotor->aStats[$staticIdFill])) {
			$url = $this->plxMotor->urlRewrite('?' . L_STATIC_URL . $staticId . '/' . $this->plxMotor->aStats[$staticIdFill]['url'] . $extra);
			if ($echo)
				echo $url;
			else
				return $url;
		}
	}

	/**
	 * Méthode qui affiche le titre de la page statique
	 *
	 * @scope    static
	 * @author    Florent MONTHEL
	 **/
	public function staticTitle()
	{

		echo plxUtils::strCheck($this->plxMotor->aStats[$this->plxMotor->cible]['name']);
	}

	/**
	 * Méthode qui affiche le groupe de la page statique
	 *
	 * @scope    static
	 * @author    Stéphane F.
	 **/
	public function staticGroup()
	{

		echo plxUtils::strCheck($this->plxMotor->aStats[$this->plxMotor->cible]['group']);
	}

	/**
	 * Méthode qui affiche la date de la dernière modification de la page statique selon le format choisi
	 *
	 * @param format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2), #time)
	 * @scope    static
	 * @author    Anthony T.
	 **/
	public function staticDate($format = '#day #num_day #month #num_year(4)')
	{

		# On genere le nom du fichier dont on veux récupérer la date
		$file = PLX_ROOT . $this->plxMotor->aConf['racine_statiques'] . $this->plxMotor->cible;
		$file .= '.' . $this->plxMotor->aStats[$this->plxMotor->cible]['url'] . '.php';
		# Test de l'existence du fichier
		if (!file_exists($file)) return;
		# On récupère la date de la dernière modification du fichier qu'on formate
		echo plxDate::formatDate(date('YmdHi', filemtime($file)), $format);
	}

	/**
	 * Méthode qui affiche la date de creation de la page statique selon le format choisi
	 *
	 * @param format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2), #time)
	 * @scope    static
	 * @author    Stephane F.
	 **/
	public function staticCreationDate($format = '#num_day/#num_month/#num_year(4) #time')
	{

		echo plxDate::formatDate($this->plxMotor->aStats[$this->plxMotor->cible]['date_creation'], $format);
	}

	/**
	 * Méthode qui affiche la date de modification de la page statique selon le format choisi
	 *
	 * @param format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_day(1), #num_day(2), #num_month, #num_year(4), #num_year(2))
	 * @scope    static
	 * @author    Stephane F.
	 **/
	public function staticUpdateDate($format = '#num_day/#num_month/#num_year(4) #time')
	{

		echo plxDate::formatDate($this->plxMotor->aStats[$this->plxMotor->cible]['date_update'], $format);
	}

	/**
	 * Méthode qui inclut le code source de la page statique
	 *
	 * @scope    static
	 * @author    Florent MONTHEL, Stephane F
	 **/
	public function staticContent()
	{

		if (eval($this->plxMotor->plxPlugins->callHook("plxShowStaticContentBegin"))) return;

		# On va verifier que la page a inclure est lisible
		if ($this->plxMotor->aStats[$this->plxMotor->cible]['readable'] == 1) {
			# On genere le nom du fichier a inclure
			$file = PLX_ROOT . $this->plxMotor->aConf['racine_statiques'] . $this->plxMotor->cible;
			$file .= '.' . $this->plxMotor->aStats[$this->plxMotor->cible]['url'] . '.php';
			# Inclusion du fichier
			ob_start();
			require $file;
			$output = ob_get_clean();
			eval($this->plxMotor->plxPlugins->callHook('plxShowStaticContent'));
			echo $output;
		} else {
?>
<p><?= L_STATICCONTENT_INPROCESS ?></p>
<?php
		}

	}

	/**
	 * Méthode qui affiche une page statique en lui passant son id (si cette page est active ou non)
	 *
	 * @param id        id numérique ou url/titre de la page statique
	 * @scope    global
	 * @author    Stéphane F, Jean-Pierre Pourrez "bazooka07"
	 **/
	public function staticInclude($id)
	{

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowStaticInclude'))) {
		   return;
		}
		# On génère un nouvel objet plxGlob
		$plxGlob_stats = plxGlob::getInstance(PLX_ROOT . $this->plxMotor->aConf['racine_statiques'], false, true, 'statiques');
		if (is_numeric($id)) {
			# inclusion à partir de l'id de la page
			$regx = '#^' . str_pad($id, 3, '0', STR_PAD_LEFT) . '\.[\w-]+\.php$#';
		} else {
			# inclusion à partir du titre de la page
			$url = plxUtils::urlify($id);
			$regx = '#^\d{3}\.' . $url . '\.php$#';
		}
		if ($files = $plxGlob_stats->query($regx)) {
			# on récupère l'id de la page pour tester si elle est active
			if (
				preg_match('#^(\d{3})\.[\w-]+\.php$#', $files[0], $matches) and
				$this->plxMotor->aStats[$matches[1]]['active']
			) {
				include PLX_ROOT . $this->plxMotor->aConf['racine_statiques'] . $files[0];
			}
		}
	}

	/**
	 * Méthode qui affiche la pagination
	 *
	 * @scope    global
	 * @author    Florent MONTHEL, Stephane F
	 **/
	public function pagination()
	{

		$plxGlob_arts = clone $this->plxMotor->plxGlob_arts;
		$aFiles = $plxGlob_arts->query($this->plxMotor->motif, 'art', '', 0, false, 'before');
		$byhomepage = ($this->plxMotor->mode == 'home' and !empty($this->plxMotor->aConf['byhomepage']) and $this->plxMotor->aConf['byhomepage'] != $this->plxMotor->page) ? $this->plxMotor->aConf['byhomepage'] : $this->plxMotor->bypage;
		if(
			empty($aFiles) or
			empty($this->plxMotor->bypage) or
			sizeof($aFiles) <= $byhomepage
		) {
			return;
		}

		# on supprime le n° de page courante dans l'url
        $arg_url = preg_replace('~(/?\b' . L_PAGE_URL . '\d+)$~', '', $this->plxMotor->get);

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowPagination'))) {
			return;
		}

		# On effectue l'affichage
		if ($this->plxMotor->page > 1) {
			# Si la page active > 1 on affiche un lien 1ère page
			$url = $this->plxMotor->urlRewrite('?' . $arg_url); # Premiere page
?>
	<a class="p_first" href="<?= $url ?>" title="<?= L_PAGINATION_FIRST_TITLE ?>"><?= L_PAGINATION_FIRST ?></a>
<?php
		}

		$arg_url .= !empty($arg_url) ? '/' . L_PAGE_URL : L_PAGE_URL;

		if ($this->plxMotor->page > 2) {
			# Si la page active > 2 on affiche un lien page precedente
			$url = $this->plxMotor->urlRewrite('?' . $arg_url . ($this->plxMotor->page - 1));
?>
	<a class="p_prev" href="<?= $url ?>" title="<?= L_PAGINATION_PREVIOUS_TITLE ?>"><?= L_PAGINATION_PREVIOUS ?></a>
<?php
		}

		# Affichage de la page courante
		$last_page = ceil((sizeof($aFiles) -  $byhomepage) / $this->plxMotor->bypage) + 1;
		printf('<span class="p_page p_current">' . L_PAGINATION . '</span>', $this->plxMotor->page, $last_page);

		if ($this->plxMotor->page < $last_page - 1) {
			# Si la page active < derniere page on affiche un lien page suivante
			$url = $this->plxMotor->urlRewrite('?' . $arg_url . ($this->plxMotor->page + 1));
?>
	<a class="p_next" href="<?= $url ?>" title="<?= L_PAGINATION_NEXT_TITLE ?>"><?= L_PAGINATION_NEXT ?></a>
<?php
		}

		if ($this->plxMotor->page < $last_page) {
			# Si la page active < derniere page, alors on affiche un lien derniere page
			$url = $this->plxMotor->urlRewrite('?' . $arg_url . $last_page);
?>
	<a class="p_last" href="<?= $url ?>" title="<?= L_PAGINATION_LAST_TITLE ?>"><?= L_PAGINATION_LAST ?></a>
<?php
		}
	}

	/**
	 * Méthode qui affiche la question du capcha
	 *
	 * @scope    global
	 * @author    Florent MONTHEL, Stephane F.
	 **/
	public function capchaQ()
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowCapchaQ'))) return;
		echo $this->plxMotor->plxCapcha->q();
?>
		<input type="hidden" name="capcha_token" value="<?= $_SESSION['capcha_token'] ?>"/>
<?php
	}

	/**
	 * DEPRECATED
	 *
	 * Méthode qui affiche la réponse du capcha cryptée en sha1
	 *
	 * @scope    global
	 * @author    Florent MONTHEL, Stephane F.
	 **/
	public function capchaR()
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowCapchaR'))) return;
		echo $this->plxMotor->plxCapcha->r();

	}

	/**
	 * Méthode qui affiche le message d'erreur de l'objet plxErreur
	 *
	 * @scope    erreur
	 * @author    Florent MONTHEL
	 **/
	public function erreurMessage()
	{

		echo $this->plxMotor->plxErreur->getMessage();
	}

	/**
	 * Méthode qui affiche le nom du tag (linké ou non)
	 *
	 * @param type    type d'affichage : link => sous forme de lien
	 * @scope    tags
	 * @author    Stephane F
	 **/
	public function tagName($type = '')
	{

		if ($this->plxMotor->mode == 'tags') {
			$tag = plxUtils::strCheck($this->plxMotor->cible);
			$tagName = plxUtils::strCheck($this->plxMotor->cibleName);
			# On effectue l'affichage
			if ($type == 'link') {
?>
<a href="<?= $this->plxMotor->urlRewrite('?' . L_TAG_URL .'/' . $tag) ?>" title="<?= $tagName ?>"><?= $tagName ?></a>
<?php
			} else {
				echo $tagName;
			}
		}
	}

	/**
	 * Méthode qui affiche un lien vers le fil Rss des articles d'un tag
	 *
	 * @param type        type de flux (obsolete)
	 * @param tag            nom du tag
	 * @param format        format du code HTML pour l'affichage du lien (variable : #feedUrl, #feedTitle, #feedName)
	 * @scope                home,categorie,article,tags,archives
	 * @author                Stephane F, Pedro "P3ter" CADETE
	 **/

	public function tagFeed($type = 'rss', $tag = '', $format = self::RSS_FORMAT)
	{

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowTagFeed')))
			return;

		if ($this->plxMotor->aConf ['enable_rss']) {
			if ($tag == '' and $this->plxMotor->mode == 'tags') {
				$tag = $this->plxMotor->cible;
			}
			echo strtr($format, array(
				'#feedUrl'		=> $this->plxMotor->urlRewrite('feed.php?rss/' . L_TAG_URL . '/' . plxUtils::strCheck($tag)),
				'#feedTitle'	=> L_ARTFEED_RSS_TAG,
				'#feedName'		=> L_ARTFEED_RSS_TAG,
			));
		}
	}

	/**
	 * Méthode qui affiche la liste de tous les tags.
	 *
	 * @param format    format du texte pour chaque tag (variable : #tag_size, #tag_id, #tag_status, #tag_count, #tag_item, #tag_url, #tag_name, #nb_art)
	 * @param max        nombre maxi de tags à afficher
	 * @param order    tri des tags (random, alpha, '' = tri par popularité)
	 * @scope    global
	 * @author    Stephane F, J.P. Pourrez
	 **/
	public function tagList($format = '<li class="tag #tag_size"><a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name (#tag_count)</a></li>', $max = '', $order = 'random')
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowTagList'))) return;

		# On verifie qu'il y a des tags
		if ($this->plxMotor->aTags) {
			$now = date('YmdHi');
			# On liste les tags sans créer de doublon
			$counters = array();
			foreach ($this->plxMotor->aTags as $idart => $tag) {
				if (isset($this->plxMotor->activeArts[$idart]) and $tag['date'] <= $now and $tag['active']) {
					if ($tags = array_map('trim', explode(',', $tag['tags']))) {
						foreach ($tags as $tag) {
							if (!empty($tag)) {
								if (!array_key_exists($tag, $counters)) {
									$counters[$tag] = 1;
								} else {
									$counters[$tag]++;
								}
							}
						}
					}
				}
			}

			# tri des tags
			switch ($order) {
				case 'alpha':
					# Le tri alpha se fait sur la clé
					ksort($counters); # éventuellement uksort pour tri spécifique sur $tag
					break;
				case 'random':
					$keys = array_keys($counters);
					shuffle($keys);
					$arr_elem = array();
					foreach ($keys as $key) {
						$arr_elem[$key] = $counters[$key];
					}
					$counters = $arr_elem;
					break;
				default:
					arsort($counters);
			}

			# limite sur le nombre de tags à afficher
			if ($max != '') $counters = array_slice($counters, 0, intval($max), true);

			# Recherche de la valeur maxi pour $counters. A multiplier par 10.
			$max_value = array_reduce(
				array_values($counters),
				function ($lastValue, $value) {
					return ($lastValue > $value) ? $lastValue : $value;
				},
				0
			);
			$max_value *= 0.1; # Pour faire varier la taille des caractères de 1 à 11;

			$mode = $this->plxMotor->mode;

			# Récupération de la liste des tags de l'article si on est en mode 'article'
			# pour mettre en évidence dans la sidebar les tags attachés à l'article
			$artTags = array();
			switch ($mode) {
				case 'article':
					$artTagList = $this->plxMotor->plxRecord_arts->f('tags');
					if (!empty($artTagList)) {
						$artTags = array_map('trim', explode(',', $artTagList));
					}
					break;
				case 'home':
					foreach ($this->plxMotor->plxRecord_arts->result as $record) {
						foreach (array_map('trim', explode(',', $record['tags'])) as $tag) {
							if (!in_array($tag, $artTags)) {
								$artTags[] = $tag;
							}
						}
					}
			}

			# On affiche la liste
			$id = 0;
			foreach ($counters as $tag => $counter) {
				$url = plxUtils::urlify($tag);
				$status = 'noactive';
				switch ($mode) {
					case 'article':
						if (in_array($tag, $artTags)) {
							$status = 'active';
						}
						break;
					case 'tags':
						$status = ($this->plxMotor->cible == $url) ? 'active' : 'noactive';
				}
				echo strtr($format, [
					'#tag_id' => 'tag-' . $id++,
					'#tag_size' => 'tag-size-' . (1 + intval($counter / $max_value)), # taille des caractères
					'#tag_count' => $counter,
					'#nb_art' => $counter,
					'#tag_item' => $url,
					'#tag_url' => $this->plxMotor->urlRewrite('?' . L_TAG_URL . '/' . $url),
					'#tag_name' => plxUtils::strCheck($tag),
					'#tag_status' => $status
				]);
			}
		}
	}

	/**
	 * Méthode qui affiche le nom de l'auteur (linké ou non)
	 *
	 * @param type    type d'affichage : link => sous forme de lien
	 * @scope        users
	 * @author        Jean-Pierre Pourrez "bazooka07"
	 **/
	public function authorName($type = '')
	{

		if ($this->plxMotor->mode == 'user' and isset($this->plxMotor->aUsers[$this->plxMotor->cible])) {
			$id = plxUtils::strCheck($this->plxMotor->cible);
			$userName = $this->plxMotor->aUsers[$id]['name'];
			# On effectue l'affichage
			if ($type == 'link') {
				$href = 'index.php?user' . $id . '/' . md5($userName);
?>
<a href="<?= $this->plxMotor->urlRewrite($href) ?>" title="<?= $userName ?>"><?= $userName ?></a>
<?php
			} else {
				echo $userName;
			}
		}
	}

	public function authorId() {
		switch ($this->plxMotor->mode) {
			case 'user' :
				if (isset($this->plxMotor->aCats[$this->plxMotor->cible])) {
					return $this->plxMotor->cible;
				}
				break;
			case 'tags':
			case 'home':
			case 'article':
				$authorId = $this->plxMotor->plxRecord_arts->f('author');
				if(isset($this->plxMotor->aUsers[$authorId])) {
					return $authorId;
				}
				break;
			default:
		}

		return '';
	}

	/**
	 * Méthode qui affiche les informations d'un utilisateur
	 *
	 * @param format    format du texte à afficher (variable: #user_infos)
	 * @scope    user
	 * @author   Jean-Pierre Pourrez "bazooka07"
	 **/
	public function authorInfos($format = '<div class="infos">#user_infos</div>') {
		if($this->plxMotor->mode == 'user') {
			$desc = plxUtils::getValue($this->plxMotor->aUsers[$this->plxMotor->cible]['infos']);
			if ($this->plxMotor->mode and $desc) {
				echo str_replace('#user_infos', $desc, $format);
			}
		}
	}

	/**
	 * @param string $format format du texte pour chaque catégorie (variable : #cat_id, #cat_status, #cat_url, #cat_name, #cat_description, #art_nb)
	 * @param string $include liste des id des utilisateurs à afficher, séparés par un ou plusieurs caractères (exemple: '001 |003 5, 45|50')
	 * @param string $exclude liste des id des utilisateurs à ne pas afficher
	 * @sortMethod string nom méthode de tri des auteurs : name, lastname, hits ou vide. Par défaut tri selon la date de l'article leplus récent pour chaque auteur
	 * @scope    global
	 * @author   Jean-Pierre Pourrez "bazooka07"
	 **/
	public function authorList($format = self::AUTHOR_PATTERN, $include = '', $exclude = '', $sortMethod='')
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowLastUserList'))) return;

		$motif = '#^\d{4}\.(?:pin,)?(?:home|\d{3})(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
		# On trie les articles par date de publication
		$arts = $this->plxMotor->plxGlob_arts->query($motif,'art','desc',0,false,'before');
		if(empty($arts)) {
			return;
		}

		$nbArts = array();
		array_walk($arts, function($item) use(&$nbArts) {
			if(preg_match('#.*\.(\d{3})\.\d{12}\.[\w-]+\.xml$#', $item, $matches)) {
				$userId = $matches[1];
				if(!isset($nbArts[$userId])) {
					$nbArts[$userId] = 1;
				} else {
					$nbArts[$userId]++;
				}
			}
		});

		# On trie les auteurs
		switch($sortMethod) {
			case 'name':
				# tri par prénom, nom des auteurs
				$users = $this->plxMotor->aUsers;
				uksort($nbArts, function($a, $b) use($users) {
					return strcasecmp($users[$a]['name'], $users[$b]['name']);
				});
				break;
			case 'lastname':
				# tri par nom (lastname) des auteurs
				$users = array_map(
					function($item) {
						$item['lastname'] = preg_replace('#.*\s+(\w[\w-]*)$#', '$1', $item['name']);
						return $item;
					},
					$this->plxMotor->aUsers
				);
				uksort($nbArts, function($a, $b) use($users) {
					return strcasecmp($users[$a]['lastname'], $users[$b]['lastname']);
				});
				break;
			case 'hits':
				# les auteurs les plus productifs en premiers
				arsort($nbArts);
				break;
			default:
				# les auteurs sont triés par date de publication de leur plus récent article
		}

		foreach ($nbArts as $userId => $v) {
			# On vérifie que cet auteur existe
			if (array_key_exists($userId, $this->plxMotor->aUsers)) {
				$userIdNum = intval($userId);
				$pattern = '@\b0*' . $userIdNum . '\b@';
				if (empty($include) or preg_match($pattern, $include)) {
					if (empty($exclude) || !preg_match($pattern, $exclude)) {
						# on a des articles pour cet auteur ou on affiche les catégories sans article
						# On modifie nos motifs
						$author = $this->plxMotor->aUsers[$userId];
						$actif = (
							($this->plxMotor->mode == 'user' and $this->plxMotor->cible == $userId) or
							($this->plxMotor->mode == 'article' and $this->plxMotor->plxRecord_arts->f('author') == $userId)
						);
						echo strtr($format, array(
							'#user_id'        => 'user-' . $userIdNum,
							'#user_url'        => $this->plxMotor->urlRewrite('?' . L_USER_URL . $userIdNum . '/' . plxUtils::urlify($author['name'])),
							'#user_name'    => plxUtils::strCheck($author['name']),
							'#user_status'    => $actif ? 'active' : 'noactive',
							'#art_nb'        => $v,
						));
					}
				}
			}
		}
	}

	/**
	 * Méthode qui affiche la liste des archives
	 *
	 * @param format    format du texte pour l'affichage (variable : #archives_id, #archives_status, #archives_selected, #archives_nbart, #archives_url, #archives_name, #archives_month, #archives_year)
	 * @scope    global
	 * @author    Stephane F, J.P. Pourrez
	 * @version 2017-06-15
	 *
	 **/
	public function archList($format = '<li id="#archives_id"><a class="#archives_status" href="#archives_url" title="#archives_name">#archives_name</a></li>')
	{

		$capture = '';

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowArchList'))) return;

		# on compte le nombre d'articles pour chaque mois de la période et pour chaque année passée
		$plxGlob_arts = clone $this->plxMotor->plxGlob_arts;
		if ($files = $plxGlob_arts->query('@^\d{4}\.(?:pin,|home,|\d{3},)*(?:' . $this->plxMotor->activeCats . ')(?:,\d{3}|,home)*\.\d{3}\.\d{12}\.[\w-]+\.xml$@', 'art', 'rsort', 0, false, 'before')) {
			# compte les années en mois !
			$periode = 12; # on détaille pour les 12 derniers mois
			$annee_mois_cc = intval(date('Y')) * 12;
			$ce_mois_ci = $annee_mois_cc + intval(date('n')) - 1; # on compte les mois à partir de 0
			$premier_mois = $ce_mois_ci - $periode; # 1er mois de la période
			$cumuls_mois = array();
			$cumuls_ans = array();
			$total = 0;

			# récupère l'année et le mois de chaque article
			$motif = '@\.\d{3}\.(\d{4})(\d{2})\d{6}\.[\w-]+\.xml$@';
			foreach ($files as $id => $filename) {
				if (preg_match($motif, $filename, $capture)) {
					$total++;
					$annee = intval($capture[1]);
					$annee_mois = $annee * 12;

					# cumul pour chaque mois de la période
					$mois = $annee_mois + intval($capture[2]) - 1; # Nb de mois depuis l'an 0
					if ($mois >= $premier_mois) {
						# l'index de $cumuls_mois est le nombre de mois depuis l'an 0
						if (isset($cumuls_mois[$mois]))
							$cumuls_mois[$mois]++;
						else
							$cumuls_mois[$mois] = 1;
					}

					# cumul pour les années écoulées
					if ($annee_mois < $annee_mois_cc) {
						# l'index de $cumuls_ans est l'année
						if (isset($cumuls_ans[$annee]))
							$cumuls_ans[$annee]++;
						else
							$cumuls_ans[$annee] = 1;
					}
				}
			}
			krsort($cumuls_mois);
			krsort($cumuls_ans);

			# Affichage pour la période en cours
			$page_actuelle = ($this->plxMotor->mode == "archives") ? $this->plxMotor->cible : '';
			# mb_internal_encoding('utf-8');
			$id = 0;
			foreach ($cumuls_mois as $m => $nbarts) {
				$id++;
				$mois = str_pad(($m % 12) + 1, 2, '0', STR_PAD_LEFT);
				$annee = intval($m / 12);
				$active = $page_actuelle == '' . $annee . $mois;
				$nom_mois = plxDate::getCalendar('month', $mois);
				echo strtr($format, array(
					'#archives_id' => 'arch-month-' . str_pad($id, 2, '0', STR_PAD_LEFT),
					'#archives_name' => $nom_mois . ' ' . $annee,
					'#archives_year' => $annee,
					'#archives_month' => $nom_mois,
					'#archives_url' => $this->plxMotor->urlRewrite('?' . L_ARCHIVES_URL . '/' . $annee . '/' . $mois),
					'#archives_nbart' => $nbarts,
					'#archives_status' => (($active) ? 'active' : 'noactive'),
					'#archives_selected' => (($active) ? 'selected' : '')
				));
			}

			# Affichage annuel
			$id = 0;
			foreach ($cumuls_ans as $annee => $nbarts) {
				$id++;
				$active = $page_actuelle == '' . $annee;
				echo strtr($format, array(
					'#archives_id' => 'arch-year-' . str_pad($id, 2, '0', STR_PAD_LEFT),
					'#archives_name' => L_YEAR . ' ' . $annee,
					'#archives_year' => $annee,
					'#archives_month' => L_YEAR,
					'#archives_url' => $this->plxMotor->urlRewrite('?' . L_ARCHIVES_URL . '/' . $annee),
					'#archives_nbart' => $nbarts,
					'#archives_status' => ($active) ? 'active' : 'noactive',
					'#archives_selected' => ($active) ? 'selected' : ''
				));
			}

			# Total des articles
			if (strpos($format, '#archives_nbart') !== false) {
				$url = '';
				if ($this->plxMotor->aConf['homestatic'] != '' and isset($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']])) {
					$url = ($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']]['active']) ? '?blog' : '';
				}
				echo strtr($format, array(
					'#archives_id' => 'arch-total',
					'#archives_name' => L_TOTAL . ' ',
					'#archives_year' => str_repeat('–', 4),
					'#archives_month' => L_TOTAL,
					'#archives_url' => $this->plxMotor->urlRewrite($url),
					'#archives_nbart' => $total,
					'#archives_status' => ($active) ? 'active' : 'noactive',
					'#archives_selected' => ($active) ? 'selected' : ''
				));
			}
		}
	}

	/**
	 * Méthode qui affiche un lien vers la page blog.php
	 *
	 * @param format    format du texte pour l'affichage (variable : #page_id, #page_status, #page_url, #page_name)
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function pageBlog($format = '<li class="#page_class #page_status" id="#page_id"><a href="#page_url" title="#page_name">#page_name</a></li>')
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowPageBlog'))) return;

		if ($this->plxMotor->aConf['homestatic'] != '' and isset($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']])) {
			if ($this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']]['active']) {
				echo strtr($format, [
					'#page_id'		=> 'static-blog',
					'#page_status'	=> (
						$this->plxMotor->get and
						preg_match('/(blog|categorie|archives|tag|article)/', $_SERVER['QUERY_STRING'] . $this->plxMotor->mode)
					) ? 'active' : 'noactive',
					'#page_class'	=> 'static menu',
					'#page_url'		=> $this->plxMotor->urlRewrite('?' . L_BLOG_URL),
					'#page_name'	=> L_PAGEBLOG_TITLE,
				]);
			}
		}
	}

	/**
	 * Méthode qui ajoute, s'il existe, le fichier css associé à un template
	 *
	 * @param css_dir     répertoire de stockage des fichiers css (avec un / à la fin)
	 * @scope    global
	 * @author    Stephane F, Thomas Ingles
	 **/
	public function templateCss($css_dir = '')
	{

		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowTemplateCss'))) return;

		$css_dir = $this->plxMotor->aConf['racine_themes'] . $this->plxMotor->style . '/' . $css_dir;#add themes/theme_folder/
		$min_css = str_replace('php', 'min.css', $this->plxMotor->template);
		$css = str_replace('php', 'css', $this->plxMotor->template);
		if (is_file($css_dir . $min_css)) {
			plxUtils::printLinkCss($css_dir . $min_css);
		} elseif (is_file($css_dir . $css)) {
			plxUtils::printLinkCss($css_dir . $css);
		}
	}

	/**
	 * Méthode qui ajoute, s'il existe, le fichier css associé aux plugins
	 *
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function pluginsCss()
	{
		# Hook Plugins
		if (eval($this->plxMotor->plxPlugins->callHook('plxShowPluginsCss'))) return;
		plxUtils::printLinkCss(PLX_PLUGINS_CSS_PATH . 'site.css');
	}

	/**
	 * Méthode qui affiche une clé de traduction appelée à partir du thème
	 *
	 * @param key    clé de traduction à afficher
	 * @return    string
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function lang($key = '')
	{
		if (isset($this->lang[$key]))
			echo $this->lang[$key];
		else
			echo $key;
	}

	/**
	 * Méthode qui renvoie une clé de traduction appelée à partir du thème
	 *
	 * @param key    clé de traduction à afficher
	 * @return    string
	 * @scope    global
	 * @author    Stephane F
	 **/
	public function getLang($key = '')
	{
		if (isset($this->lang[$key]))
			return $this->lang[$key];
		else
			return $key;
	}

	/**
	 * Méthode qui appel un hook à partir du thème
	 *
	 * @param hookName    nom du hook
	 * @param parms        parametre ou liste de paramètres sous forme de array
	 * @return    string or array
	 * @scope    global
	 * @author    Stephane F
	 *
	 * Exemple:
	 *        # sans return, passage d'un paramètre
	 *        eval($plxShow->callHook('MyPluginFunction', 'AZERTY'));
	 *        # avec return, passage de 2 paramètres à faire sous forme de tableau
	 *        $b = $plxShow->callHook('MyPluginFunction', array('AZERTY', 'QWERTY'));
	 **/
	public function callHook($hookName, $parms = null)
	{
		$return = $this->plxMotor->plxPlugins->callHook($hookName, $parms);
		if (is_array($return)) {
			ob_start();
			eval($return[0]);
			echo ob_get_clean();
			return $return[1];
		} else {
			return $return;
		}
	}

	/**
	 * Méthode qui permet d'injecter du code php au niveau d'un hook
	 *
	 * @param hookName    nom du hook
	 * @param code        code php à injecter
	 * @scope    global
	 * @author    Stephane F
	 */
	public function addCodeToHook($hookName, $userCode)
	{
		$this->plxMotor->plxPlugins->aHooks[$hookName][] = array(
			'class' => '=SHORTCODE=',
			'method' => $userCode
		);
	}

	/**
	 * Print tags in the html page or return urls for RSS feeds regarding modes (categorie, article, ...).
	 *
	 * @param mode        the view mode from plxMotor->mode (categorie, tags, user or '')
	 * @param html_tag		'', 'a', 'link', 'li' allowed
	 * @return     string      the contextualised rss feed URL
	 * @author     Pedro "P3ter" CADETE, Jean-Pierre Pourrez "bazooka07"
	 */
	public function urlPostsRssFeed($mode = '', $html_tag='')
	{
		$href = 'feed.php?rss';
		$default = $href;

		if(empty($mode)) {
			$mode = $this->plxMotor->mode;
		}

		switch ($mode) {
			case 'categorie':
				$id = $this->catId();
				$idNum = intval($id);
				$href .= '/' . L_CATEGORY_URL . $idNum; // . '/' . $this->plxMotor->aCats[$id]['url']
				$title = sprintf(L_ARTFEED_RSS_CATEGORY, $this->plxMotor->aCats[$id]['name']);
				break;
			case 'user':
				$id = $this->plxMotor->cible;
				$idNum = intval($id);
				$href .= '/' . L_USER_URL . $idNum; //  . '/' . $this->plxMotor->aUsers[$id]['login']);
				$title = sprintf(L_ARTFEED_RSS_USER, $this->plxMotor->aUsers[$id]['name']);
				break;
			case 'tags':
				$tag = plxUtils::strCheck($this->plxMotor->cible);
				$href .= '/' . L_TAG_URL . '/' . urlencode($tag);
				$title = sprintf(L_ARTFEED_RSS_TAG, $tag);
				break;
			default :
				$default = '';
				$title = L_ARTFEED_RSS;
		}

		if(empty($html_tag)) { # ---------- no tag <...> -------------
			# url the feed url address for the articles in the current mode
			return $this->plxMotor->urlRewrite($href);
		} elseif(strtolower($html_tag) == 'a') { # ---------- <a> -------------
?>
<a class="rss" href="<?= $this->plxMotor->urlRewrite($href); ?>" download><?= $title  ?></a>
<?php
		} elseif(strtolower($html_tag) == 'link') { # ---------- <link> -------------
			# Prints lhe link tags for articles and comments. Especially  for the heade of the html page
?>
	<link rel="alternate" type="application/rss+xml" title="<?= $title ?>" href="<?= $this->plxMotor->urlRewrite($href) ?>" />
<?php
			if(!empty($default)) {
?>
	<link rel="alternate" type="application/rss+xml" title="<?= L_ARTFEED_RSS ?>" href="<?= $this->plxMotor->urlRewrite($default) ?>" />
<?php
			}

			# comments
			if(empty($this->plxMotor->aConf['enable_rss_comment'])) {
				return;
			}

			$href = $this->plxMotor->racine . 'feed.php?rss/' . L_COMMENTS_URL;
?>
	<link rel="alternate" type="application/rss+xml" title="<?= L_COMFEED_RSS ?>" href="<?= $this->plxMotor->urlRewrite($href) ?>" />
<?php
			if($this->plxMotor->mode == 'article') {
				if(empty($this->plxMotor->plxRecord_coms) or $this->plxMotor->plxRecord_coms->size == 0) {
					# No available coms. Allowed ?
					$records = $this->plxMotor->plxRecord_arts;
					if(empty($records) or intval($records->f('allow_com')) == 0) {
						# Comments are closed for this article
						return;
					}
				}

				$href .= '/article' . intval($this->plxMotor->cible);
				$title = sprintf(L_COMFEED_RSS_ARTICLE, $this->plxMotor->plxRecord_arts->f('title'));
?>
	<link rel="alternate" type="application/rss+xml" title="<?= $title ?>" href="<?= $this->plxMotor->urlRewrite($href) ?>" />
<?php
			}
		} elseif(strtolower($html_tag) == 'li') { # ---------- <li> -------------
?>
	<li><a class="rss" href="<?= $this->plxMotor->urlRewrite($href); ?>" download><?= $title  ?></a></li>
<?php
			if(!empty($default)) {
?>
	<li><a class="rss" href="<?= $this->plxMotor->urlRewrite($default); ?>" download><?= L_ARTFEED_RSS  ?></a></li>
<?php
			}

			# comments
			if(empty($this->plxMotor->aConf['enable_rss_comment'])) {
				return;
			}

			$href = 'feed.php?rss/' . L_COMMENTS_URL;
?>
	<li><a class="rss" href="<?= $this->plxMotor->urlRewrite($href) ?>" download><?= L_COMFEED_RSS ?></a></li>
<?php
			if($this->plxMotor->mode == 'article') {
				if(empty($this->plxMotor->plxRecord_coms) or $this->plxMotor->plxRecord_coms->size == 0) {
					# No available coms. Allowed ?
					$records = $this->plxMotor->plxRecord_arts;
					if(empty($records) or intval($records->f('allow_com')) == 0) {
						# Comments are closed for this article
						return;
					}
				}

				$href .= '/article' . intval($this->plxMotor->cible);
				$title = sprintf(L_COMFEED_RSS_ARTICLE, $this->plxMotor->plxRecord_arts->f('title'));
?>
	<li><a class="rss" href="<?= $this->plxMotor->urlRewrite($href) ?>"><?= $title ?></a></li>
<?php
			}
		}
	}

	/**
	 * Print link tags for the html page for RSS feeds regarding modes (categorie, article, ...)
	 *
	 * @param $rewrite canonical url by default
	 * @author Jean-Pierre Pourrez "bazooka07"
	 * */
	public function rssLinks($rewrite=false) {
		if(empty($this->plxMotor->aConf['enable_rss'])) {
			return;
		}

		switch($this->plxMotor->mode) {
			case 'categorie':
				$id = $this->catId();
				$idNum = intval($id);
				$query = '/' . L_CATEGORY_URL . $idNum;
				$title = sprintf(L_ARTFEED_RSS_CATEGORY, $this->plxMotor->aCats[$id]['name']);
				break;
			case 'user':
				$id = $this->plxMotor->cible;
				$idNum = intval($id);
				$query = '/' . L_USER_URL . $idNum;
				$title = sprintf(L_ARTFEED_RSS_USER, $this->plxMotor->aUsers[$id]['name']);
				break;
			case 'tags':
				$tag = plxUtils::strCheck($this->plxMotor->cible);
				$query = '/' . L_TAG_URL . '/' . plxUtils::strCheck($tag);
				$title = sprintf(L_ARTFEED_RSS_TAG, $this->plxMotor->cible);
				break;
			default:
				$query = '';
				$title = L_ARTFEED_RSS;
		}
		$href = $this->plxMotor->racine . 'feed.php?rss' . $query;
		if($rewrite) {
			$href = $this->urlRewrite($href);
		}
?>
	<link rel="alternate" type="application/rss+xml" title="<?= $title ?>" href="<?= $href ?>" />
<?php

		if(empty($this->plxMotor->aConf['enable_rss_comment'])) {
			return;
		}

		if($this->plxMotor->mode == 'article') {
			if(empty($this->plxRecord_arts) or intval($this->plxRecord_arts->f('allow_com')) == 0) {
				# Comments areclosed for this article
				return;
			}

			$query = '/article' . intval($this->plxMotor->cible);
			$title = sprintf(L_COMFEED_RSS_ARTICLE, $this->plxMotor->plxRecord_arts->f('title'));
		} else {
			$query = '';
			$title = L_COMFEED_RSS;
		}
		$href = $this->plxMotor->racine . 'feed.php?rss/' . L_COMMENTS_URL . $query;
?>
	<link rel="alternate" type="application/rss+xml" title="<?= $title ?>" href="<?= $href ?>" />
<?php
	}

	public function allowRSS() {
		return !empty($this->plxMotor->aConf['enable_rss']);
	}
}

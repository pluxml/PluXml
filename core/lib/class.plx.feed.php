<?php

/**
 * Classe plxFeed responsable du traitement global des flux de syndication
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Amaury Graillat
 **/

const PLX_FEED = true;

class plxFeed extends plxMotor {

	const FORMAT_DATE = 'YmdHi';

	/**
	 * Méthode qui se charger de créer le Singleton plxFeed
	 *
	 * @return	plxFeed		retourne une instance de la classe plxFeed
	 * @author	Stephane F, J.P. Pourrez "Bazooka07"
	 **/
	public static function getInstance(){
		if (empty(parent::$instance))
			parent::$instance = new plxFeed(path('XMLFILE_PARAMETERS'));
		return parent::$instance;
	}

	/**
	 * Constructeur qui initialise certaines variables de classe
	 * et qui lance le traitement initial
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	protected function __construct($filename) {

		# On parse le fichier de configuration
		$this->getConfiguration($filename);

		# récupération des paramètres dans l'url
		$this->get = plxUtils::getGets();

		# gestion du timezone
		date_default_timezone_set($this->aConf['timezone']);

		# chargement des variables
		$this->racine = $this->aConf['racine'];
		$this->bypage = $this->aConf['bypage_feed'];
		$this->tri = 'desc';
		$this->clef = (!empty($this->aConf['clef']))?$this->aConf['clef']:'';

		# Traitement des plugins
		$this->plxPlugins = new plxPlugins($this->aConf['default_lang']);
		$this->plxPlugins->loadPlugins();

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxFeedConstructLoadPlugins'));
		}

		# Traitement sur les répertoires des articles et des commentaires
		$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles'],false,true,'arts');
		$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires']);

		# Récupération des données dans les autres fichiers xml
		$this->getCategories(path('XMLFILE_CATEGORIES'));
		$this->getUsers(path('XMLFILE_USERS'));
		$this->getTags(path('XMLFILE_TAGS'));

		# Récupération des articles appartenant aux catégories actives
		$this->getActiveArts();

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxFeedConstruct'));
		}
	}

	/**
	 * Méthode qui effectue une analyse de la situation et détermine
	 * le mode à appliquer. Cette méthode alimente ensuite les variables
	 * de classe adéquates
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function feedRouter() {

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			if(eval($this->plxPlugins->callHook('plxFeedPreChauffageBegin'))) return;
		}

		if($this->get AND preg_match('#^(?:atom/|rss/)?categorie(\d+)/?#',$this->get,$capture)) {
			$this->mode = 'article'; # Mode du flux
			# On récupère la catégorie cible
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complète sur 3 caractères
			# On modifie le motif de recherche
			$this->motif = '#^\d{4}.((?:home,|\d{3},)*(?:'.$this->cible.')(?:,\d{3}|,home)*)\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
		}
		elseif($this->get AND preg_match('#^(?:atom/|rss/)?commentaires/?$#',$this->get)) {
			$this->mode = 'commentaire'; # Mode du flux
		}
		elseif($this->get AND preg_match('#^(?:atom/|rss/)?tag/([\w-]+)/?$#', $this->get, $capture)) {
			$this->mode = 'tag';
			$this->cible = $capture[1];
			$ids = array();
			$datetime = date(self::FORMAT_DATE);
			foreach($this->aTags as $idart => $tag) {
				if($tag['date']<=$datetime) {
					$tags = array_map("trim", explode(',', $tag['tags']));
					$tagUrls = array_map(array('plxUtils', 'urlify'), $tags);
					if(in_array($this->cible, $tagUrls)) {
						if(!isset($ids[$idart])) $ids[$idart] = $idart;
						if(!isset($cibleName)) {
							$key = array_search($this->cible, $tagUrls);
							$cibleName=$tags[$key];
						}
					}
				}
			}
			if(sizeof($ids)>0) {
				$this->motif = '#('.implode('|', $ids).').(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.\d{12}.[\w-]+.xml$#';
			} else
				$this->motif = '';

		}
		elseif($this->get AND preg_match('#^(?:atom/|rss/)?commentaires/article(\d+)/?$#',$this->get,$capture)) {
			$this->mode = 'commentaire'; # Mode du flux
			# On récupère l'article cible
			$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT); # On complète sur 4 caractères
			# On modifie le motif de recherche
			$this->motif = '#^'.$this->cible.'.(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.\d{12}.[\w-]+.xml$#';
		}
		elseif($this->get AND preg_match('#^admin([\w-]+)/commentaires/(hors|en)-ligne/?$#',$this->get,$capture)) {
			$this->mode = 'admin'; # Mode du flux
			$this->cible = '-';	# /!\: il ne faut pas initialiser à blanc sinon ça prend par défaut les commentaires en ligne (faille sécurité)
			if ($capture[1] == $this->clef) {
				if($capture[2] == 'hors')
					$this->cible = '_';
				elseif($capture[2] == 'en')
					$this->cible = '';
			}
		} else {
			$this->mode = 'article'; # Mode du flux
			# On modifie le motif de recherche
			$this->motif = '#^\d{4}.(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.\d{12}.[\w-]+.xml$#';
		}

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxFeedPreChauffageEnd'));
		}

	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null ou redirection si une erreur est détectée
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function feedRun() {

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			if(eval($this->plxPlugins->callHook('plxFeedDemarrageBegin'))) return;
		}

		# Flux de commentaires d'un article précis
		if($this->mode == 'commentaire' AND $this->cible) {
			if(!$this->getArticles()) { # Aucun article, on redirige
				$this->cible = $this->cible + 0;
				header('Location: '.$this->urlRewrite('?article'.$this->cible.'/'));
				exit;
			} else { # On récupère les commentaires
				$regex = '/^'.$this->cible.'.\d{10}-\d+.xml$/';
				$this->getCommentaires($regex,'rsort',0,$this->bypage);
			}
		}
		# Flux de commentaires global
		elseif($this->mode == 'commentaire') {
			$regex = '#^\d{4}.\d{10}-\d+.xml$#';
			$this->getCommentaires($regex,'rsort',0,$this->bypage);
		}
		# Flux admin
		elseif($this->mode == 'admin') {
			if(empty($this->clef)) { # Clef non initialisée
				header('Content-Type: text/plain; charset='.PLX_CHARSET);
				echo L_FEED_NO_PRIVATE_URL;
				exit;
			}
			# On récupère les commentaires
			$this->getCommentaires('#^'.$this->cible.'\d{4}.\d{10}-\d+.xml$#','rsort',0,$this->bypage,'all');
		}
		# Flux d'articles pour un tag
		elseif($this->mode == 'tag') {
			if(empty($this->motif)) {
				header('Location: '.$this->urlRewrite('?tag/'.$this->cible.'/'));
				exit;
			} else {
				$this->getArticles(); # Récupération des articles (on les parse)
			}
		}
		# Flux d'articles
		else {
			# Flux des articles d'une catégorie précise
			if($this->cible) {
				# On va tester la catégorie
				if(empty($this->aCats[$this->cible]) OR !$this->aCats[$this->cible]['active']) { # Pas de catégorie, on redirige
					$this->cible = $this->cible + 0;
					header('Location: '.$this->urlRewrite('?categorie'.$this->cible.'/'));
					exit;
				}
			}
			$this->getArticles(); # Récupération des articles (on les parse)
		}

		# Selon le mode, on appelle la méthode adéquate
		switch($this->mode) {
			case 'tag':
			case 'article' : $this->getRssArticles(); break;
			case 'commentaire' : $this->getRssComments(); break;
			case 'admin' : $this->getAdminComments(); break;
			default : break;
		}

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxFeedDemarrageEnd'));
		}

	}

	/**
	 * Méthode qui affiche le flux rss des articles du site
	 * @author	Florent MONTHEL, Stephane F, Amaury GRAILLAT
	 **/
	public function getRssArticles() {

		# Initialisation
		$entry_link = '';
		if($this->mode == 'tag') {
			$title = $this->aConf['title'] . ' - '. L_ARTFEED_RSS_TAG .' : ' . $this->cible;
			$link = $this->urlRewrite('?tag/' . $this->cible);
		}
		elseif($this->cible) { # Articles d'une catégorie
			$catId = $this->cible + 0;
			$title = $this->aConf['title'] . ' - '.$this->aCats[ $this->cible ]['name'];
			$link = $this->urlRewrite('?categorie'.$catId.'/'.$this->aCats[ $this->cible ]['url']);
		} else { # Articles globaux
			$title = $this->aConf['title'];
			$link = $this->urlRewrite();
		}

		$lastBuildDate = (!empty($this->plxRecord_arts)) ? $this->plxRecord_arts->lastUpdated() : date(self::FORMAT_DATE);
		echo XML_HEADER;
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?= plxUtils::cdataCheck($title) ?></title>
		<link><?= $link ?></link>
		<language><?= $this->aConf['default_lang'] ?></language>
		<description><?= plxUtils::cdataCheck($this->aConf['description']) ?></description>
		<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="<?= $this->urlRewrite('feed.php?'.$this->get) ?>" />
		<lastBuildDate><?= plxDate::dateIso2rfc822($lastBuildDate) ?></lastBuildDate>
		<generator>PluXml</generator>
<?php
		# On va boucler sur les articles (s'il y en a)
		if(!empty($this->plxRecord_arts)) {
			while($this->plxRecord_arts->loop()) {
				$thumb = '';
				$src = $this->plxRecord_arts->f('thumbnail');
				if($src!='') {
					$src = (strpos($src, 'http')===false ? $this->racine.$src : $src);
					$alt = plxUtils::strCheck($this->plxRecord_arts->f('thumbnail_alt'));
					$thumb = '<img src="' . plxUtils::strCheck($src) .'" alt="' . $alt . '" />' . PHP_EOL;
				}
				# Traitement initial
				if($this->aConf['feed_chapo']) {
					$content = $this->plxRecord_arts->f('chapo');
					if(trim($content)=='') $content = $this->plxRecord_arts->f('content');
				} else {
					$content = $this->plxRecord_arts->f('chapo').$this->plxRecord_arts->f('content');
				}
				$content .= $this->aConf['feed_footer'];
				$artId = $this->plxRecord_arts->f('numero') + 0;
				$author = $this->aUsers[$this->plxRecord_arts->f('author')]['name'];
?>
		<item>
			<title><?= plxUtils::cdataCheck($this->plxRecord_arts->f('title')) ?></title>
			<link><?= $this->urlRewrite('?article' . $artId . '/' . $this->plxRecord_arts->f('url')) ?></link>
			<guid><?= $this->urlRewrite('?article' . $artId . '/' . $this->plxRecord_arts->f('url')) ?></guid>
			<description><?= plxUtils::cdataCheck(plxUtils::rel2abs($this->racine, $thumb . $content)) ?></description>
			<pubDate><?= plxDate::dateIso2rfc822($this->plxRecord_arts->f('date')) ?></pubDate>
			<dc:creator><?= plxUtils::cdataCheck($author) ?></dc:creator>
<?php
				if(!empty($this->plxPlugins)) {
					# Hook plugins
					$entry = '';
					eval($this->plxPlugins->callHook('plxFeedRssArticlesXml'));
					if(!empty($entry)) { echo $entry; }
				}
?>
		</item>
<?php
			}
		}
?>
	</channel>
</rss>
<?php
	}

	/**
	 * Méthode qui affiche le flux rss des commentaires du site
	 * @author	Florent MONTHEL, Amaury GRAILLAT
	 **/
	public function getRssComments() {

		# Traitement initial
		if($this->cible) { # Commentaires d'un article
			$artId = $this->plxRecord_arts->f('numero') + 0;
			$title = $this->aConf['title'] . ' - ' . $this->plxRecord_arts->f('title').' - ' . ucfirst(L_COMMENTS);
			$link = $this->urlRewrite('index.php?article' . $artId . '/' . $this->plxRecord_arts->f('url'));
		} else { # Commentaires globaux
			$title = $this->aConf['title'] . ' - ' . ucfirst(L_COMMENTS);
			$link = $this->urlRewrite('index.php');
		}
		$lastBuildDate = (!empty($this->plxRecord_coms)) ? $this->plxRecord_coms->lastUpdated() : date(self::FORMAT_DATE);

		# On affiche le flux
		echo XML_HEADER;
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="<?= $this->urlRewrite('feed.php?rss/commentaires/') ?>" />
		<title><?= plxUtils::cdataCheck($title) ?></title>
		<link><?= $link ?></link>
		<language><?= $this->aConf['default_lang'] ?></language>
		<description><?= plxUtils::cdataCheck($this->aConf['description']) ?></description>
		<lastBuildDate><?= plxDate::dateIso2rfc822($lastBuildDate) ?></lastBuildDate>
		<generator>PluXml</generator>
<?php
		# On va boucler sur les commentaires (s'il y en a)
		if(!empty($this->plxRecord_coms)) {
			while($this->plxRecord_coms->loop()) {
				# Traitement initial
				if(isset($this->activeArts[$this->plxRecord_coms->f('article')])) {
					$artId = $this->plxRecord_coms->f('article') + 0;
					if($this->cible) { # Commentaires d'un article
						$title_com = $this->plxRecord_arts->f('title') . ' - ' .
							L_WRITTEN_BY . ' ' . $this->plxRecord_coms->f('author') . ' @ ' .
							plxDate::formatDate($this->plxRecord_coms->f('date'), plxDate::FORMAT_TIME);
						$comId = 'c' . $this->plxRecord_coms->f('article') . '-' . $this->plxRecord_coms->f('index');
						$link_com = $this->urlRewrite('index.php?article' . $artId . '/' . $this->plxRecord_arts->f('url') . '#' . $comId);
					} else { # Commentaires globaux
						$title_com = $this->plxRecord_coms->f('author').' @ ' .
							plxDate::formatDate($this->plxRecord_coms->f('date'), plxDate::FORMAT_TIME);
						$artInfo = $this->artInfoFromFilename($this->plxGlob_arts->aFiles[$this->plxRecord_coms->f('article')]);
						$comId = 'c' . $this->plxRecord_coms->f('article') . '-' . $this->plxRecord_coms->f('index');
						$link_com = $this->urlRewrite('index.php?article' . $artId . '/' . $artInfo['artUrl'] . '#' . $comId);
					}
?>
		<item>
			<title><?= plxUtils::cdataCheck($title_com) ?></title>
			<link><?= $link_com ?></link>
			<guid><?= $link_com ?></guid>
			<description><?= plxUtils::cdataCheck($this->plxRecord_coms->f('content')) ?></description>
			<pubDate><?= plxDate::dateIso2rfc822($this->plxRecord_coms->f('date')) ?></pubDate>
			<dc:creator><?= plxUtils::cdataCheck($this->plxRecord_coms->f('author')) ?></dc:creator>
<?php
					if(!empty($this->plxPlugins)) {
						# Hook plugins
						$entry = '';
						eval($this->plxPlugins->callHook('plxFeedRssCommentsXml'));
						if(!empty($entry)) { echo $entry; }
					}
?>
		</item>
<?php
				}
			}
		}
?>
	</channel>
</rss>
<?php
	}

	/**
	 * Méthode qui affiche le flux RSS des commentaires du site pour l'administration
	 * @author	Florent MONTHEL, Amaury GRAILLAT
	 **/
	public function getAdminComments() {
		# Traitement initial
		$url_base = $this->racine . substr(PLX_ADMIN_PATH, strlen(PLX_ROOT));

		$offline = ($this->cible == '_'); # Commentaires en/hors ligne
		$link = $url_base . 'comments.php?sel=' . ($offline ? 'offline' : 'online');
		$title = $this->aConf['title'].' - ' . ($offline ? L_FEED_OFFLINE_COMMENTS : L_FEED_ONLINE_COMMENTS);
		$link_feed = $this->racine.'feed.php?admin' . $this->clef . '/commentaires/' . ($offline ? 'hors-ligne' : 'en-ligne');

		$lastBuildDate = (!empty($this->plxRecord_coms)) ? $this->plxRecord_coms->lastUpdated() : date(self::FORMAT_DATE);

		# On affiche le flux
		echo XML_HEADER;
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?= plxUtils::cdataCheck($title) ?></title>
		<description><?= plxUtils::cdataCheck($this->aConf['description']) ?></description>
		<link><?= $link ?></link>
		<language><?= $this->aConf['default_lang'] ?></language>
		<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="<?= $link_feed ?>" />
		<lastBuildDate><?= plxDate::dateIso2rfc822($lastBuildDate) ?></lastBuildDate>
		<generator>PluXml</generator>
<?php
		# On va boucler sur les commentaires (s'il y en a)
		if(!empty($this->plxRecord_coms)) {
			$last_updated = '';
			while($this->plxRecord_coms->loop()) {
				$artId = $this->plxRecord_coms->f('article') + 0;
				$comId = $this->cible.$this->plxRecord_coms->f('article').'.'.$this->plxRecord_coms->f('numero');
				$title_com = $this->plxRecord_coms->f('author').' @ ';
				$title_com .= plxDate::formatDate($this->plxRecord_coms->f('date'), plxDate::FORMAT_TIME);
				$link_com = $url_base . 'comment.php?c=' . $comId;
				# On vérifie la date de publication
				if($this->plxRecord_coms->f('date') > $last_updated) # ???? variable inutilisée
					$last_updated = $this->plxRecord_coms->f('date');
				# On affiche le flux dans un buffer
?>
		<item>
			<title><?= plxUtils::cdataCheck($title_com, ENT_QUOTES, PLX_CHARSET) ?></title>
			<link><?= $link_com ?></link>
			<guid><?= $link_com ?></guid>
			<description><?= plxUtils::cdataCheck($this->plxRecord_coms->f('content')) ?></description>
			<pubDate><?= plxDate::dateIso2rfc822($this->plxRecord_coms->f('date')) ?></pubDate>
			<dc:creator><?= plxUtils::cdataCheck($this->plxRecord_coms->f('author')) ?></dc:creator>
<?php
				if(!empty($this->plxPlugins)) {
					# Hook plugins
					$entry = '';
					eval($this->plxPlugins->callHook('plxFeedAdminCommentsXml'));
					if(!empty($entry)) { echo $entry; }
				}
?>
		</item>
<?php
			}
		}
?>
	</channel>
</rss>
<?php
	}
}

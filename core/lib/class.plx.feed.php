<?php

/**
 * Classe plxFeed responsable du traitement global des flux de syndication
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Amaury Graillat
 **/

# https://www.rssboard.org/rss-specification
# https://validator.w3.org/feed/

const PLX_FEED = true;

class plxFeed extends plxMotor {

	public $rssAttachment = 'pluxml.rss';
	public $rssTitle = null;
	public $rssLink = null;
	public $rssLastBuildDate = null;
	public $clef = null; # Feed, PHP 8

	/**
	 * Méthode qui se charger de créer le Singleton plxFeed
	 *
	 * @return	objet			retourne une instance de la classe plxFeed
	 * @author	Stephane F
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

		parent::__construct($filename);
		$this->bypage = $this->aConf['bypage_feed'];
		$this->tri = 'desc'; # pour les articles
		$this->clef = $this->aConf['clef'];

		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedConstruct'));
	}

	public static function notFound() {
		$msg = 'Bad request for RSS feed.';
		header('HTTP/1.0 404 Not Found');
		header('Content-Type: text/plain;charset=UTF-8');
		header('Content-length: ' . strlen($msg));
		echo $msg;
		exit;
	}

	/**
	 * Méthode qui effectue une analyse de la situation et détermine
	 * le mode à appliquer. Cette méthode alimente ensuite les variables
	 * de classe adéquates
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function fprechauffage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxFeedPreChauffageBegin'))) return;

		# Par defaut : flux RSS pour les articles
		$this->mode = 'article'; # Mode du flux
		$this->motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
		$this->cible = '';
		$idioms = plxUtils::getI18nUrls();

		if(!empty($this->get)) {
			if(preg_match('#^(?:atom/|rss/)?(?:' . implode('|', $idioms['L_CATEGORY_URL']) . ')(\d+)/?#', $this->get, $capture)) {
				$this->mode = 'categorie'; # Mode du flux
				# On récupère la catégorie cible et on complète sur 3 caractères
				$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT);
				# On vérifie que la catégorie existe et est active
				if(!isset($this->aCats[$this->cible]) OR !$this->aCats[$this->cible]['active']) {
					self::notFound();
				}

				# On modifie le motif de recherche
				$this->motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*(?:'.$this->cible.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
			}
			elseif(preg_match('#^(?:atom/|rss/)?(?:' . implode('|', $idioms['L_USER_URL']) . ')(\d+)/?#',$this->get,$capture)) {
				$this->mode = 'user'; # Mode du flux
				# On récupère l'id de l'utilisateur et on complète sur 3 caractères
				$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT);
				# On vérifie que le user existe et est active
				if(!isset($this->aUsers[$this->cible]) OR !$this->aUsers[$this->cible]['active']) {
					self::notFound();
				}

				# On modifie le motif de recherche
				$this->motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*(?:home|\d{3})(?:,\d{3})*\.' . $this->cible . '\.\d{12}\.[\w-]+\.xml$#';
			}
			elseif(preg_match('#^(?:atom/|rss/)?(?:' . implode('|', $idioms['L_TAG_URL']) . ')/([\w-]+)/?$#', $this->get, $capture)) {
				$this->mode = 'tag';
				$this->cible = $capture[1];
				$ids = array();
				$datetime = date('YmdHi');
				foreach($this->aTags as $idart => $tag) {
					if($tag['active'] and $tag['date'] <= $datetime) {
						$tags = array_map('trim', explode(',', $tag['tags']));
						$tagUrls = array_map(array('plxUtils', 'urlify'), $tags);
						if(in_array($this->cible, $tagUrls) and !isset($ids[$idart])) {
							$ids[$idart] = true;
						}
					}
				}
				# On vérifie qu'il y a des articles pour ce tag
				if(empty($ids)) {
					self::notFound();
				}

				# Notice 000 and home are always in activeCats
				$this->motif = '#('.implode('|', array_keys($ids)).')\.(?:pin,|home,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
			}
			elseif(preg_match('#^(?:atom/|rss/)?(?:' . implode('|', $idioms['L_COMMENTS_URL']) . ')/?$#',$this->get)) {
				$this->mode = 'commentaire'; # Mode du flux
			}
			elseif(preg_match('#^(?:atom/|rss/)?(?:' . implode('|', $idioms['L_COMMENTS_ARTICLE_URL']) . ')(\d+)/?$#',$this->get,$capture)) {
				$this->mode = 'commentaire'; # Mode du flux
				# On récupère l'article cible et on complète sur 4 caractères
				$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT);
				# On vérifie que l'article est publié
				$this->motif = '#^' . $this->cible . '\.(?:pin,|home,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
				if(!$this->getArticles()) {
					# Article non publié
					self::notFound();
				}

				# On modifie le motif de recherche
				$this->motif = '#^'.$this->cible.'\.(?:pin,|home,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
			}
			elseif(preg_match('#^admin([\w-]+)/commentaires/(hors|en)-ligne/?$#',$this->get,$capture)) {
				$this->mode = 'admin'; # Mode du flux
				if ($capture[1] == $this->clef) {
					$this->cible = ($capture[2] == 'hors') ? '_' : '';
				} else {
					header('Content-Type: text/plain; charset='.PLX_CHARSET);
					echo L_FEED_NO_PRIVATE_URL;
					exit;
				}
			} elseif(!preg_match('#^(?:atom|rss)/?$#',$this->get)) {
				self::notFound();
			}
		}

		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedPreChauffageEnd'));

	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null ou redirection si une erreur est détectée
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function fdemarrage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxFeedDemarrageBegin'))) return;

		if($this->mode == 'commentaire') {
			$idArts = !empty($this->cible) ? $this->cible : '\d{4}';
			$this->getCommentaires('#^(' . $idArts . ')\.(\d{10,})-(\d+)\.xml$#', 'rsort', 0, $this->bypage);
			$this->getRssComments();
		} elseif($this->mode == 'admin') {
			# Flux admin
			# On récupère les commentaires
			$this->getCommentaires('#^' . $this->cible . '(\d{4})\.(\d{10,})-(\d+)\.xml$#', 'rsort', 0, false, 'all');
			$this->getAdminComments();
		} else {
			# Flux des articles, éventuellement pour une catégorie, un utilisateur ou un tag particulier
			$this->getArticles(); # Récupération des articles (on les parse)
			$this->getRssArticles();
		}

		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedDemarrageEnd'));

	}

	/**
	 * Méthode qui imprime le début du flux RSS
	 *
	 * @param $plxRecord tableau des enregistrements (en général $this->plxRecord_arts ou $this->plxRecord_coms)
	 * @author J.P. Pourrez @bazooka07
	 **/
	public function printRSSTop(&$plxRecord) {
		if(empty($this->rssLastBuildDate) and !empty($plxRecord)) {
			$this->rssLastBuildDate = $plxRecord->lastUpdateDate();
		}

		if(empty($this->rssLastBuildDate)) {
			# $plxRecord->lastUpdateDate() retourne NUll si aucun enregistrement.
			$this->rssLastBuildDate = date('YmdHi');
		}
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="<?= $this->urlRewrite('feed.php?' . $this->get) ?>" />
		<title><?= plxUtils::strCheck($this->rssTitle, true, null) ?></title>
		<link><?= $this->rssLink ?></link>
		<lastBuildDate><?= plxDate::dateIso2rfc822($this->rssLastBuildDate) ?></lastBuildDate>
		<language><?= $this->aConf['default_lang'] ?></language>
		<description><?= plxUtils::strCheck($this->aConf['description'], true, null) ?></description>
		<generator>PluXml</generator>
<?php

		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedPrintRSSTop'));
	}

	/**
	 * Méthode qui imprime la fin du flux RSS
	 *
	 * @author J.P. Pourrez @bazooka07
	 **/
	public function printRSSBottom() {
?>
	</channel>
</rss>
<?php
	}

	/**
	 * Méthode qui affiche le flux rss des articles du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Stephane F, Amaury GRAILLAT
	 **/
	public function getRssArticles() {

		# Initialisation
		# valeurs par defaut : Articles globaux
		$this->rssTitle = $this->aConf['title'];
		$this->rssLink = $this->urlRewrite();
		$this->rssAttachment = 'posts.rss';
		switch($this->mode) {
			case 'tag':
				$this->rssTitle = $this->aConf['title'] . ' - ' . L_PAGETITLE_TAG . ' ' . $this->cible;
				$this->rssLink = $this->urlRewrite('?' . L_TAG_URL . '/' . $this->cible);
				$this->rssAttachment =  L_TAG_URL . '-' . $this->cible . '.rss';
				break;
			case 'categorie':
				$this->rssTitle = $this->aConf['title'] . ' - ' . $this->aCats[ $this->cible ]['name'];
				$this->rssLink = $this->urlRewrite('?' . L_CATEGORY_URL . intval($this->cible));
				$this->rssAttachment = L_CATEGORY_URL . '-' . ltrim($this->cible, '0') . '-' . $this->aCats[ $this->cible ]['name'] . '.rss';
				break;
			case 'user':
				$this->rssTitle = $this->aConf['title'] . ' - ' . $this->aUsers[ $this->cible ]['name'];
				$this->rssLink = $this->urlRewrite('?' . L_USER_URL . intval($this->cible));
				$this->rssAttachment = L_USER_URL . '-' . $this->aUsers[ $this->cible ]['name'] . '.rss';
				break;
			default:
		}

		$this->printRSSTop($this->plxRecord_arts);

		# On va boucler sur les articles si possible
		if($this->plxRecord_arts) {
			while($this->plxRecord_arts->loop()) {
				$length = '';
				$mimetype = '';
				$src = $this->plxRecord_arts->f('thumbnail');
				if(!empty($src) and !preg_match('#^\w?:#', $src)) {
					$thumb = plxUtils::thumbName($src);
					if(file_exists(PLX_ROOT . $thumb)) {
						$mimetype = mime_content_type(PLX_ROOT . $thumb);
						$length = filesize(PLX_ROOT . $thumb);
						$src = $this->racine . $thumb;
					} elseif(file_exists(PLX_ROOT . $src)) {
						$mimetype = mime_content_type(PLX_ROOT . $src);
						$length = filesize(PLX_ROOT . $src);
						$src = $this->racine . $src;
					}
				}

				# Traitement initial
				if($this->aConf['feed_chapo']) {
					$content = $this->plxRecord_arts->f('chapo');
					if(trim($content)=='') $content = $this->plxRecord_arts->f('content');
				} else {
					$content = $this->plxRecord_arts->f('chapo').$this->plxRecord_arts->f('content');
				}
				if(!empty(trim($this->aConf['feed_footer']))) {
					$content .= $this->aConf['feed_footer'];
				}
				$artId = $this->plxRecord_arts->f('numero') + 0;
				$author = $this->aUsers[$this->plxRecord_arts->f('author')]['name'];
				# On vérifie la dernière date de publication

				if(empty($this->rssLastBuildDate )) {
					$this->rssLastBuildDate = $this->plxRecord_arts->f('date');
				}

				# On affiche le flux dans un buffer
?>
		<item>
			<title><?= plxUtils::strCheck($this->plxRecord_arts->f('title')) ?></title>
			<link><?= $this->urlRewrite('?' . L_ARTICLE_URL . $artId . '/' . $this->plxRecord_arts->f('url')) ?></link>
			<guid><?= $this->racine . '?' . L_ARTICLE_URL . $artId ?></guid>
<?php
				if(!empty($length)) {
?>
			<enclosure url="<?= $src ?>" length="<?= $length ?>" type="<?= $mimetype ?>" />
<?php
				}
?>
			<description><?= plxUtils::strCheck(plxUtils::rel2abs($this->racine, $content), true) ?></description>
			<pubDate><?= plxDate::dateIso2rfc822($this->plxRecord_arts->f('date')) ?></pubDate>
			<dc:creator><?= plxUtils::strCheck($author) ?></dc:creator>
<?php
				# Hook plugins
				eval($this->plxPlugins->callHook('plxFeedRssArticlesXml'));
?>
		</item>
<?php
			}
		}

		$this->printRSSBottom();
	}

	/**
	 * Méthode qui affiche le flux rss des commentaires du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Amaury GRAILLAT
	 **/
	public function getRssComments() {

		# Traitement initial
		$entry_link = '';
		$entry = '';
		if($this->cible) { # Commentaires d'un article
			$artId = $this->plxRecord_arts->f('numero') + 0;
			$this->rssTitle = $this->aConf['title'].' - '.$this->plxRecord_arts->f('title').' - '.L_FEED_COMMENTS;
			$this->rssLink = $this->urlRewrite('?'.L_ARTICLE_URL.$artId.'/'.$this->plxRecord_arts->f('url'));
			$this->rssAttachment = L_COMMENTS_URL . '-' . $artId . '.rss';
		} else { # Commentaires globaux
			$this->rssTitle = $this->aConf['title'].' - '.L_FEED_COMMENTS;
			$this->rssLink = $this->urlRewrite();
			$this->rssAttachment = L_COMMENTS_URL . '.rss';
		}

		$this->printRSSTop($this->plxRecord_coms);

		# On va boucler sur les commentaires (s'il y en a)
		if($this->plxRecord_coms) {
			while($this->plxRecord_coms->loop()) {
				# Traitement initial
				if(isset($this->activeArts[$this->plxRecord_coms->f('article')])) {
					$artId = $this->plxRecord_coms->f('article') + 0;
					$comId = 'c'.$this->plxRecord_coms->f('index');
					if($this->cible) { # Commentaires d'un article
						$title_com = $this->plxRecord_arts->f('title').' - ';
						$title_com .= L_FEED_WRITTEN_BY.' '.$this->plxRecord_coms->f('author').' @ ';
						$title_com .= plxDate::formatDate($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
						$link_com = $this->urlRewrite('?'.L_ARTICLE_URL.$artId.'/'.$this->plxRecord_arts->f('url').'#'.$comId);
					} else { # Commentaires globaux
						$title_com = $this->plxRecord_coms->f('author').' @ ';
						$title_com .= plxDate::formatDate($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
						$artInfo = $this->artInfoFromFilename($this->plxGlob_arts->aFiles[$this->plxRecord_coms->f('article')]);
						$link_com = $this->urlRewrite('?'.L_ARTICLE_URL.$artId.'/'.$artInfo['artUrl'].'#'.$comId);
					}

					# On affiche le flux dans un buffer
?>
		<item>
			<title><?= plxUtils::strCheck(html_entity_decode($title_com, ENT_QUOTES, PLX_CHARSET), true, null)  ?></title>
			<link><?= $link_com ?></link>
			<guid isPermaLink="false"><?= md5(preg_replace('#^https?://#', '', $this->racine) . $comId) ?></guid>
			<description><?= plxUtils::strCheck(html_entity_decode($this->plxRecord_coms->f('content'), ENT_QUOTES, PLX_CHARSET), true, null) ?></description>
			<pubDate><?= plxDate::dateIso2rfc822($this->plxRecord_coms->f('date')) ?></pubDate>
			<dc:creator><?= plxUtils::strCheck($this->plxRecord_coms->f('author')) ?></dc:creator>
<?php
					# Hook plugins
					eval($this->plxPlugins->callHook('plxFeedRssCommentsXml'));
?>
		</item>
<?php
				}
			}
		}

		$this->printRSSBottom();
	}

	/**
	 * Méthode qui affiche le flux RSS des commentaires du site pour l'administration
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Amaury GRAILLAT
	 **/
	public function getAdminComments() {
		# Traitement initial
		if($this->cible == '_') { # Commentaires hors ligne
			$this->rssLink = $this->racine . 'core/admin/comments.php?sel=offline&amp;page=1';
			$this->rssTitle = $this->aConf['title'] . ' - ' . L_FEED_OFFLINE_COMMENTS;
			$link_feed = $this->racine.'feed.php?admin'.$this->clef.'/commentaires/hors-ligne';
			$this->rssAttachment = 'comments-offline.rss';
		} else { # Commentaires en ligne
			$this->rssLink = $this->racine.'core/admin/comments.php?sel=online&amp;page=1';
			$this->rssTitle = $this->aConf['title'].' - '.L_FEED_ONLINE_COMMENTS;
			$link_feed = $this->racine.'feed.php?admin'.$this->clef.'/commentaires/en-ligne';
			$this->rssAttachment = 'comments-online.rss';
		}

		$this->printRSSTop($this->plxRecord_coms);

		# On va boucler sur les commentaires (s'il y en a)
		if($this->plxRecord_coms) {
			while($this->plxRecord_coms->loop()) {
				$artId = $this->plxRecord_coms->f('article') + 0;
				$comId = $this->cible.$this->plxRecord_coms->f('article').'.'.$this->plxRecord_coms->f('numero');
				$title_com = $this->plxRecord_coms->f('author').' @ ';
				$title_com .= plxDate::formatDate($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
				$link_com = $this->racine.'core/admin/comment.php?c=' . $comId;
?>
		<item>
			<title><?= plxUtils::strCheck(html_entity_decode($title_com, ENT_QUOTES, PLX_CHARSET), true, null) ?></title>
			<link><?= $link_com ?></link>
			<guid isPermaLink="false"><?= md5(preg_replace('#^https?://#', '', $this->racine) . $comId) ?></guid>
			<description><?= plxUtils::strCheck($this->plxRecord_coms->f('content'), true) ?></description>
			<pubDate><?= plxDate::dateIso2rfc822($this->plxRecord_coms->f('date')) ?></pubDate>
			<dc:creator><?= plxUtils::strCheck($this->plxRecord_coms->f('author')) ?></dc:creator>
<?php
				# Hook plugins
				eval($this->plxPlugins->callHook('plxFeedAdminCommentsXml'));
?>
		</item>
<?php
			}
		}

		$this->printRSSBottom();
	}
}

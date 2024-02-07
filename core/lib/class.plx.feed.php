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
		$this->rssLastBuildDate = '';
		$this->clef = $this->aConf['clef'];

		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedConstruct'));
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

		if($this->get AND preg_match('#^(?:atom/|rss/)?categorie(\d+)/?#',$this->get,$capture)) {
			$this->mode = 'categorie'; # Mode du flux
			# On récupère la catégorie cible
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complète sur 3 caractères
			# On modifie le motif de recherche
			$this->motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*(?:'.$this->cible.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
		}
		elseif($this->get AND preg_match('#^(?:atom/|rss/)?user(\d+)/?#',$this->get,$capture)) {
			$this->mode = 'user'; # Mode du flux
			# On récupère l'id de l'utilisateur
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complète sur 3 caractères
			# On modifie le motif de recherche
			$this->motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*(?:home|\d{3})(?:,\d{3})*\.' . $this->cible . '\.\d{12}\.[\w-]+\.xml$#';
		}
		elseif($this->get AND preg_match('#^(?:atom/|rss/)?commentaires/?$#',$this->get)) {
			$this->mode = 'commentaire'; # Mode du flux
		}
		elseif($this->get AND preg_match('#^(?:atom/|rss/)?tag/([\w-]+)/?$#', $this->get, $capture)) {
			$this->mode = 'tag';
			$this->cible = $capture[1];
			$ids = array();
			$datetime = date('YmdHi');
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
				# Notice 000 and home are always in activeCats
				$this->motif = '#('.implode('|', $ids).')\.(?:pin,|home,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
			} else
				$this->motif = '';

		}
		elseif($this->get AND preg_match('#^(?:atom/|rss/)?commentaires/article(\d+)/?$#',$this->get,$capture)) {
			$this->mode = 'commentaire'; # Mode du flux
			# On récupère l'article cible
			$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT); # On complète sur 4 caractères
			# On modifie le motif de recherche
			$this->motif = '#^'.$this->cible.'\.(?:pin,|home,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
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
			$this->motif = '#^\d{4}\.(?:pin,|home,|\d{3},)*(?:'.$this->activeCats.')(?:,\d{3})*\.\d{3}\.\d{12}\.[\w-]+\.xml$#';
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
			if(!empty($this->cible)) {
				# Flux de commentaires d'un article précis
				if(!$this->getArticles()) { # Aucun article, on redirige
					$this->cible = $this->cible + 0;
					header('Location: ' . $this->urlRewrite('?article' . $this->cible . '/'));
					exit;
				} else {
					# On récupère les commentaires de l'article
					$regex = '#^' . $this->cible . '.\d{10}-\d+\.xml$#';
				}
			} else {
				# Flux de commentaires global
				$regex = '#^\d{4}\.\d{10}-\d+\.xml$#';
			}
			$this->getCommentaires($regex, 'rsort', 0, $this->bypage);
		} elseif($this->mode == 'admin') {
			# Flux admin
			if(empty($this->clef)) { # Clef non initialisée
				header('Content-Type: text/plain; charset='.PLX_CHARSET);
				echo L_FEED_NO_PRIVATE_URL;
				exit;
			}

			# On recherche le dernier commentaire soumis
			$aFiles = $this->plxGlob_coms->query('#^_?\d{4}\.\d{10}-\d+\.xml$#','com', 'rsort', 0, 1, 'all');
			if($aFiles) {
				$array = array();
				foreach($aFiles as $k=>$v) {
					$array[$k] = $this->parseCommentaire(PLX_ROOT.$this->aConf['racine_commentaires'].$v);
				}
				$this->rssLastBuildDate = $array[0]['date'];
				unset($array);
			}

			# On récupère les commentaires
			$this->getCommentaires('#^' . $this->cible . '\d{4}\.\d{10}-\d+\.xml$#', 'rsort', 0, false, 'all');
		} else {
			# Flux des articles d'une catégorie ou d'un utilisateur précis
			if($this->cible) {
				switch($this->mode) {
					case 'categorie':
						# On vérifie que la catégorie existe et est active
						if(!isset($this->aCats[$this->cible]) OR !$this->aCats[$this->cible]['active']) {
							# Echec, on redirige
							header('Location: '.$this->urlRewrite('?categorie'.intval($this->cible).'/'));
							exit;
						}
						break;
					case 'tag':
						# Flux d'articles pour un tag
						if(empty($this->motif)) {
							header('Location: '.$this->urlRewrite('?tag/'.$this->cible.'/'));
							exit;
						}
						break;
					case 'user':
						# On vérifie que la catégorie existe et est active
						if(!isset($this->aUsers[$this->cible]) OR !$this->aUsers[$this->cible]['active']) {
							# Echec, on redirige
							header('Location: '.$this->urlRewrite('?user'.intval($this->cible).'/'));
							exit;
						}
						break;
					default:
						header('Location: index.php');
						exit;
				}
			}
			$this->getArticles(); # Récupération des articles (on les parse)
		}

		# Selon le mode, on appelle la méthode adéquate
		switch($this->mode) {
			case 'tag':
			case 'user':
			case 'categorie':
			case 'article' : $this->getRssArticles(); break;
			case 'commentaire' : $this->getRssComments(); break;
			case 'admin' : $this->getAdminComments(); break;
			default : break;
		}

		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedDemarrageEnd'));

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
		$this->rssAttachment = 'articles.rss';
		switch($this->mode) {
			case 'tag':
				$this->rssTitle = $this->aConf['title'].' - '.L_PAGETITLE_TAG.' '.$this->cible;
				$this->rssLink = $this->urlRewrite('?tag/'.$this->cible);
				$this->rssAttachment = 'tag-' . $this->cible . '.rss';
				break;
			case 'categorie':
				$this->rssTitle = $this->aConf['title'].' - '.$this->aCats[ $this->cible ]['name'];
				$this->rssLink = $this->urlRewrite('?categorie'.intval($this->cible));
				$this->rssAttachment = 'categorie-' . ltrim($this->cible, '0') . '-' . $this->aCats[ $this->cible ]['name'] . '.rss';
				break;
			case 'user':
				$this->rssTitle = $this->aConf['title'].' - '.$this->aUsers[ $this->cible ]['name'];
				$this->rssLink = $this->urlRewrite('?user'.intval($this->cible));
				$this->rssAttachment = 'user-' . $this->aUsers[ $this->cible ]['name'] . '.rss';
				break;
			default:
		}

		# On va boucler sur les articles si possible
		if($this->plxRecord_arts) {
			$this->rssLastBuildDate = $this->plxRecord_arts->lastUpdated('date_update');
			# On affiche l'entête xml du flux
			$this->getRssXml();
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
				$content .= $this->aConf['feed_footer'];
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
			<link><?= $this->urlRewrite('?article' . $artId . '/' . $this->plxRecord_arts->f('url')) ?></link>
			<guid><?= $this->racine . 'index.php?article' . $artId ?></guid>
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
		else $this->getRssXml();# On affiche l'entête xml du flux

		# On affiche la fin xml du flux
		$this->getRssXml('foot');
	}

	/**
	 * Méthode qui affiche le flux rss des commentaires du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Amaury GRAILLAT
	 **/
	public function getRssComments() {

		# Traitement initial
		$last_updated = '197001010100';
		$entry_link = '';
		$entry = '';
		if($this->cible) { # Commentaires d'un article
			$artId = $this->plxRecord_arts->f('numero') + 0;
			$this->rssTitle = $this->aConf['title'].' - '.$this->plxRecord_arts->f('title').' - '.L_FEED_COMMENTS;
			$this->rssLink = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url'));
			$this->rssAttachment = 'comments-' . $artId . '.rss';
		} else { # Commentaires globaux
			$this->rssTitle = $this->aConf['title'].' - '.L_FEED_COMMENTS;
			$this->rssLink = $this->urlRewrite();
			$this->rssAttachment = 'comments.rss';
		}

		# On va boucler sur les commentaires (s'il y en a)
		if($this->plxRecord_coms) {
			$this->rssLastBuildDate = $this->plxRecord_coms->lastUpdated();
			# On affiche l'entête xml du flux
			$this->getRssXml();
			while($this->plxRecord_coms->loop()) {
				# Traitement initial
				if(isset($this->activeArts[$this->plxRecord_coms->f('article')])) {
					$artId = $this->plxRecord_coms->f('article') + 0;
					$comId = 'c'.$this->plxRecord_coms->f('article').'-'.$this->plxRecord_coms->f('index');
					if($this->cible) { # Commentaires d'un article
						$title_com = $this->plxRecord_arts->f('title').' - ';
						$title_com .= L_FEED_WRITTEN_BY.' '.$this->plxRecord_coms->f('author').' @ ';
						$title_com .= plxDate::formatDate($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
						$link_com = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url').'#'.$comId);
					} else { # Commentaires globaux
						$title_com = $this->plxRecord_coms->f('author').' @ ';
						$title_com .= plxDate::formatDate($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
						$artInfo = $this->artInfoFromFilename($this->plxGlob_arts->aFiles[$this->plxRecord_coms->f('article')]);
						$link_com = $this->urlRewrite('?article'.$artId.'/'.$artInfo['artUrl'].'#'.$comId);
					}
					# On vérifie la date de publication
					if($this->plxRecord_coms->f('date') > $last_updated)
						$last_updated = $this->plxRecord_coms->f('date');

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
		else $this->getRssXml();# On affiche l'entête xml du flux

		# On affiche la fin xml du flux
		$this->getRssXml('foot');
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

		# On va boucler sur les commentaires (s'il y en a)
		if($this->plxRecord_coms) {
			$this->rssLastBuildDate = $this->plxRecord_coms->lastUpdated();
			# On affiche l'entête xml du flux
			$this->getRssXml();
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
		else $this->getRssXml();# On affiche l'entête xml du flux
		# On affiche la fin xml du flux
		$this->getRssXml('foot');
	}
	/**
	 * Méthode qui affiche le debut (entête) ou la fin du xml
	 *
	 * @param	$who	string	'head' ou 'foot'
	 * @scope	getRss[Articles|AdminComments|Comments]
	 * @return	flux sur stdout
	 * @author	Thomas I. @sudwebdesin
	 **/
	public function getRssXml($who = 'head') {
		# On affiche l'entête
		if($who == 'head'):

			if(empty($this->lastBuildDate))
				$this->lastBuildDate = date('YmdHi');

?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="<?= $this->urlRewrite('feed.php?' . $this->get) ?>" />
		<title><?= plxUtils::strCheck($this->rssTitle) ?></title>
		<link><?= $this->rssLink ?></link>
		<lastBuildDate><?= plxDate::dateIso2rfc822($this->rssLastBuildDate) ?></lastBuildDate>
		<language><?= $this->aConf['default_lang'] ?></language>
		<description><?= plxUtils::strCheck($this->aConf['description']) ?></description>
		<generator>PluXml</generator>
<?php
			# Hook plugins
			eval($this->plxPlugins->callHook('plxFeedGetRssXmlHead'));
		elseif($who == 'foot'):
			# Hook plugins
			eval($this->plxPlugins->callHook('plxFeedGetRssXmlFoot'));
?>
	</channel>
</rss>
<?php
		endif;
	}
}

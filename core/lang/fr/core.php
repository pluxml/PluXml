<?php

$LANG = array(

'L_LANGUE'						=> 'Français',

# common
'L_PLUXML_VERSION'				=> 'Pluxml version',
'L_HOMEPAGE'					=> 'Accueil',
'L_UNCLASSIFIED'				=> 'Non classé',
'L_INFO_PHP_VERSION'			=> 'Version de php',
'L_INFO_MAGIC_QUOTES'			=> 'Etat des "magic quotes"',
'L_INFO_CHARSET'				=> 'encodage',

# index.php
'L_ERR_THEME_NOTFOUND'			=> 'Le theme principal de PluXml est introuvable',
'L_ERR_FILE_NOTFOUND'			=> 'Le fichier cible de PluXml est introuvable',
'L_ERR_PAGE_NOT_FOUND'			=> 'Page non trouvée',

# class.plx.date.php
'L_JANUARY'						=> 'janvier',
'L_FEBRUARY'					=> 'février',
'L_MARCH'						=> 'mars',
'L_APRIL'						=> 'avril',
'L_MAY'							=> 'mai',
'L_JUNE'						=> 'juin',
'L_JULY'						=> 'juillet',
'L_AUGUST'						=> 'août',
'L_SEPTEMBER'					=> 'septembre',
'L_OCTOBER'						=> 'octobre',
'L_NOVEMBER'					=> 'novembre',
'L_DECEMBER'					=> 'décembre',
'L_MONDAY'						=> 'lundi',
'L_TUESDAY'						=> 'mardi',
'L_WEDNESDAY'					=> 'mercredi',
'L_THURSDAY'					=> 'jeudi',
'L_FRIDAY'						=> 'vendredi',
'L_SATURDAY'					=> 'samedi',
'L_SUNDAY'						=> 'dimanche',

# class.plx.capcha.php
'L_LAST'						=> 'dernière',
'L_FIRST'						=> 'première',
'L_SECOND'						=> 'deuxième',
'L_THIRD'						=> 'troisième',
'L_FOURTH'						=> 'quatrième',
'L_FIFTH'						=> 'cinquième',
'L_SIXTH'						=> 'sizième',
'L_SEVENTH'						=> 'septième',
'L_EIGTH'						=> 'huitième',
'L_NINTH'						=> 'neuvième',
'L_TENTH'						=> 'dixième',
'L_NTH'							=> 'ème',
'L_CAPCHA_QUESTION'				=> 'Quelle est la <span class="capcha-letter">%s</span> lettre du mot <span class="capcha-word">%s</span> ?',

# class.plx.utils.php
'L_WRITE_ACCESS'				=> '%s est accessible en écriture',
'L_WRITE_NOT_ACCESS'			=> '%s n\'est pas accessible en écriture ou n\'existe pas',
'L_MODREWRITE_AVAILABLE'		=> 'Module apache de réécriture d\'url mod_rewrite disponible',
'L_MODREWRITE_NOT_AVAILABLE'	=> 'Module apache de réécriture d\'url mod_rewrite non disponible',
'L_LIBGD_INSTALLED'				=> 'Bibliothèque GD installée',
'L_LIBGD_NOT_INSTALLED'			=> 'Bibliothèque GD non installée',
'L_MAIL_AVAILABLE'				=> 'Fonction d\'envoi de mail disponible',
'L_MAIL_NOT_AVAILABLE'			=> 'Fonction d\'envoi de mail non disponible',

# class.plx.motor.php
'L_FILE_VERSION_REQUIRED'		=> 'Le fichier "%sversion" est necessaire au fonctionnement de PluXml',
'L_ARTICLE_NO_TAG'				=> 'Aucun article pour ce mot clé !',
'L_UNKNOWN_CATEGORY'			=> 'Cette catégorie est inexistante !',
'L_NO_ARTICLE_PAGE'				=> 'Aucun article pour cette page !',
'L_UNKNOWN_ARTICLE'				=> 'Cet article n\'existe pas ou n\'existe plus !',
'L_COM_IN_MODERATION'			=> 'Le commentaire est en cours de modération par l\'administrateur de ce site',
'L_UNKNOWN_STATIC'				=> 'Cette page n\'existe pas ou n\'existe plus !',
'L_DOCUMENT_NOT_FOUND'			=> 'Le document spécifié est introuvable',
'L_NEWCOMMENT_ERR'				=> 'Une erreur s\'est produite lors de la publication de ce commentaire',
'L_NEWCOMMENT_FIELDS_REQUIRED'	=> 'Merci de remplir tous les champs obligatoires requis',
'L_NEWCOMMENT_ERR_ANTISPAM'		=> 'La vérification anti-spam a échoué',

# class.plx.show.php

'L_HTTPENCODING'				=> 'Compression %s activée',
'L_PAGETITLE_ARCHIVES'			=> 'Archives',
'L_PAGETITLE_TAG'				=> 'Tag',
'L_NO_CATEGORY'					=> 'aucune catégorie',
'L_CATEGORY'					=> 'catégorie',
'L_CATEGORIES'					=> 'catégories',
'L_NO_ARTICLE'					=> 'aucun article',
'L_ARTICLE'						=> 'article',
'L_ARTICLES'					=> 'articles',
'L_ARTAUTHOR_UNKNOWN'			=> 'inconnu',
'L_ARTTAGS_NONE'				=> 'aucun',
'L_ARTCHAPO'					=> 'Lire la suite de #art_title',
'L_ARTFEED_RSS_CATEGORY'		=> 'Fil Rss des articles de cette catégorie',
'L_ARTFEED_RSS_TAG'				=> 'Fil Rss des articles de ce mot clé',
'L_ARTFEED_RSS'					=> 'Fil Rss des articles',
'L_NO_COMMENT'					=> 'aucun commentaire',
'L_COMMENT'						=> 'commentaire',
'L_COMMENTS'					=> 'commentaires',
'L_COMFEED_RSS_ARTICLE'			=> 'Fil Rss des commentaires de cet article',
'L_COMFEED_RSS'					=> 'Fil Rss des commentaires',
'L_STATICCONTENT_INPROCESS'		=> 'Cette page est actuellement en cours de rédaction',

'L_PAGINATION_FIRST_TITLE'		=> 'Aller à la première page',
'L_PAGINATION_FIRST'			=> '«',
'L_PAGINATION_PREVIOUS_TITLE'	=> 'Page précédente',
'L_PAGINATION_PREVIOUS'			=> 'précédente',
'L_PAGINATION_NEXT_TITLE'		=> 'page suivante',
'L_PAGINATION_NEXT'				=> 'suivante',
'L_PAGINATION_LAST_TITLE'		=> 'Aller à la dernière page',
'L_PAGINATION_LAST'				=> '»',
'L_PAGINATION'					=> 'page %s sur %s',

'L_PAGEBLOG_TITLE'				=> 'Blog',

# class.plx.feed.php
'L_FEED_NO_PRIVATE_URL'			=> 'Les URLs privees n\'ont pas ete initialisees dans vos parametres d\'administration !',
'L_FEED_COMMENTS'				=> 'Commentaires',
'L_FEED_ONLINE_COMMENTS'		=> 'Commentaires en ligne',
'L_FEED_OFFLINE_COMMENTS'		=> 'Commentaires hors ligne',
'L_FEED_WRITTEN_BY'				=> 'Rédigé par',

);
?>
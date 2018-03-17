<?php

$LANG = array(

'L_LANGUE'					=> 'Deutsch',

'L_DATE_CREATION'			=> 'Erstellungsdatum',
'L_DATE_UPDATE'				=> 'Datum der Aktualisierung',

# common
'L_PLUXML_VERSION'			=> 'Pluxml Version',
'L_HOMEPAGE'				=> 'Homepage',
'L_UNCLASSIFIED'			=> 'Nicht kategorisiert',
'L_INFO_PHP_VERSION'		=> 'PHP Version',
'L_INFO_MAGIC_QUOTES'		=> '"magic quotes" Status',
'L_INFO_CHARSET'			=> 'Kodierung',

# index.php
'L_ERR_THEME_NOTFOUND'		=> 'Der Haupttheme von PluXml konnte nicht gefunden werden.',
'L_ERR_FILE_NOTFOUND'		=> 'Die angeforderte Datei konnte von PluXml nicht gefunden werden.',
'L_ERR_PAGE_NOT_FOUND'		=> 'Seite nicht gefunden',

# class.plx.date.php
'L_SHORT_JANUARY'			=> 'jan',
'L_SHORT_FEBRUARY'			=> 'feb',
'L_SHORT_MARCH'				=> 'märz',
'L_SHORT_APRIL'				=> 'apr',
'L_SHORT_MAY'				=> 'mai',
'L_SHORT_JUNE'				=> 'juni',
'L_SHORT_JULY'				=> 'juli',
'L_SHORT_AUGUST'			=> 'aug',
'L_SHORT_SEPTEMBER'			=> 'sept',
'L_SHORT_OCTOBER'			=> 'okt',
'L_SHORT_NOVEMBER'			=> 'nov',
'L_SHORT_DECEMBER'			=> 'dez',
'L_JANUARY'					=> 'Januar',
'L_FEBRUARY'				=> 'Februar',
'L_MARCH'					=> 'März',
'L_APRIL'					=> 'April',
'L_MAY'						=> 'Mai',
'L_JUNE'					=> 'Juni',
'L_JULY'					=> 'Juli',
'L_AUGUST'					=> 'August',
'L_SEPTEMBER'				=> 'September',
'L_OCTOBER'					=> 'Oktober',
'L_NOVEMBER'				=> 'November',
'L_DECEMBER'				=> 'Dezember',
'L_MONDAY'					=> 'Montag',
'L_TUESDAY'					=> 'Dienstag',
'L_WEDNESDAY'				=> 'Mittwoch',
'L_THURSDAY'				=> 'Donnerstag',
'L_FRIDAY'					=> 'Freitag',
'L_SATURDAY'				=> 'Samstag',
'L_SUNDAY'					=> 'Sonntag',

# class.plx.capcha.php
'L_LAST'					=> 'letzte',
'L_FIRST'					=> 'erste',
'L_SECOND'					=> 'zweiste',
'L_THIRD'					=> 'dritte',
'L_FOURTH'					=> 'vierte',
'L_FIFTH'					=> 'fünfte',
'L_SIXTH'					=> 'sechste',
'L_SEVENTH'					=> 'siebte',
'L_EIGTH'					=> 'achte',
'L_NINTH'					=> 'neunte',
'L_TENTH'					=> 'zehnte',
'L_NTH'						=> 'te',
'L_CAPCHA_QUESTION'			=> 'Was ist der <span class="capcha-letter">%s</span> Buchstabe des Wortes <span class="capcha-word">%s</span> ?',

# class.plx.utils.php
'L_WRITE_ACCESS'			=> '%s ist schreibbar',
'L_WRITE_NOT_ACCESS'		=> '%s ist nicht schreibbar',
'L_MODREWRITE_AVAILABLE'	=> 'mod_rewrite APACHE-modul ist verfügbar',
'L_MODREWRITE_NOT_AVAILABLE'=> 'mod_rewrite APACHE-Modul ist nicht verfügbar',
'L_LIBGD_INSTALLED'			=> 'GD Grafikbibliothek ist verfügbar',
'L_LIBGD_NOT_INSTALLED'		=> 'GD Grafikbibliothek ist nicht verfügbar',
'L_LIBXML_INSTALLED'		=> 'XML Grafikbibliothek ist verfügbar',
'L_LIBXML_NOT_INSTALLED'	=> 'XML Grafikbibliothek ist nicht verfügbar',
'L_MAIL_AVAILABLE'			=> 'E-Mail versenden ist möglich',
'L_MAIL_NOT_AVAILABLE'		=> 'E-Mail versenden ist nicht möglich',

# class.plx.motor.php
'L_ARTICLE_NO_TAG'			=> 'Es gibt keinen Artikel für dieses Schlagwort!',
'L_UNKNOWN_CATEGORY'		=> 'Diese Kategorie existiert nicht!',
'L_NO_ARTICLE_PAGE'			=> 'Es gibt keinen Artikel für diese Seite!',
'L_UNKNOWN_ARTICLE'			=> 'Dieser Artikel existiert nicht oder nicht mehr!',
'L_COM_PUBLISHED'			=> 'Der Kommentar ist veröffentlicht',
'L_COM_IN_MODERATION'		=> 'Der Kommentar muss erst vom Administrator moderiert werden.',
'L_UNKNOWN_STATIC'			=> 'Diese Seite existiert nicht oder nicht mehr!',
'L_DOCUMENT_NOT_FOUND'		=> 'Das angeforderte Dokument kann nicht gefunden werden.',
'L_NEWCOMMENT_ERR'			=> 'Es gab einen Fehler beim Anlegen dieses Kommentars.',
'L_NEWCOMMENT_FIELDS_REQUIRED' => 'Sie müssen alle Pflichtfelder ausfüllen',
'L_NEWCOMMENT_ERR_ANTISPAM'	=> 'Anti-SPAM Überprüfung fehlgeschlagen',

# class.plx.show.php

'L_HTTPENCODING'			=> 'Datenkompression %s aktiviert',
'L_PAGETITLE_ARCHIVES'		=> 'Archive',
'L_PAGETITLE_TAG'			=> 'Tag',
'L_NO_CATEGORY'				=> 'keine Kategorie',
'L_CATEGORY'				=> 'Kategorie',
'L_CATEGORIES'				=> 'Kategorien',
'L_NO_ARTICLE'				=> 'keine Artikel',
'L_ARTICLE'					=> 'Artikel',
'L_ARTICLES'				=> 'Artikel',
'L_ARTAUTHOR_UNKNOWN'		=> 'unbekannt',
'L_ARTTAGS_NONE'			=> 'kein',
'L_ARTCHAPO'				=> 'Den Artikel #art_title lesen',
'L_ARTFEED_RSS_CATEGORY'	=> 'Artikel-Feed (RSS) dieser Kategorie',
'L_ARTFEED_RSS_TAG'			=> 'Artikel-Feed (RSS) dieser Tag',
'L_ARTFEED_RSS'				=> 'Artikel-Feed (RSS)',
'L_NO_COMMENT'				=> 'keine Kommentare',
'L_COMMENT'					=> 'Kommentar',
'L_COMMENTS'				=> 'Kommentare',
'L_COMFEED_RSS_ARTICLE'		=> 'Kommentare-Feed (RSS) dieses Artikels',
'L_COMFEED_RSS'				=> 'Kommentare-Feed (RSS)',
'L_STATICCONTENT_INPROCESS'	=> 'Diese Seite wird gerade bearbeitet',
'L_SAID'					=> 'sagte',

'L_PAGINATION_FIRST_TITLE'	=> 'Zur ersten Seite',
'L_PAGINATION_FIRST'		=> '«',
'L_PAGINATION_PREVIOUS_TITLE' => 'Letzte Seite',
'L_PAGINATION_PREVIOUS'		=> 'Letzte',
'L_PAGINATION_NEXT_TITLE'	=> 'Nächste Seite',
'L_PAGINATION_NEXT'			=> 'Nächste',
'L_PAGINATION_LAST_TITLE'	=> 'Zur letzten Seite',
'L_PAGINATION_LAST'			=> '»',
'L_PAGINATION'				=> 'Seite %s auf %s',

'L_PAGEBLOG_TITLE'			=> 'Blog',

'L_YEAR'					=> 'jahr',
'L_TOTAL'					=> 'gesamt',

# class.plx.feed.php
'L_FEED_NO_PRIVATE_URL'		=> 'Private URLs wurden in der Administration nicht konfiguriert!',
'L_FEED_COMMENTS'			=> 'Kommentare',
'L_FEED_ONLINE_COMMENTS'	=> 'Online Kommentare',
'L_FEED_OFFLINE_COMMENTS'	=> 'Offline Kommentaire',
'L_FEED_WRITTEN_BY'			=> 'Geschrieben von',

);
?>

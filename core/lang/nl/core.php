<?php

const L_LANGUE = 'Nederlands';
const L_DATE_CREATION = 'Aanmaakdatum';
const L_DATE_UPDATE = 'Geactualiseerd';

# common

const L_PLUXML_VERSION = 'PluXml versie';
const L_PLUXML_VERSION_DATA = 'Versie datums';
const L_HOMEPAGE = 'Onthaal';
const L_UNCLASSIFIED = 'Niet geclassificeerd';
const L_INFO_PHP_VERSION = 'PHP-versie';
const L_INFO_CHARSET = 'codering';
const L_PAGE = 'Pagina';
const L_SAVE_SUCCESSFUL = 'Gegevens succesvol opgeslagen';
const L_PASSWORD						= 'Wachtwoord';
const L_CONFIRM_PASSWORD			= 'Bevestig het wachtwoord';
const L_ERR_CONFIRM_PASSWORD		= 'Bevestiging van wachtwoord mislukt !';
const L_ERR_MISSING_PASSWORD			= 'Vul een wachtwoord in !';
const L_PWD_VERY_WEAK					= 'Zeer zwak wachtwoord';
const L_PWD_WEAK						= 'Zwak wachtwoord';
const L_PWD_GOOD						= 'Goed wachtwoord';
const L_PWD_STRONG						= 'Sterk wachtwoord';

# index.php

const L_ERR_THEME_NOTFOUND = 'De standaardlayout van PluXml werd niet gevonden';
const L_ERR_FILE_NOTFOUND = 'Het volgende bestand ontbreekt';
const L_ERR_PAGE_NOT_FOUND = 'Pagina niet gevonden';

# class.plx.date.php

const L_SHORT_JANUARY = 'jan';
const L_SHORT_FEBRUARY = 'feb';
const L_SHORT_MARCH = 'maart';
const L_SHORT_APRIL = 'apr';
const L_SHORT_MAY = 'mei';
const L_SHORT_JUNE = 'juni';
const L_SHORT_JULY = 'juli';
const L_SHORT_AUGUST = 'aug';
const L_SHORT_SEPTEMBER = 'sept';
const L_SHORT_OCTOBER = 'okt';
const L_SHORT_NOVEMBER = 'nov';
const L_SHORT_DECEMBER = 'dec';
const L_JANUARY = 'januari';
const L_FEBRUARY = 'februari';
const L_MARCH = 'maart';
const L_APRIL = 'april';
const L_MAY = 'mei';
const L_JUNE = 'juni';
const L_JULY = 'juli';
const L_AUGUST = 'augustus';
const L_SEPTEMBER = 'september';
const L_OCTOBER = 'oktober';
const L_NOVEMBER = 'november';
const L_DECEMBER = 'december';
const L_MONDAY = 'maandag';
const L_TUESDAY = 'dinsdag';
const L_WEDNESDAY = 'woensdag';
const L_THURSDAY = 'donderdag';
const L_FRIDAY = 'vrijdag';
const L_SATURDAY = 'zaterdag';
const L_SUNDAY = 'zondag';

# class.plx.capcha.php

const L_LAST = 'laatste';
const L_FIRST = 'eerste';
const L_SECOND = 'tweede';
const L_THIRD = 'derde';
const L_FOURTH = 'vierde';
const L_FIFTH = 'vijfde';
const L_SIXTH = 'zesde';
const L_SEVENTH = 'zevende';
const L_EIGTH = 'achste';
const L_NINTH = 'negende';
const L_TENTH = 'tiende';
const L_NTH = 'de';
const L_CAPCHA_QUESTION = 'Welke is de <span class="capcha-letter">%s</span> aard van het woord <span class="capcha-word">%s</span> ?';

# class.plx.utils.php

const L_WRITE_ACCESS = '%s is toegankelijk met schrijfrechten';
const L_WRITE_NOT_ACCESS = '%s is niet toegankelijk met schrijfrechten of bestaat niet';
const L_MODREWRITE_AVAILABLE = 'Apache module mod_rewrite voor het herschrijven van URLs is beschikbaar';
const L_MODREWRITE_NOT_AVAILABLE = 'Apache module mod_rewrite voor het herschrijven van URLs is niet beschikbaar';
const L_LIBGD_INSTALLED = 'GD-bibliotheek is geïnstalleerd';
const L_LIBGD_NOT_INSTALLED = 'GD-bibliotheek is niet geïnstalleerd of beschikbaar';
const L_LIBXML_INSTALLED = 'XML-bibliotheek is geïnstalleerd';
const L_LIBXML_NOT_INSTALLED = 'XML-bibliotheek is niet geïnstalleerd of beschikbaar';
const L_MAIL_AVAILABLE = 'Email verzendfunctie is beschikbaar';
const L_MAIL_NOT_AVAILABLE = 'Email verzendfunctie is niet beschikbaar';

# class.plx.motor.php

const L_ARTICLE_NO_TAG = 'Geen artikel gevonden voor dit sleutelwoord !';
const L_UNKNOWN_CATEGORY = 'Deze categorie bestaat niet !';
const L_NO_ARTICLE_PAGE = 'Geen artikel gevonden voor deze pagina !';
const L_UNKNOWN_ARTICLE = 'Dit artikel bestaat niet of niet meer !';
const L_COM_PUBLISHED = 'De opmerking is gepubliceerd';
const L_COM_IN_MODERATION = 'De commentaar wordt momenteel gemodereerd door de beheerder van deze site';
const L_UNKNOWN_STATIC = 'Deze pagina bestaat niet of niet meer !';
const L_DOCUMENT_NOT_FOUND = 'Het opgevraagde document is onvindbaar';
const L_NEWCOMMENT_ERR = 'Er is een fout opgetreden bij het wegschrijven van de commentaar';
const L_NEWCOMMENT_FIELDS_REQUIRED = 'Gelieve alle verplichte velden in te vullen a.u.b.';
const L_NEWCOMMENT_ERR_ANTISPAM = 'La vérification anti-spam a échoué';
const L_UNKNOWN_AUTHOR = 'Onbekende auteur';
const L_NEWCOMMENT_ERR_LOGIN = 'Foute login of wachtwoord';

# class.plx.show.php

const L_HTTPENCODING = '%s compressie is geactiveerd';
const L_PAGETITLE_ARCHIVES = 'Archieven';
const L_PAGETITLE_TAG = 'Tag';
const L_NO_CATEGORY = 'geen categorie';
const L_CATEGORY = 'categorie';
const L_CATEGORIES = 'categorieën';
const L_NO_ARTICLE = 'geen artikel';
const L_ARTICLE = 'artikel';
const L_ARTICLES = 'artikels';
const L_ARTAUTHOR_UNKNOWN = 'onbekend';
const L_ARTTAGS_NONE = 'geen';
const L_ARTCHAPO = 'Lees het vervolg #art_title';
const L_ARTFEED_RSS_CATEGORY = 'RSS-feed van artikelen uit categorie %s';
const L_ARTFEED_RSS_USER = 'RSS-feed van artikelen uit categorie %s';
const L_ARTFEED_RSS_TAG = 'Artikelen RSS-feed voor tag %s';
const L_ARTFEED_RSS = 'Rss-feed van de artikels';
const L_NO_COMMENT = 'geen commentaar';
const L_COMMENT = 'commentaar';
const L_COMMENTS = 'commentaren';
const L_FORBIDDEN_COMMENTS = 'Verboden commentaren';
const L_COMFEED_RSS_ARTICLE = 'Rss-feed van de commentaren van dit artikel';
const L_COMFEED_RSS = 'Rss-feed van de commentaren';
const L_STATICCONTENT_INPROCESS = 'Deze pagine wordt momenteel bijgewerkt';
const L_SAID = 'zei';
const L_PAGINATION_FIRST_TITLE = 'Ga naar de eerste pagina';
const L_PAGINATION_FIRST = '«';
const L_PAGINATION_PREVIOUS_TITLE = 'Vorige pagina';
const L_PAGINATION_PREVIOUS = 'vorige';
const L_PAGINATION_NEXT_TITLE = 'Volgende pagina';
const L_PAGINATION_NEXT = 'volgende';
const L_PAGINATION_LAST_TITLE = 'Ga naar de laatste pagina';
const L_PAGINATION_LAST = '»';
const L_PAGINATION = 'pagina %s in %s';
const L_PAGEBLOG_TITLE = 'Blog';
const L_YEAR = 'jaar';
const L_TOTAL = 'totaal';

# class.plx.feed.php

const L_FEED_NO_PRIVATE_URL = 'Privé URLs zijn niet geïnitialiseerd in uw instellingen !';
const L_FEED_COMMENTS = 'Commentaren';
const L_FEED_ONLINE_COMMENTS = 'Online commentaren';
const L_FEED_OFFLINE_COMMENTS = 'Offline commentaren';
const L_FEED_WRITTEN_BY = 'Geschreven door';

# auth.php

const L_AUTH_LOGIN_FIELD = 'Uw Login';
const L_AUTH_PASSWORD_FIELD = 'Uw Paswoord';
const L_LOST_PASSWORD = 'Wachtwoord vergeten?';

# for urls - must be urlify !

const L_ARTICLE_URL = 'artikel';
const L_STATIC_URL = 'static';
const L_CATEGORY_URL = 'categorie';
const L_USER_URL = 'schrijver';
const L_TAG_URL = 'tag';
const L_ARCHIVES_URL = 'archieven';
const L_BLOG_URL = 'blog';
const L_COMMENTS_URL = 'opmerkingen';
const L_PAGE_URL = 'pagina';
const L_DOWNLOAD_URL = 'downloaden';


# Changelog

## 5.9.0 - 2024/xx/xx

### Features
- List all articles of a same author (#585) (bazooka07)
- Pinned articles (#625) (#628) (bazooka07)
- Management of subscribers and handle comments only for them (#637) (bazooka07)
- Number of articles to display on the homepage (#659) (bazooka07)
- Choose from which user profil medias files are independent (#643) (bazooka07)
- Backoffice action to generate a robots.txt (#647) (bazooka07)
- Minors CSS changes to the backoffice theme on login page and on table headers (#600) (bazooka07)

### Default theme
- New user (comments) and RSS icons (Haruka)
- Add RSS icons to the sidebar (Haruka)
- Use h2 instead of h3 in the comment section (Haruka)
- Handle comments availability for subscribers only (#636) (Bazooka)
- Scroll to top icon (#658) (bazooka07)

### plxShow
- plxShow::artPinClass() new function to add a CSS class on pinned articles (#628) (bazooka07)
- plxShow::meta_all() new function to add meta description, meta keywords and meta author (#657) (bazooka07)
- plxShow::pageUrl() new function to get the article or page canonical url (#654) (bazooka07)
- plxShow::staticList() can show or hide (by default) the home link on the homepage (#675) (bazooka07)
- plxShow::pagination() display icons instead of textual links (#660) (bazooka07)
- plxShow::catName() use return instead of echo (#478) (#596) (bazooka07)
- plxShow::meta() can choose between echo or return the result (#484) (#582) (Philippe-M, bazooka07)
- plxShow::articleAllowComs() check if comments ar allowed for the article (#629) (bazooka07)

### Bugfix
- Vulnerability fix in plxAdmin::editConfiguration() for PLX_CONFIG_PATH (#321) (#566) (bazooka07)
- Occitan translation (#567) (#623) (#664) (#674) (ensag-dev, Mejans)
- Translations (#641) (bazooka07)
- Switch to the default theme if the selected one does not exist or do not have the selected language (#597) (#601) (bazooka07)
- Administration : check if there are articles before displaying artices list (#602) (bazooka07)
- Error while displaying changed password success message on lostpassword page (Haruka)
- Static page on home (#673) (bazooka07)
- Files download (#65)2 (bazooka07)
- Typo in plxDate::checkDate() (#651) (bazooka07)
- Cleanly delete plugins files (#605) (#627) (bazooka07)
- Urlify authors names #621 (bazooka07)
- Check PluXml update availibility when asking for the RSS feed #614 (bazooka07)
- Allow some HTML5 entities for plxAdmin::editConfiguration() and plxUtils::strCheck() #603 (bazooka07)
- Check missing language for a plugin (#368) (#594) (bazooka07)
- Remove the php version in the XMailer attribute (#553) (#580) (tharosd, bazooka07)
- plxShow::staticInclude() do not work as expected #575 (bazooka07)

### Other
- php 8.1 compatibility and improvements to plxUtils::makeThumb (#661) (sudwebdesign)
- php 8.2 compatibility in plxUtils _printSelectDir: Warning: Cannot modify header information (#671) (FrancoisThareau)
- php 8.2 compatibility in plxMotor: Creation of dynamic property plxMotor::$cibleName is deprecated (#666) (Haruka, Txori)
- PluXml can run under PHP5.6 without PHPMailer #592 (bazooka07)
- Enhance static page edition security (#558) (#589) (bazooka07)
- Sitemap loc attribute use the canonical url (#665) (Haruka)
- plxMsg() can handle multiple messages (#542) (#591) (lolo3129, bazooka07)
- Depedencies security and PHP8 update (Haruka)
- Administration messages CSS and animation changes (#578) (bazooka07)
- New constant PLX_VERSION_DATA - async versions between datas and PluXml core (#576) (bazooka07)
- Display install.php warning only for admins users and on index.php (#608) (bazooka07)
- Add better controls on input values in plxAdmin::editConfiguration() (#613) (bazooka07)
- Descending  order for sorting articles in tag mode and in the backoffice #595 (bazooka07)
- Update all .htaccess files (#593) (bazooka07)

### Refacto
- Replace arrays by constants in languages files (#633) (bazooka07)
- Frontend and backoffice sessions (#630) (#635) (bazooka07)
- Article edition (#656) (bazooka07)
- Authentication (#646) (bazooka07)
- Profil name in backoffice (#642) (bazooka07)
- Backoffice menu (#639) (bazooka07)
- Backoffice users page (#638) (bazooka07)
- Get categories on the homepage (#626) (bazooka07)
- Better checks in plxMotor (#622) (bazooka07)
- Check the result of plxMotor::parseArticle() in several places (#650) (bazooka07)
- Pagination in backoffice (#649) (bazooka07)
- Default configuration for first install (#624) (bazooka07)
- RSS Feed generation #619 (#658) (bazooka07)
- XML generation and datas checkes (#604) (bazooka07)
- Autoloader for plx classes (#598) (#599) (bazooka07)
- Random string generation (#655) (bazooka07)
- Replace hard-coded cryptographic key by a generated one (#653) (Haruka, bazooka07)
- Tags list (#583) (bazooka07)
- Remove use of plxUtils::testModRewrite() (#617) (#618) (bazooka07)
- .htaccess file generation for url rewriting (#573) (#616) (bazooka07)
- Thumbnail name generation (#615) (bazooka07)
- Enhance plxAdmin::editArticle() (#590) (bazooka07)
- Replace plxUtils::getValue() by plxUtils::getTagIndexValue() and plxUtils:/getTagValue() for parsing XML files (#588) (bazooka07)
- Cleanup long month names for plxDate (#586) (bazooka07)
- Replace tabs with spaces(4) in source code (#584) (bazooka07)
- Move *.js files from core/lib/ to core/admin/js/. and add .htaccess in core/lib/ (#581) (bazooka07)
- Enhance security in plxAdmin::editConfiguration() (#568) (bazooka07)
- url_encode dos not work properly in plxUtils::urlify() (#577) (bazooka07)
- Regex for moderated comments in the backoffice, misc. optimization for comments (#572) (bazooka07)

## PLUXML 5.8.16 (2024/08/27)
[+] toggle display for fatal error
[+] PLX_DEBUG is set to "false"

## PLUXML 5.8.15 (2024/07/18)
[+] plxAdmin::getFileStatique() always returns a string
[+] PLX_DEBUG is set to "true"
[+] In plxPlugins::__construct(), error is ignored in some cases

## PLUXML 5.8.14 (2024/05/15)
[+] date_creation or date_update may be missing in a file of article

## PLUXML 5.8.13 (2024/04/26)
FIX Preview article is alaways displaying on the same tab or window
[+] Better view on fatal error
FIX No use of utf8_decode() - Deprecated with PHP-8.2.0

# PLUXML 5.8.12 (2024/04/05)
FIX Typo in parametres_plugin.php
FIX Resolve symbolic link for Linux in plxPlugins::__construct()
FIX Declare plxMotor::cibleName - Required for PHP-8.0.0+

## PLUXML 5.8.11 (2024/03/23)
FIX disable plugins not ready for PHP8.0 and displays error message
FIX regex for plxGlob::PATTERNS for comments

## PLUXML 5.8.10 (2024/03/18)
FIX PHP 8+ compatibility

## PLUXML 5.8.9 (2022/08/01) ##
REVERT v5.8.8 FIX Static pages PHP injection vulnerability #558 (P3ter, Moritz Huppert)

## PLUXML 5.8.8 (2022/07/29) ##
FIX Delete install.php link and redirect fixed for all administration pages #540 (P3ter)
FIX Sitemap lastmod attribute is now set with the modification date #541 (gcyrillus, P3ter)
FIX Filter articles, comments and pages names in plxGlob #383 #545 (bazooka07)
FIX PHP8 __Deprecated: Required parameter $conf follows optional parameter $isHtml #537 (MAPC2012, Francis)
FIX Force some values of $plxMotor->aConf[] to be integer #552 (bazooka07)
FIX Minor fix in PlxMotor function artInfoFromFilename #554 (gcyrillus, bazooka07)
FIX Article thumbnail XSS vulnerability #556 (P3ter, Moritz Huppert)
FIX PluXml Documentation link for e-mail service configuration (P3ter)

## PLUXML 5.8.7 (2021/06/03) ##
[+] Move plugins cached css files to data folder #526 (bazooka07)
[+] Open static page from administration in a new tab #532 (bazooka07)
[+] i18n Occitan correction #535 (Mejans)
[+] PHPMailer update to 6.4.1 (P3ter)
FIX Comments no more displayed with PHP8 #529 (bazooka07)
FIX Revert .htaccess generation for plugins compatibility with url rewrite (P3ter)
FIX Wrong use of uasort() in pages and categories edition #530 (bazooka07)
FIX Display 404 error page when switching to a theme with a missing template #533 (bazooka07)
FIX Category creation can replace an existing category #534 (bazooka07)
FIX Minors CSS fixes (P3ter)

## PLUXML 5.8.6 (2021/02/15) ##
[+] Update PHPMailer 6.2.0 : security fixes and PHP 8.0 compatibility (P3ter)
[+] Distinct article and comment rss feeds configuration #521 (thx3r)
[+] Refine plxMotor::urlRewrite() to use parse_url() #520 (bazooka07)
FIX Missing thumbnail in first article #518 #525 (bazooka07, sudwebdesign)
FIX Error when editing .htaccess when Apache is not installed (P3ter)
FIX 404 Error when accessing /blog with url rewriting (bazooka07)
FIX Error on article publication caused by regex in plxMotor::artInfoFromFilename() #522 (bazooka07)
FIX Wording change on the capcha question #523 (Thx3r)

## PLUXML 5.8.5 (2021/01/11) ##
[+] Refine url rewriting rule #497 (bazooka07)
FIX Allow punctuation in articles and pages titles #505 (bazooka07)
FIX plxShow::catList() and plxShow::catId multiples article with same category #495 (bazooka07)
FIX Check if resetting password is not allowed before password change #500 (lolo3129, P3ter)
FIX Error with password reset token generation (P3ter)
FIX Check if a user profil is defined in the current session before changing it when necessary #511 (bazooka07)
FIX Allow comments default value #516 (bazooka07)

## PLUXML 5.8.4 (2020/09/07) ##
[+] Add mailto link on comment author's e-mail #512 (bazooka07)
[+] Disable removing short words on urlify (P3ter)
FIX Icon's CSS in medias manager (P3ter)
FIX Reduce notification bar width (P3ter)
FIX Error "Constant PLX_SITE_LANG already defined" (bazooka07)
FIX Do not use urlify for links (P3ter)
FIX Medias sorting in the backoffice (P3ter, sudwebdesign)
FIX Medias breadcrumb in the backoffice (P3ter, sudwebdesign)
FIX 404 error with tracking params in the URL (bazooka07)
FIX Display thumbnail in administration for SVG medias #482 #479 (bazooka07)

## PLUXML 5.8.3 (2020/05/19) ##
[+] New Fontello icons on authentification page and in the admin panel (bazooka07)
[+] Enhancement for CSRF token #385 (bazooka07)
[+] New "rename" and "copy to clipboard" icons in medias administration page #387 (bazooka07)
[+] plxMotor and plxFeed regex refacto #380 (P3ter, bazooka07)
[+] plxToken and plxCapcha optimisations #406 (bazooka07)
[+] plxShow->artThumbnail can have a link to the article #426 (Thatoo)
FIX Articles and static pages with underscores are not visible #380 (P3ter)
FIX Headline and Tags toggles always closed in article edition #382 (sudwebdesign)
FIX Fix medias administration zoombox and javascript optimisations #387 (bazooka07)
FIX Error when adding or modifying a user #393 (P3ter)
FIX Alternative to PHPMailer with sendmail #401 (bazooka07)
FIX Used of an extern URL as a static page #403 (bazooka07)
FIX Comment editor is empty #418 (P3ter)
FIX Comments list sorting #419 (P3ter)
FIX Comments feed link is displayed even if comments are disabled #429 (guiguid)
FIX Default theme : double underline on footer menu (P3ter)
FIX Oauth2 token generation button #445 (bazooka07)

## PLUXML 5.8.2 (2020/02/09) ##
[+] #371 Check PluXml update using javascript if "curl" or "file_get_contents" can not be used (bazooka07)
FIX #365 plxUtils::getLangs() optimisations (bazooka07)
FIX #367 plxUtils::sendmail() minor correction (bazooka07)
FIX #369 Comments indentation does not work on frontend (P3ter)
FIX #372 [i18n] missing L_CONFIG_ADVANCED_LOSTPASSWORD in oc, es, pl, pt, ro, ru, it (P3ter)
FIX #378 Add UTF-8 to plxUtils::urlify() (bazooka07)

## PLUXML 5.8.1 (sortie : 07/01/2020) ##
FIX #360 Undefined index: password_token
FIX #361 Use of undefined constant PLX_TEMPLATES
FIX #362 Uncaught Error: Class 'PlxTemplate' not found
FIX #363 Notice: Undefined index: name
FIX #364 warning messages and use browser language

## PLUXML 5.8 (sortie : 05/01/2020) ##
[+] PluCSS 1.3.1
[+] Feature "Mot de passe oublié" : envoi d'un lien par mail permettant la création d'un nouveau mot de passe (P3ter)
[+] Feature "Mot de passe oublié" : l'adresse e-mail devient obligatoire à l'installation de PluXml et à la création d'un utilisateur (P3ter)
[+] Ajout de la translitteration des URLs des articles, des pages et des médias pour le Russe, le Polonais, le Roumain et l'Allemand (bazooka07, P3ter)
[+] Thème par défaut : ajout du titre et du sous-titre sur mobile, modification des breakpoints et de la largeur des bords, modification de l'entête (P3ter, sudwebdesign)
[+] Amélioration des performances du gestionnaire de médias (bazooka07)
[+] Les modifications sur les profils utilisateurs sont immédiatement visibles, inutile de se re-connecter (sudwebdesign)
[+] Un utilisateur supprimé ou désactivé est déconnecté automatiquement (sudwebdesign)
[+] Administration des médias : affichage du nombre maximum de fichiers téléversables en une seule fois (sudwebdesign)
[+] Ajout de la fonction plxUtils::printInputRadio permettant d'afficher des boutons radio (P3ter)
[+] Ajout de la fonction plxUtils::sendMailPhpMailer permettant d'envoyer des mails avec ou sans OAUTH2 (P3ter)
[+] Ajout de la fonction plxUtils::printLinkCss permettant d'afficher une balise <link> avec la date de modification du fichier (bazooka07, sudwebdesign)
[+] Ajout de la fonction plxUtils::printSelectDir permettant d'afficher une arborescence dans l'administration des médias et des templates (bazooka07, sudwebdesign)
[+] Plugins : ajout de nouveaux hooks sur la page d'authentification AdminAuthBegin, AdminAuthTopLostPassword, AdminAuthLostPassword, AdminAuthTopChangePassword, AdminAuthChangePassword, AdminAuthTopChangePasswordError, AdminAuthChangePasswordError  (sudwebdesign)
[+] Ajout de paramètres à la fonction plxUtils::cleanHeaders() (sudwebdesign)
[+] #40 Ne pas afficher le meta du site en mode article, static, categorie (Stéphane)
[+] #135 Contextualiser le lien du flux RSS dans le <head> en fonction du mode catégorie, tag, ou autre (bazooka07, P3ter)
[+] #207 Afficher/masquer le chapeau et les tags d'un article dans l'administration en CSS3 au lieu d'utiliser du JS (bazooka07, Stéphane, P3ter)
[+] #215 Mise en avant du lien de l'article en cours de modification dans l'administration (sudwebdesign, P3ter)
[+] #216 Refacto de la fonction plxUtils::printArea() (bazooka07)
[+] #232 Ajout de la possibilité de désactiver l'affichage des flux RSS (Philippe-M)
[+] #280 Support des formats de fichiers .gpx, .bmp et .webp par le gestionnaire de média (P3ter)
[+] #293 Amélioration de la sécurité du repertoire data (bazooka07)
[+] #305 Remplacement dirname(__FILE__) par __DIR__ dans l'admin et le thème par défaut (P3ter)
[+] #309 Remplacement de define() par const (bazooka07)
[+] #312 En présence d'une homepage statique, le lien "archive total" redirige vers le blog (je-evrard, bazooka07, P3ter)
[+] #312 Remplacement de la fonction str_replace() par strtr() pour les liens vers les pages archives (bazooka07, P3ter)
[+] #313 Fermer l'overlay des médias avec la touche "ESC/ECHAP" du clavier (jerrywham)
[+] #317 #325 plxShow->lastArtList() rechercher des catégories par leur url plutôt que par leur ID (jerrywham, sudwebdesign)
[+] #331 #330 Ajout d'une image d'accroche sur une catégorie (Philippe-M)
[+] #348 Ajout d'un paramètre "extra" à la fonction plxUtils->printArea (bazooka07)
[+] #349 Lors de l'installation, possibilité de créer ou non les données d'exemples (sudwebdesign)
FIX Correction de la gestion des ID CSS dans les menus (sudwebdesign)
FIX Autorisation du cross-origin dans les flux RSS/ATOM (sudwebdesign)
FIX #253 Faille XSS : modification des paramètres du cookie session (bazooka07, P3ter)
FIX #287 Capcha : ajout d'un espace insécable avant le "?" (P3ter)
FIX #302 Renvoie vers index.php si un paramètre est inconnu dans l'URI (bazooka07)
FIX #315 Les espaces dans la recherche de medias font disparaitre les résultats (P3ter)
FIX #318 L_NEW_CATEGORY en doublon pour certaines langues (P3ter)
FIX #322 plxDate::getCalendar() ne retourne jamais L_SUNDAY (jerrywham, P3ter)
FIX #323 La fonction plxUtils::printSelect ne fonctione pas avec un "selected" de type numérique (Philippe-M)
FIX #324 Correction couleur des selecteurs si le theme du navigateur est inversé (sudwebdesign)
FIX #326 Remplacement de "create_function()" déprécié depuis PHP 5.3 (faille et dégradation des performances) (P3ter)
FIX #329 Mauvais affichage du nom du site et du menu avec Safari (P3ter)
FIX #332 Traductions manquantes en Polonais (sudwebdesign)
FIX #334 Thème par défaut : affichage cassé des sous-menu de pages statiques (sudwebdesign)
FIX #343 Article n'est plus affiché après suppression de la catégorie (P3ter)
FIX #345 $plxShow->catList affiche la catégorie 10 à tort (bazooka07)
FIX #349 Suppression de l'utilisation de la fonction "get_magic_quotes_gpc()" dépréciée en PHP 7.4 (sudwebdesign)

## PLUXML 5.7 (sortie : 11/12/2018) ##

[+] PluCSS 1.3
[+] Nouveau thème par défaut
[+] Prise en compte du fichier CSS des plugins sur la page auth.php
[+] Administration : Tri ordre des catégories et des pages statiques par drag&drop
[+] Affichage de l'image d'accroche dans les flux RSS
[+] Gestionnaire de médias : affichage du nombre maximal de fichiers par envoi
[+] Amélioration des performances du gestionnaire de médias (contribution bazooka07)
[+] Thème par défaut : entête de page fixe (contribution bazooka07)
[+] Tri alphabétique inversé des articles et catégories  (contribution bazooka07)
[+] #225 Ajout id à la balise <body> de la page auth.php
[+] #230 Image d'accroche dans le premier article créé à l'installation
[+] #239 Test et affichage accès en écriture du dossier racine pour les thèmes
[+] #264 fonction debugJS (contribution bazooka07)
[+] #265 Affichage des archives sur une périodes glissantes de 12 mois (contribution bazooka07)
[+] #266 Tri des pages statiques, catégories et plugins par drag n drop (contribution bazooka07)
[+] #269 Ajout scope[admin|site] dans les fichiers infos.xml des plugins pour charger un plugin uniquement coté admin, site ou les 2 (contribution bazooka07)
[+] #305 Remplacement dirname(__FILE__) par __DIR__ dans l'admin et le thème par défaut (contribution bazzoka07)
FIX Suppression fichier plugin update impossible (droit fichier)
FIX Chevauchement des menus de l'administration avec un facteur de zoom > 100%
FIX Administration : mauvais affichage des caractères spéciaux dans le nom de l'auteur d'un commentaire (contribution bazooka07)
FIX mediasManager.js : fonction callback inactive avec Firefox Quantum
FIX Minification des fichiers CSS du thème de l'admin et du thème par défaut
FIX #220 Problème affichage CSS thème par défaut
FIX #225 Ajout id à la balise <body> de la page auth.php
FIX #237 urlRewrite et caractère "&"
FIX #249 ajout clés de traduction russe manquantes
FIX #256 plxPlugin::setParam : mauvais test sur le paramètre "$type" (contribution bazooka07)
FIX #260 Libellé de catégorie "Non classé" non affiché lors de la prévisualisation
FIX #262 Suppression tris inutiles
FIX #289 Ajout de la class "noactive" par défaut sur les tags (contribution WorldBot)
FIX #302 Renvoie vers index.php si un paramètre est inconnu dans l'URI  (contribution bazooka07)
FIX #303 Récupération des archives d'une année (contribution sudwebdesign)
FIX #305 Remplacement dirname(__FILE__) par __DIR__ dans l'admin et le thème par défaut (contribution bazzoka07)

## PLUXML 5.6 (sortie : 05/04/2017) ##

[+] PluCSS 1.2
[+] Contrôle de la force des mots de passe saisis
[+] Protection attaque brute force sur l'écran de connexion à l'administration
[+] Administration > Paramètres > Thèmes : test existence fichier infos.xml dans le dossier thème pour le lister ou non
[+] Administration > Paramètres > Plugins : ajout filtre de recherche
[+] Administration > Paramètres > Options d'affichage : Nombre d'articles affichés par page dans les mots clés
[+] Administration : recherche possible à partir de l'identifiant d'un article
[+] Administration : suppression fichier install.php à partir du message d'information
[+] Gestionnaire de médias : affichage des miniatures jpeg
[+] Gestionnaire de médias : copie lien image dans le presse-papier
[+] Gestionnaire de médias : champ de recherche/filtre sur le nom des images
[+] Gestionnaire de médias : renommage fichier
[+] Suppression des doublons dans la saisie des tags des articles
[+] Gestion de la méthode onUpdate dans les plugins
[+] Tri des articles par ordre aléatoire configurable à partir de l'administration (Paramètres > Options d'affichage et Catégories)
[+] plxShow:templateCSS - Prise en compte des fichiers css minifiés si disponibles (contribution alexandre-lg)
[+] Réglage de l'affichage de l'indentation des commentaires sur smartphones (contribution kowalsky)
[+] Ajout du hook plxMotorRedir301 dans la classe plxMotor
[+] plxShow::staticList : ajout de la variable #group_status
[+] Thème par défaut : affichage des groupes de pages statiques sous forme de menus déroulant
[+] Plugins : si la langue par défaut n'est pas disponible on tente de charger le fr.php sinon on prend le 1er fichier de langue dispo
[+] Test existence libraire XML à l'installation (onctribution Sbgodin)
[+] plxShow::comLevel : paramètre pour le nom de la classe css servant à l'indentation des commentaires (contribution Jerry Wham)
[+] plxShow::tagList : mise en évidence dans la sidebar des tags appartenant à l'article (contribution Yannic)
[+] plxShow::artUrl : ajout paramètre echo et extra
[+] plxShow::staticUrl : ajout paramètre echo et extra
[+] #69: plxAdmin:editArticle - ajout du hook plxAdminEditArticleEnd
[+] #177: Ajout de la protection du lien téléphone (contribution cfdev)
[+] #191: plxShow:catList - Ajout de la variable #cat_description
[+] #194: Rédaction d'un article: optimisation de l'ajout rapide de tag (contribution MatthieuQuantin)
[+] #212: Administration > Paramètres > Comptes utilisateurs : ajout de l'adresse email (contribution bazooka07)
[+] #214: plxUtils::printInput : meilleur comportement responsive des champs input (contribution bazooka07)
[-] Suppression icon help.png, remplacée par du css
BUG Non affichage du nombre de commentaires d'un article si les commentaires sont fermés
BUG plxUtils::removeAccents - amélioration de la prise en compte des caractères non latin
BUG Administration > Pages Statiques : lien Voir incomplet
BUG Administration : problème d'affichage du compteur des articles en attente de validation
BUG Administration : modification impossible de la date du commentaire à partir de l'icône calendrier
BUG Administration : Mise à jour auteur commentaire avec apostrophe/caractères spéciaux transformés en équivalent html
BUG Mauvais lien des commentaires dans les flux rss
BUG Gestionnaire de médias: mauvais affichage de l'extension d'un fichier si le fichier n'a pas d'extension
BUG Administration : conflit entre la langue par défaut du site et la langue du profil utilisateur
BUG #171: Décalage theme par défaut
BUG #174: Mauvaise réécriture d'url avec les liens data:image, javascript et commençant par #
BUG #176: Image preview.png du thème par defaut déformée
BUG #181: Commentaires toujours refusés si capcha désactivé (contribution ortolot)
BUG #182: Affichage en trop si commentaires fermés (contribution josé)
BUG #183: Fichier css custom non chargé sur la page auth.php
BUG #184: Pas d'affichage du flux RSS des commentaires quand il n'y a pas de commentaires enregistrés
BUG #185: Mauvaise cible de téléchargement
BUG #187: Mauvais numéro d'article dans l'ajout d'un nouveau commentaire (contribution mathieu269)
BUG #189: plxAdmin:modCommentaire - mauvais message affiché après la validation d'un commentaire
BUG #190: Warning sur l'utilisation de mktime (paramètre de type string au lieu de integer)
BUG #192: Rédaction d'un article: ajout rapide de tag avec une apostrophe impossible (erreur javascript)
BUG #205: Pas de réécriture d'url pour les liens commençant uniquement par une ancre
BUG #208: Suppression sans confirmation des pages statiques, catégories, utilisateurs, médias

## PLUXML 5.5 (sortie : 01/04/2016) ##

[+] #99, #165: Ajout de la prise en compte des extensions "m4a, m4v, epub, svg, vtt, webm, xcf" dans le gestionnaire de médias
[+] #110: Gestionnaire de médias, ajout des fichiers: Option "Redimensionner images > Taille originale" cochée par défaut
[+] #114: Réécriture de la pagination des articles et des commentaires dans l'administration
[-] #107: Suppression du fichier version, remplacé par la constante PLX_VERSION dans le fichier core/lib/config.php
[+] rédaction d'un article: ajout d'un champs pour avoir une image d'accroche
[+] API pour afficher le gestionnaire de médias dans une fenêtre popup et récuperer l'image sélectionnée dans la fenêtre appelante
[+] Mise à jour traduction polonais: contribution 18jaguar18
[+] Ajout de la constante PLX_FEED
[+] Ajout d'un jeton de sécurité au formulaire des commentaires
[+] Gestionnaire de médias: possibilité de sélectionner plusieurs fichiers à la fois pour l'upload des fichiers
[+] Gestionnaire de médias: visionneuse image en cliquant sur l'icône de la photo
[+] plxUtils::makeThumb - réécriture de la méthode pour générer des images cropées et carré
[+] Réécriture de la méthode plxShow::pageTitle()
[+] Administration: ajout du menu Paramètres > Thèmes
[+] Ajout des hooks: AdminThemesDisplayTop, AdminThemesDisplay, AdminThemesDisplayFoot
[+] Réécriture et optimisation de la fonction plxUtils::rel2abs (contribution bazooka07)
[+] Page statique: gestion de la date de création et de mise à jour
[+] Articles: gestion de la date de création et de mise à jour
[+] plxShow: ajout des fonctions staticCreationDate et staticUpdateDate pour afficher la date de création et de mise à jour d'une page statique
[+] plxShow: ajout de la fonction artThumbnail pour afficher l'image d'accroche de l'article
[+] plxShow::lastArtList - ajout du hook plxShowLastArtListContent
[+] plxShow::tagList - ajout des variables #tag_count, #tag_item (contribution danielsan)
[+] #126: plxShow::staticInclude - l'affichage tient compte si la page est active ou non
[+] plxDate::formatDate() - ajout de la variable #time
[+] #68: Sitemap - datation des pages statiques
[+] Optimisation de la taille des images (contribution Syl)
[+] Ajout de la variable #art_thumbnail à la fonction plxShow::lastArtList
[+] Mise à jour de la traduction occitane (contribution Rubén)
BUG fix #101: Thème par défaut - problème de retour à la ligne dans l'affichage des tags
BUG fix #102: Thème par défaut - la sidebar passe dans le footer après désactivation du captcha
BUG fix #103: La recherche dans les articles ne fonctionne dans l'administration pas à partir du champ "Rechercher"
BUG fix #104: Message d'erreur lorsqu'on essaye d’accéder à une page statique non active
BUG fix #108: Changement de langue non pris en compte sur l'écran d'installation
BUG fix #109: Édition page statique: disparition de champs
BUG fix #112: Encodage des caractères sur le titre d'un article sur la page d'administration (plxUtils::strCut)
BUG fix #122: Mauvais affichage du lien "Visualiser" sur la page d'accueil des articles de l'administration
BUG fix #125: Bug affichage flux RSS d'une catégorie
BUG Administration > Paramètres > Edition des fichiers du thème : impossible de sauvegarder les modifications

## PLUXML 5.4 (sortie : 13/07/2015) ##

[+] Nouveau thème par défaut
[+] Nouvelle interface d'administration en responsive design
[+] Aménagement du gestionnaire de médias
[+] Ajout traduction en occitan (contribution Rubén)
[+] Correction traduction de l'italien (contribution nikynik)
[+] Lien en nofollow pour les auteurs des commentaires
[+] issue #76: plxShow::lastComList affichage du titre de l'article des commentaires avec la variable #com_art_title (contribution Suricat)
[+] issue #73: Formatage des jours sur 1 ou 2 chiffres avec les variables #num_day(1) ou #num_day(2)
[+] Administration > Paramètres > Configuration Avancée: ajout champ "Emplacement du fichier css personnel pour customisation de l'interface d'administration (option)"
[+] classe plxPlugin: ajout de la méthode delParam (contribution jormun)
[+] plxShow::artChapo() : ajout du paramètre optionnel anchor (contribution jerrywham)
[+] plxShow::staticInclude() : possibilité d'inclure une page statique à partir de son titre ou de son url
[+] #78: Tri aléatoire des articles (random)
[+] plxShow: ajout du hook plxShowStaticContentBegin
BUG fix #77: fct artFeed: lien rss pour les catégories incomplet
BUG fix #51: L'accès à l'écran d'administration des commentaires est impossible si les commentaires sont désactivés dans les paramètres de base de PluXml
BUG Correction d'une possible auth-bypass (contribution jvoisin)
BUG Prise en compte des liens en // dans la réécriture d'url
BUG Mauvaise position du menu d'accès au fichier admin.php d'un plugin dans la sidebar d'administration si position non renseignée
BUG Gestionnaire de médias: affichage du lien pour visualiser la miniature même si l'image n'existe pas sur le serveur

## PLUXML 5.3.1 (sortie : 13/03/2014) ##

[+] plxAdmin::delArticle: ajout du hook plxAdminDelArticle
[+] Administration > Nouvel article: ajout de l'attribut id="id_cal" à la balise <a> de l'icone du calendrier
BUG fix #46: Paramètre display_empty_cat non initialisé dans install.php
BUG fix #48: Erreur de parenthèse dans plxFeed sur instruction utf8_decode
BUG fix #53: plxShow::staticList : variable #static_class non traitée (contribution ReSpAwN)
BUG fix #54: Ajout commentaire: mauvais controle existence fichier (contribution rockyhorror)
BUG fix #55: Duplicate content avec page statique en page d'accueil (contribution Ethno Urban)
BUG fix #56: Mauvais tri alpha des tags avec des accents
BUG fix #57: Mauvais tri des répertoires dans le gestionnaire de médias (contribution rockyhorror)
BUG Mauvais chargement des fichiers de langues. Impact sur les plugins
Thème par défaut
- problème formatage balise <pre>
- affichage la sidebar sur la page d'erreur
- fix #45 problème balise blockquote
- fix #47 erreur format html5 balise <time>

## PLUXML 5.3 (sortie : 08/01/2014) ##

[+] Affichage du titre du site après le nom de la catégorie dans la balise <title> (amélioration du référencement)
[+] Administration: ajout d'un message de confirmation avant les suppressions en masse
[+] Administration: ajout d'un favicon
[+] Administration > Pages statiques: possibilité d'ajouter des urls internes commençant par le caractère ?
[+] Administration > Paramètres > Plugins: affichage d'un message d'information en rouge pour un plugin activé non configuré si requiert une configuration
[+] Administration > Paramètres > Plugins: ajout menu "Code css" pour gérer un fichier admin.css et site.css propre à chaque plugin (dans un dossier css du plugin)
[+) plxShow::artReadMore nouvelle fonction permettant d'afficher le lien "Lire la suite"
[+] plxShow::tagList ajout tri des tags par ordre alphabétique, random, aucun (alpha|random|'')
[+] plxShow::lastComList traduction oubliée (a dit)
[+] plxShow::staticList réécriture de la fonction pour mieux gérer l'affichage des groupes et permettre de faire des menus déroulants plus facilement
[+] plxShow::lastArtList ajout du paramètre sort pour permettre de trier l'affichage des derniers articles par date croissante, décroissante ou par titre (sort|rsort|alpha)
[+] plxUtils::printInput ajout du paramètre placeholder
[+] plxUtils::printSelect génération de l'id du champ à partir d'un paramètre si renseigné, sinon génération automatique
[+] plxUtils::sendMail ajout de Reply-To et Date
[+] plxUtils::minify nouvelle fonction pour minifier un buffer de sortie
[+] Nouveau thème par défaut
[+] Mise à jour traduction espagnole (contribution toote)
[+] Modification pour tenir compte des proxies HTTPS
BUG Administration: menu en double dans la sidebar d'administration si setAdminMenu utilisé dans les plugins
BUG plxUtils::getRacine problème avec certains hébergeurs (caractère \ en fin d'url)
BUG plxUtils::getGets mauvais renvoi des paramètres passés dans l'url
BUG plxShow::error404 erreur de syntaxe (espace en trop)
BUG plxShow::pageTitle mauvais affichage du nom du tag (mode tags)
BUG Thème par défaut fichier tags.php: mauvais affichage du nom du tag
BUG fix #23: plxUtils::rel2abs liens spéciaux en *:// mal interprétés
BUG fix #24: exclusion des fichiers articles .xml ne commençant pas par un n° d'article
Bug fix #25: Administration > Paramètres > Configuration avancée : .htaccess modifié quand clic sur le bouton "Modifier la configuration avancée"
BUG fix #31: plxShow::artCat mauvais affichage des catégories d'un article quand article assigné à la catégorie "Page d'accueil"
BUG fix #33: plxUtils::rel2abs liens avec des simples quote non réécrits
BUG fix #35: Connexion impossible suite à la modification du login admin 001
BUG fix #36: plxUtils::checkSite mauvaise validation url
BUG fix #43: Undefined index: HTTP_ACCEPT_ENCODING
BUG fix #44: Accès index tableau inexistant

## PLUXML 5.2 (sortie : 04/08/2013) ##

[+] sitemap.php: ajout des hooks SitemapBegin et SitemapEnd
[+] feed.php: ajout des hooks FeedBegin et FeedEnd
[+] Récupération automatique de la racine du site
[+] plxShow::lastArtList: ajout de #art_chapo(num) pour afficher n caractères du chapo
[+] plxShow::artAuthorInfos: ajout de #art_author pour afficher l'auteur de l'article
[+] plxDate: gestion du libellé court des mois
[+] Template pour la page d'accueil (Administration > Paramètres > Options d'affichage > déroulant "Template de la page d'accueil")
[+] Administration - rédaction d'un article: vérification de l'unicité de l'url de l'article
[+] Administration > Paramètres > Plugins: nouvel écran de gestion des plugins
[+] Réécriture d'une partie du moteur des plugins pour accélerer le temps de chargement et réduire l'occupation mémoire
[-] Suppression du paramètre racine dans le fichier de configuration parametres.xml
BUG fix #4  Gestionnaire de médias: mauvais tri ddu déroulant 'Dossier'
BUG fix #2  Notice à l'activation d'un plugin si des plugins ont été supprimés manuellement
BUG fix #3  Thème par défaut: balise <article> en double dans le fichier du thème article-full-width.php
BUG fix #5  Thème par défaut: mauvais padding pour la balise <code>
BUG fix #8  Thème par défaut: espace en trop à la fin de 2 balises <link>
BUG fix #10 Thème par défaut: balise <html> absente
BUG fix #6  plxShow::templateCss() Pas de prise en compte de la réécriture d'url
BUG fix #9  plxShow::artCat() les catégories inactives sont listées
BUG fix #7  plxShow::pagination() Les articles avec une date de publication future sont comptabilisés dans la pagination
BUG fix #12 Warning date_default_timezone_get() sur la page d'installation
BUG fix #13 Warning date_default_timezone_get() dans les flux rss
BUG fix #17 Administration: mauvaise pagination lorsque le filtre par catégorie est appliqué sur la page des articles
BUG fix #19 Administration: Notice à la rédaction d'un article si date de publication erronée
Bug fix #20 Erreur si fichier version non présent
BUG Réglage du focus sur le bouton Installer de la page d'installation

## PLUXML 5.1.7 (sortie : 25/01/2013) ##

[+] Les fichiers de parametres des plugins sont désormais stockés dans le dossier de configuration de PluXml
[+] plxShow:tagList(): gestion de la taille des tags (#tag_size)
[+) plxShow: ajout du hook plxShowTagFeed
[+] plxShow: ajout de la fontion tagFeed() : flux rss des articles pour un mot clé
[+] plxShow: ajout de la méthode artCatIds()
[+] plxShow: ajout de la méthode artActiveCatId()
[+] plxShow:pageTitle() : positionnement du titre du site en dernier pour améliorer les référencements (contribution Jos)
[+] plxShow:pagination() : suppression duplicate content entre la page1 et la page d'accueil
[+] plxShow::comMessage() : ajout parametre format (variable #com_message)
[+] plxShow::catDescription() : ajout format affichage #cat_description
[+] plxAdmin::htaccess() : pas d'écrasement et perte du contenu du fichier .htaccess s'il existe déjà + ajout du hook plxAdminHtaccess
[+] plxPlugin: ajout de la fonction setAdminMenu pour personnaliser le menu permettant d'accèder à la page admin.php d'un plugin
[+] plxFeed: ajouts des hooks plxFeedRssArticlesXml, plxFeedRssCommentsXml, plxFeedAdminCommentsXml
[+] plxFeed: changement du nom des méthodes getAdminCommentaires, getRssCommentaires en getAdminComments et getRssComments
[+] Administration: sidebar statique
[+] Administration: Edition article : ajout du champ "Lien de l'article" et du lien "voir" pour accéder à l'article
[+] Administration: Paramètres > Plugins -> Ordre de chargement des plugins
[+] Administration: Paramètres > Configuration avancée -> Ajout du choix de l'emplacement des fichiers de configuration (dossier)
[+] Administration: Paramètres > Options d'affichage > Éditer les fichiers du thème : edition des fichiers dans les sous-dossiers
[+] Administration: Catégories > Options : ajout du paramètre "Afficher les articles de cette catégorie sur la page d'accueil" (oui/non)
[+] Administration: Gestionnaire de médias: tri des dossiers dans le déroulant 'Dossier'
[+] Administration: Ajout du paramètre thumbs dans le fichier de configuration de PluXml pour création ou pas des miniatures
[+] Amélioration de la traduction en allemand (contribution Jürgen K.)
[+] Nouveau thème defaut
[-] Suppression du thème mobile.defaut
[-] Suppression ancien thème defaut
[-] plxShow: suppression de la méthode artCatId()
[-] plxShow::catDescription: suppression scope article car bug si article associé à plusieurs catégories
[-] plxAdmin::htaccess() : suppression des hooks plxAdminHtaccessNew et plxAdminHtaccessUpdate
[-] plxUtils::showMsg : suppression des balises <strong> (contribution danielsan)
[-] Administration: Paramètres > Configuration avancée -> Suppression du choix de l'emplacement des fichiers .xml
BUG Pas d'affichage des articles affectés à plusieurs catégories si une catégorie est inactive
BUG Pas de récupération des infos dans le fichier infos.xml d'un plugin sur l'écran admin.php du plugin
BUG sitemap: mauvais format de date dans la balise lastmod
BUG Réécriture d'url: mauvaise gestion des paramètres dans l'url (&)
BUG plxMotor:urlRewrite() : renvoi de l'url du site si uri uniquement constituée de ?
BUG plxMotor:demarrage() : si aucun article, statut 200 au lieu de 404 (contribution petitchevalroux)
BUG plxShow::artAuthorInfos() : erreur si l'auteur n'existe plus
BUG plxShow::staticList() : tri des menus différents si paramètre extra renseigné ou pas
BUG plxShow::templateCss() : mauvais chemin dans le test existence fichier css (contribution danielsan)
BUG plxShow::catName() : appel de la fonction carUrl au lieu de catUrl
BUG plxShow:pagination(): lien "première page" collé au lien "précédente"
BUG plxFeed::getRssArticles() : pas de réécriture d'url dans la balise atom:link (contribution petitchevalroux)
BUG plxFeed: erreur date dans les flux rss si aucun article
BUG plxPlugins::getInfos(): mauvais test sur la récupération du site
BUG plxPlugins: variable default_lang mal renseignée -> mauvais chargement du fichier de langue des plugins
BUG plxUtils::rel2abs : problème d'url en mode article
BUG plxUtils::makeThumb() : problème si largeur ou hauteur non renseignée
BUG plxUtils::strCut() : problème pour couper une chaine de caractère avec l'encodage UTF-8
BUG date_default_timezone_get + php >= 5.4 sur hébergeur 1&1
BUG sitemap : pas d'affichage des pages statiques si option menu = NON
Sécurité: Faille XSS possible sur la récupération de l'adresse ip d'un visiteur (remerciement à The Lizard King)
Sécurité: Mauvais contrôle des tokens (remerciement à The Lizard King)
Sécurité: Suppression du champ contenant la solution du captcha dans le formulaires des commentaires (remerciement à The Lizard King)

## PLUXML 5.1.6 (sortie : 16/04/2012) ##

[+] Administration: Paramètres > Option d'affichage -> option pour afficher le nom des catégories même si elles ne contiennent pas d'article
[+] Administration: Page statiques > Options -> ajout champ pour définir le contenu de la balise <title>
[+] Administration: Catégories > Options -> ajout champ pour définir le contenu de la balise <title>
[+] Administration: affichage compteur des articles en attente de validation à droite du menu "Articles"
[+] Administration: compteur des commentaires hors ligne à droite du menu "commentaires" cliquable -> accès direct à la liste des commentaires hors ligne.
[+] Administration: Edition des commentaires -> possibilité de modifier la date/heure des commentaires
[+] Administration: Gestionnaire de médias: ajout du format ogg dans la liste des fichiers autorisés
[+] Administration: Prise en compte du fuseau horaire dans le javascript de l'icone calendrier
[+] Core: réécriture de la gestion des fuseaux horaires
[+] Core: déplacement du hook plxShowPagination
[+] Core: prise en compte de la valeur localhost dans le champ url des pages statiques
[+] Core: ajout du hook IndexBegin
[+] Core: ajout de la classe plxTimezones (fichier class.plx.timezones.php)
[+] plxShow: ajout de la classe css p_current pour la page courante de la pagination
[+] plxShow: ajout de la fonction tagName
[+] plxShow: lastComList ajout paramètre pour filtrer les derniers commentaires sur 1 ou plusieurs catégories
[+] plxShow: ajout de la fonction catUrl, méthode qui retourne l'url d'une catégorie
[+] plxShow: réécriture de la fonction artNbCom
[+] plxShow: réécriture de la fonction nbAllCom
[+] plxShow: réécriture de la fonction nbAllArt
[+] plxDate: ajout des fonctions formatDate, timestamp2Date, date2Array
[-] plxDate: suppression des fonctions dateToIso, timestampToIso, dateIsoToHum, heureIsoToHum, dateIso2Admin
[-] plxShow: suppression des fonctions nbAllCat, artHour
BUG Highlight des menus admin des plugins
BUG Highlight du menu "Blog" avec une page statique ajoutée par un plugin
BUG Pagination
BUG Version du sitemap
BUG Appel des hooks dans le sitemap sans eval
BUG Affichage des catégories contenant des articles avec une date de publication future
BUG Security error : invalid or expired token
BUG Dans plxMedias si "upload_max_filesize" dans php.ini paramétré pour des fichiers >= 1Go
BUG A partir de la deuxième page du blog, la classe de "Accueil" devient noactive
BUG Mauvaise classe active pour le menu des archives année/mois
BUG Mauvaise réécriture d'url dans le fichier .htaccess
BUG Fonction de téléchargement de fichier (contribution Humpf)
BUG Réécriture d'url (contribution Humpf)
BUG plxShow: appel callHook en double (contribution rockyhorror)
BUG Gestionnaire de médias: réinitialisation de la variable de session medias (pour medias.php) au cas si changement de chemin images/documents dans la config
BUG Mauvais rappel du type de commentaires sélectionnés
Sécurité: Full Path Disclosure avec un code injection sur le PHPSESSID (contribution MyckSécurity)
Sécurité: Possible faille XSS dans le fichier de mise à jour (contribution gwae)
Sécurité: Local File Inclusion (crédit High-Tech Bridge SA Security Research Lab: HTB23086)

## PLUXML 5.1.5 (sortie : 04/12/2011) ##

[+] Conversion encodage des fichiers ANSI en UTF-8 (sans BOM)
BUG Version du sitemap 0.9 au lieu de 0.90 (problème avec Google)
Sécurité Faille sécurité d'identification sur des sites en sous domaines

## PLUXML 5.1.4 (sortie : 27/11/2011) ##

[+] Ajout des hooks AdminArticlePreview, AdminArticlePostData, AdminArticleParseData, AdminArticleInitData
[+] Accès des admins et modérateurs sur les images et documents de tous les utilisateurs
[+] Ajout lien "Répondre" sur la page des commentaires
[+] Validation avant publication des articles des profils "Rédacteur" et "Editeur"
[+] Passage de paramètres et retour de valeur dans l'appel des hooks utilisateurs (plxShow::callHook)
[+] Mise à jour du sitemap en version 0.90
[+] plxMotor::artInfoFromFilename passe de protected en plublic
[+] Meilleure gestion des liens pour éviter le duplicate content
[+] Redirections 301
[+] Gestion des erreurs 404
[+] Ajout des paramètres racine_themes et racine_plugins pour définir l'emplacement des dossiers
[+] Ajout champ pour définir le contenu de la balise <title> des articles
[-] Suppression des liens du type feed.php?rss ou feed.php?rss/commentaires par feed.php ou feed.php?commentaires
[-] Suppression du fichier blog.php
BUG Gestionnaire de médias, formulaire d'envoi de fichier: le radiobox "Redimensionner images" peut être sélectionné pour plusieurs valeurs
BUG Initialisation du type de commentaire admin/normal
BUG Erreur lors de l'enregistement de la modification d'un commentaire
BUG plxShow::staticList Erreur sur l'url de la page statique
BUG Adresse ip remise à blanc lors de la sauvegarde/modification d'un commentaire
BUG Page statique d'accueil listée en double dans le sitemap
BUG Affichage des metas vides en mode article
BUG Compression Gzip
BUG Perte de l'id de l'article en cours d'édition si ajout d'une nouvelle catégorie dans le sidebar (article.php)

## PLUXML 5.1.3 (sortie : 27/09/2011) ##

[+] Ajout du hook AdminTopBottom
[+] Administration, Pages statiques : augmentation de la longueur du champ "Titre" à 255 caractères
[+] Administration, Pages statiques : ajout de la colonne "Page d'accueil" pour sélectionner la page statique à mettre en page d'accueil
[+] Administration, Nouvel article : dépliant sur la zone chapo (lien afficher/masquer). Zone chapo masquée par défaut si vide.
[+] Administration, Médias : lors de l'upload d'un fichier, changement du formatage du nom si le fichier existe déjà (ex: 1image.jpg remplacé par image.1.jpg)
[+] Administration, Médias : tri des fichiers par titre/date (entête de colonne cliquable)
[+] Administration, Médias : ajout de la fonction "Recréer les miniatures"
[+] Administration, Médias : ajout zone taille image paramétrable dans l'administration (comme pour les miniatures)
[+] Administration, Articles : amélioration du filtre sur les articles
[+] Administration, Commentaires : ajout du nombre de commentaires hors-ligne dans la sidebar
[+] Administration, Catégories : possibilité d'activer/désactiver des catégories
[+] Administration, Plugins : pas d'execution des méthodes OnActivate et OnDeactive des plugins lors de leur activation/désactivation
[+] Amélioration et optimisation du script de mise à jour pour prendre en compte les versions bétas
[+] plxShow::lastArtList : ajout de #art_nbcoms pour afficher le nombre de commentaires de chaque article
[+] Traduction : gestion du pluriel "aucun commentaire", "1 commentaire", "2 commentaires" (idem pour article et catégorie)
[+] Thème par défaut : modification paramètre de la fonction artAuthorInfos pour ne pas avoir de balise div vide affichée
[+] Thème par défaut : renommage du fichier screen.css en style.css et déplacement à la racine du dossier
[+] Plugin plxToolbar : bouton pour ajouter une image à gauche, au centre ou à droite + tri des fichiers par titre/date
[+] Plugin plxEditor : bouton pour ajouter une image à gauche, au centre ou à droite + tri des fichiers par titre/date
BUG Administration, Articles : lien "Publiés et "Brouillons" pas activé quand sélectionné
BUG Administration, Articles : filtre "Non classé" sur la catégorie inactif
BUG Administration, Paramétrages, Comptes utilisateurs > Options : adresse email non sauvegardée
BUG plxUtils::rel2abs : erreur dans le regex
BUG Fichier function.js : suppression d'un message d'alerte de debuggage
BUG Site : lien Acceuil/Blog mal activé quand sélectionné (classe active dans le css)
BUG Site : liens sur les archives annuelles inopérants
BUG Traduction thème par défaut : chaines de traduction oubliées
BUG Traduction class.plx.feed.php : chaines de traduction oubliées
BUG Administration : Division par zero si paramètre bypage_admin non définit et renseigné (pagination)
BUG Mauvais profil utilisateur avec register_globals = on
Sécurité : Faille XSS sur le champ site du formulaire des commentaires

## PLUXML 5.1.2 (sortie : 20/07/2011) ##

BUG Gestionnaire de medias : upload de fichiers impossible dans un sous-dossier
BUG Gestionnaire de medias : problèmes de génération des vignettes pour des images dans des sous-dossiers existants
BUG Modification article : en cas de probleme d'écriture du fichier xml, l'article est supprimé
BUG Gestion des pages statiques, catégories, utilisateurs : en cas de probleme d'écriture du fichier xml, les données affichées susceptibles d'être fausses
BUG Gestion des plugins : Liste déroulante du haut sans action
BUG Page de mise à jour : mauvais fichiers css chargés
BUG plxShow::lastArtList : suppression de l'affichage des entités html dans #artChapo
BUG Lors de l'édition d'un article, le menu "Nouvel article" sélectionné dans la sidebar de l'administration
BUG plxUtils:rel2abs : corrections diverses sur la réécriture d'url (contribution de Elessar)
BUG Impossible de publier un article avec une année de publication inférieure à 2000
BUG Mauvais classe active pour la sélection dans la liste des archives de la sidebar
BUG Theme pas défaut : lien "Haut de page" ne fonctionne pas
BUG class du lien "Blog" active/noactive (contribution de danielsan)
[+] Ajout du tri des articles et catégories par ordre alphabétique (contribution de Thomas Mur)
[+] Gestionnaire de médias : optimisation temps d'affichage de la page
[+] Suppression indexation des moteurs de recherches : pages administration et mise à jour
[+] Changement du nom des hooks AdminCategoriePrepend, AdminCategorieTop, AdminCategorie, AdminCategorieFoot en AdminCategoryPrepend, AdminCategoryTop, AdminCategory, AdminCategoryFoot
[+] Ajout des hooks : AdminSettingsDisplayTop, AdminSettingsDisplay, AdminSettingsDisplayFoot
[+] Ajout des hooks : AdminSettingsAdvancedTop, AdminSettingsAdvanced, AdminSettingsAdvancedFoot
[+] Ajout des hooks : AdminSettingsBaseTop, AdminSettingsBase, AdminSettingsBaseFoot
[+] Ajout des hooks : AdminSettingsEdittplTop, AdminSettingsEdittpl, AdminSettingsEdittplTop
[+] Ajout des hooks : AdminUsersTop, AdminUsersFoot
[+] Ajout des hooks : AdminMediasUpload, AdminSettingsInfos
Sécurité: meilleur contrôle de l'url d'un site dans plxUtils::checkSite (faille XSS possible)
Traduction fr: coquille dans L_NBALLCOM

## PLUXML 5.1.1 (sortie : 01/07/2011) ##

[+] Traduction polonaise
[+] Traduction espagnole
[+] Traduction allemande
[+] Traduction portugaise
[+] Traduction russe
[+] Traduction roumaine
[+] Traduction néerlandaise
[+] Nouveau thème par défaut
[+] Nouvelle interface d'administration
[+] Nouveau gestionnaire de médias
[+] Prévisualisation des articles coté visiteur avec le thème du site
[+] plxToolbar: smilies conforme xHTML 1.0 Strict par ajout de la balise alt
[+] Si metas d'une page statique, d'un article ou d'une catégorie vides, affichage des metas globaux si renseignés
[+] Agrandissement de la zone url des pages statiques à 255 caractères
[+] plxShow::artCat - ajout du paramètre separator, permettant de choisir le caractere entre chaque catégorie affichée (par défaut ,)
[+] plxShow::artTags - ajout du paramètre separator, permettant de choisir le caractere entre chaque tag affiché (par défaut ,)
[+] Administration: ajout lien retour blog si page statique comme page de démarrage
[+] Ajout du hook plxMotorDemarrageNewCommentaire
[+] Ajout valeur de retour pour les hooks AdminMediasDisplayFolders, AdminMediasDisplayImages, AdminMediasDisplayDocuments
[+] plxShow: ajout de la fonction staticGroup permettant d'afficher le groupe auquel appartient la page statique.
[+] plxShow::mainTitle : ajout de class="maintitle" dans le lien du titre
[+] Ajout du meta author en mode article
[+] plxShow::lastArtList : ne supprime plus le code html, ajout parametre 'ending' = texte à ajouter en fin de ligne
[+] Création nouvel utilisateur: control de l'unicité du login et du nom d'utilisateur
[+] Création nouvelle catégorie: control de l'unicité du nom et de l'url de la page
[+] Création nouvelle page statique: control de l'unicité du titre et de l'url de la page statique
[+] Nom des miniatures modifié: fichier.ext.tb remplacé par fichier.tb.ext
[+] Ajout du meta noindex, nofollow sur la page d'identification à l'administration pour interdire l'indexation de la page par les moteurs de recherche.
[-] Suppression du caractere : dans la tradcution L_ARTCHAPO
Traduction: plxShow::artCat() chaine 'Non classé' non traduite
Traduction: Dans le fichier sidebar.php du theme, 'a dit' non traduit au niveau des derniers commentaires
Traduction: Dans le fichier header.php du theme la langue est codée en dur xml:lang="fr" lang="fr" -> ajout de la fonction plxShow::defaultLang()
BUG: Ré-encodage en ANSI des fichiers langues en/admin.php et fr/admin.php
BUG: url rewriting + plugins
BUG: Effet de bord plxShow::archList() sur la pagination
BUG: Mauvaise prise en compte des caractères accentués dans les fichiers xml créés par l'installation
BUG: Admin - Mauvaise prise en compte des caractères accentués dans le titre par défaut d'un nouvel article
BUG: Admin - Mauvaise prise en compte des caractères accentués dans le contenu par défaut du champ de recherche
BUG: Inversion des libellés des liens rss articles et commentaires dans le fichier header.php du theme par défaut
BUG: Articles publiés dans "Page d'accueil" non affichés dans les archives
BUG: Mauvais affichage du nombre de commentaire en mode article
BUG: plxShow:tagList() mauvais affichage des tags numériques
BUG: Mauvais encodage de l'url pour le téléchargement d'un fichier avec l'url rewriting activé
BUG: Pas de pagination avec des articles en page d'accueil
BUG: Problème affichage du contenu de la liste des auteurs d'un article
BUG: plxShow::artAuthorInfos : impossible d'afficher du code html. html converti en texte brut
BUG: Commentaire: adresse email de l'utilisateur connecté non renseigné quand réponse à un commentaire
BUG: Erreurs sitemap.php sur la liste d'articles + variable $array non initialisée (effet de bords avec register_globals = on)
BUG: mauvais affichage du titre du site lors de la consultation des archives
BUG: L'affichage de la description d'une catégorie ne prend pas en compte l'html
BUG: Message d'erreur lors de la création d'une miniature si la librairie GD n'est pas installée
BUG: Pas de flux RSS en mode categorie avec des articles affectés à plusieurs catégories
Sécurité: Faille injection xml
Sécurité: Protection par token des formulaires de la zone d'administration
Sécurité: Renforcement des mots de passe des utilisateurs (sha1 + salt)
Sécurité: Faille XSS dans le titre des catégories sur la page de rédaction d'un nouvel article
Sécurité: Faille Directory Listing sur les dossiers core, plugins, data et themes (ajout .htaccess + directive Options -Indexes)
Sécurité: Null byte protection
Sécurité: Vérifications diverses sur les droits du profil PROFIL_WRITER
Sécurité: Vérifications emplacement des fichiers templates utilisés dans les articles/pages statiques/categories
Sécurité: Injection d'une langue qui n'existe pas (profil.php, user.php)

## PLUXML 5.1 (sortie : 26/01/2011) ##

[+] Internationalisation de PluXml
[+] Moteur de plugins
Paramètres
[+] Parametres > Configuration de base: choix de la langues par défaut du site
[+] Parametres > Configuration de base: balise meta "description" pour l'ensemble du site
[+] Parametres > Configuration de base: balise meta "keywords" pour l'ensemble du site
[-] Parametres > Configuration de base: suppression de la sélection de l'éditeur (-> plugins)
[+] Parametres > Options d'affichage: nombre d'articles affichés par page dans les archives
[+] Parametres > Comptes utilisateurs: nouvel écran pour paramétrer les options utilisateurs
[+] Parametres > Comptes utilisateurs: nouveau champ de saisie "adresse email" pour chaque utilisateur
[+] Parametres > Comptes utilisateurs: nouveau champ de saisie "langue utilisée dans l'administration" pour chaque utilisateur
[+] Parametres > Configuration avancée: choix de l'utilisation d'un dossier images/documents différents pour chaque utilisateur
[+] Parametres > Configuration avancée: interdiction d'activer l'url rewriting si le module apache mod_rewrite non présent sur le serveur
[+] Paramètres > Pluginq : nouveau menu pour accèder à la gestion des plugins
[-] Parametres > Vérifier la version officielle: menu et écran supprimé
[+] Parametres > informations: meilleure lisibilité des informations avec des icones + controles disponibilité mod_rewrite, fonction email
[+] Parametres > informations: ajout affichage si nouvelle version de PluXml disponible
Articles
[+] Simplification du filtre de recherche
Rédaction article
[+] Ajout lien "+" pour visualiser la liste de tous les tags connus
[+] Ajout champ de saisie pour les données de la balise meta "description" concernant l'article
[+] Ajout champ de saisie pour les données de la balise meta "keywords"  concernant l'article
[+] Pas d'affichage de la sélection "Autoriser les commentaires" si le paramètre global est à non
Pages statiques
[+] Page dédiée pour configurer les options d'un page statique
[+] Ajout champ de saisie pour les données de la balise meta "description" concernant la page statiuqe
[+] Ajout champ de saisie pour les données de la balise meta "keywords"  concernant la page statique
Commentaires
[+] Possibilité de modifier le nom, le site et l'adresse email de l'auteur des commentaires (édition commentaire)
Gestionnaires de médias
[+] Interdiction d'envoyer des fichiers '.htaccess', '.phtml', '.php' sur le serveur
Catégories
[+] Page dédiée pour configurer les options d'un page statique
[+] Ajout champ de saisie pour la description de la catégorie
[+] Ajout champ de saisie pour les données de la balise meta "description" concernant la catégorie
[+] Ajout champ de saisie pour les données de la balise meta "keywords"  concernant la catégorie
Profil
[+] Ajout champ de saisie pour paramétrer son adresse email
[+] Ajout liste de sélection pour choisir la langue à utiliser dans l'administration
Administration
[+] Nouvelle mire de connexion à l'administration
[+] Effet visuel de fade out sur les messages d'informations
[+] Affichage du nombre de commentaires en ligne/hors ligne à coté du menu Commentaires dans la barre principale des menus
[+] Ajout du Profil Manager et Editeur (PROFIL_MANAGER, PROFIL_EDITOR)
				Articles	Catégories	Commentaires	Pages Statiques	Paramètres
PROFIL_ADMIN		X			X			X				X				X
PROFIL_MANAGER		X			X			X				X
PROFIL_MODERATOR	X			X			X
PROFIL_EDITOR		X			X
PROFIL_WRITER		X
Themes
[+] Theme par défaut: ajout balise title sur les n° de commentaire
[+] Theme par défaut: affichage d'un message si les commentaires sont fermés
[+] Theme par défaut: affichage de l'heure d'un commentaire
[+] Thème par défaut: ajout de rel="nofollow" sur le lien dans le footer pour accèder à l'administration
[+] plxShow::lastArtList(): ajout de la variable #chapo pour afficher le chapo des articles
[+] plxShow::catList($extra,$format,$include,$exclude): ajout des paramètres $include et $exclude pour filtrer l'affichage des catégories
[+] plxShow::archList(): ajout des variables #archives_month, #archives_year pour n'afficher que le mois et l'année des archives
[+] plxShow::meta($meta): affiche les balises metas "descrition" et "keywords"
[+] plxShow::catDescription()(): affiche la description des catégories
[+] plxShow::artAuthorEmail(): affiche l'adresse email de l'auteur d'un article
[+] plxShow::lang($msg): affiche une clé de traduction associée à un thème dans la langue courante
[+] plxShow::getLang($msg): renvoie une clé de traduction associée à un thème dans la langue courante
[-] Theme par defaut: suppression des liens de syndication Atom
Améliorations
[+] plxUtils:checkSite(): meilleur controle de l'url d'un site
[+] Remplacement des liens de type href="#" par href="javascript:void(0)"
[+] Echappement des cdata écrits dans les fichiers xml
[+] Ajout attribut for dans les balises <label>
[+] Nettoyage des headers html et suppression de la mise en cache sur les pages d'installation et d'identification
[+] Protection du listing du contenu de certains répertoires avec un fichier index.html vide
[+] Gestionnaire de médias: control des variables passées en paramètres de l'url
[+] Page d'installation: meilleure lisibilité des informations avec des icones + controles disponibilité mod_rewrite, fonction email
[+] Message d'avertissement si installation/mise à jour de PluXml sur un serveur PHP4
[+] Thème par défaut: amélioration css
[+] Remplacement de l'url de téléchargement ?telechargement par ?download
[+] plxUtils::sendMail(): ajout de la fonction pour envoyer des mails
[+] plxDate::dateIso2rfc822(): ajout de la fonction pour formater les dates des les flux de syndication
Flux de syndication
[+] Flux RSS 2.0
[+] Validation des flux rss w3.org
[-] Abandon des flux atom
Plugins
[+] plxToolbar: barre d'outils pour rédiger les articles
[+] plxMobile: détection des mobiles pour basculer sur le thème mobile du site
[+] plxCapchaImage: captcha à base d'image
Suppressions
[-] Suppression de la plxtoolbar en natif -> plugin
[-] Suppression de la détection des mobiles en natif -> plugin
[-] Abandon des flux de syndication Atom
Corrections
BUG: Affichage de la liste des tags: mauvais tri alphabétique des tags
BUG: Affichage de la liste des tags: prise en compte des caractères accentués dans le tri d'affichage
BUG: Url rewriting + flux rss: ajout "Options -Multiviews" dans le fichier .htaccess créé
BUG: Url rewriting + liens avec des ancres
BUG: Url rewriting + liens relatifs
BUG: Compression Gzip
BUG: Mauvaise comptabilisation des commentaires dans les thèmes
BUG: Mauvais affichage des articles en fonction du tag sélectionné en mode home
BUG: Rédaction d'un article: mauvais libellé du bouton "Enregistrer brouillon" si article associé à des catégories
BUG: Rédaction d'un article: message d'erreur non affiché en cas de saisie d'une date invalide
BUG: Thème par défaut: mauvais formatage html de l'affichage des informations sur le rédacteur plxShow->artAuthorInfos()
BUG: Mauvaise sélection du template des pages statiques si paramètre non renseigné
BUG: Pas de création de la miniature d'une image dans un sous-dossier
BUG: Effet de bord plxShow::lastArtList() sur la pagination quand la fonction est appelée dans header.php
Sécurité: faille XSS et CSRF sur la page d'identification
Sécurité: failles XSS dans l'administration, gestionnaire de médias, plxToolbar en mode plein écran

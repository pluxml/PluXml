PluXml legacy
=============
Créez un site web performant en toute simplicité et sans base de données.

[**Télécharger PluXml 5.9.0 Legacy**](http://sudwebdesign.free.fr/depot.php?script=PluXml&download) (zip)

* See source of this version (5.9.0) : [master](https://github.com/sudwebdesign/PluXml/tree/master)

### Note of version:
* It's unofficial legacy release retail & fixed mixes of masters from scratch [in reality the next of 5.8.3 legacy](https://forum.pluxml.org/discussion/comment/60115/#Comment_60115)
* Admin with PluCss + little Fix of official master branch at [#3068fca](https://github.com/pluxml/PluXml/commit/3068fca5be14b5b5a61ae941e0326297d4e27c8b)
* New const LANG system & functions core of PluXml by team at [#3068fca](https://github.com/pluxml/PluXml/commit/3068fca5be14b5b5a61ae941e0326297d4e27c8b)
* Tested with PHP 5.5 & 7.3. Maybe work with 5.3
* I use this PluXml to check if plugins work on next motor gen (& maybe ready on next official PluXml 6.0 & (probably) with little retail of admin/config style)
* The objective of this, its a PluXml work on [toile-libre.org](https://www.toile-libre.org)
* love PluCss, but maybe if the latest time of this backoffice & go to [knaCss](https://www.knacss.com/) on next 6.0 of PluXml. Why not save it & proposal to download...?

##### 3 (unofficial) new plxShowArtNavigation's hooks (maybe name change or unexist in future official release)
+		# Hook Plugins
+		if(eval($this->plxMotor->plxPlugins->callHook('plxShowArtNavigationBegin'))) return;
+		# Hook Plugins
+		if(eval($this->plxMotor->plxPlugins->callHook('plxShowArtNavigation'))) return;
+		# Hook Plugins
+		if(eval($this->plxMotor->plxPlugins->callHook('plxShowArtNavigationEnd'))) return;

#### Important*:
* when you upgrade to next 6.0 official release (save data folder)
* Change 5.9.0 to 5.8.3 (before launch 6.0 update) (recommended)
###### in data/configuration/parametres.xml
```
	<parametre name="version">5.9.0</parametre>
```
###### TO
```
	<parametre name="version">5.8.3</parametre>
```
 * Or chose 5.8.3 in version selector when update ask you.

##### *This is unnecessary if this version is officialy officialized

## Original PluXml Readme

[**Télécharger PluXml 5.8.3 officiel**](https://www.pluxml.org/download/pluxml-latest.zip) (zip)

* Official Stables versions : [v5.8.3](https://github.com/pluxml/PluXml/releases)
* dev version : [master](https://github.com/pluxml/PluXml/tree/master)

Principales caractéristiques
----------------------------

* Aucune base de données requise
* Portable sur clé USB
* Multiutilisateurs avec des niveaux d'autorisations différents
* Pages statiques, catégories, gestion des tags
* Gestion des commentaires
* Gestionnaire de médias
* Traduit en 11 langues (français, allemand, anglais, espagnol, italien, néerlandais, occitan, polonais, portugais, roumain, russe)
* Thèmes personnalisables
* Plugins
* Réécriture d'url (nécessite le module apache mod_rewrite)

Démonstration
-------------

* [Blog](https://demo.pluxml.org/)
* [Administration](https://demo.pluxml.org/core/admin/auth.php?p=/core/admin/)

Prérequis
---------

Que ce soit en local sur votre ordinateur ou sur internet, votre hébergement doit posséder les éléments suivants pour pouvoir utiliser PluXml :

* PHP 5.6 ou supérieur
* Librairie GD pour la gestion des images
* Fonction PHP d'envoi d'emails autorisée (non obligatoire)
* Le module Apache mod_rewrite activé pour utiliser la réécriture d'url (non obligatoire)

Procédure d'installation
------------------------

* Récuperez l'archive téléchargeable [sur cette page](https://www.pluxml.org/) et dézippez la à la racine de votre site
* Connectez-vous à votre site et suivez la procédure d'installation affichée à l'écran

Mise à jour d'une version existante de PluXml
---------------------------------------------

* **IMPORTANT** : Sauvegardez le dossier data de votre PluXml
* Récuperez l'archive téléchargeable sur cette page et dézippez la à la racine de votre site de manière à écraser les fichiers existants
* Connectez-vous à votre site et suivez la procédure de mise à jour affichée à l'écran


Liens
-----
* [Site officiel](https://www.pluxml.org/)
* [Forum](https://forum.pluxml.org/)
* [Thèmes et plugins](https://ressources.pluxml.org/)
* [Documentation](https://wiki.pluxml.org/)

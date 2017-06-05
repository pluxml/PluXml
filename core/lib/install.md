---
# meta-datas pour Pandoc
title:	Présentation de PluXml
author:	http://www.pluxml.org
date: \today
fontsize:	12pt
lang:	fr_FR
babel-lang:	french
# header-includes:
# 	- \\frenchsetup{StandardItemLabel, ShowOptions}
papersize:	a4
# fontfamily:	times
fontfamily:	palatino
# fontfamily:	utopia
links-as-notes:	yes
keywords: "pluxml,cms,xml,no-sql"
geometry: "margin=1.25cm, top=0.5cm, head=2.5cm, includefoot"
# abstract: "PluXml est un gestionnaire de contenus (C.M.S.)."
---

Principales caractéristiques
============================
* Aucune base de données requise
* Portable sur clé USB
* Multi-utilisateurs avec des profils d'autorisations différents
* Pages statiques, catégories, gestion des tags (mots-clés) et archivage des articles
* Gestion des commentaires
* notification par courriel
* Gestionnaire de médias
* Traduit en 11 langues (français, allemand, anglais, espagnol, italien, néerlandais, occitan, polonais, portugais, roumain, russe)
* Thème par défaut en responsive design et ajout de thèmes personnalisables
* Plugins
* Réécriture d'url (nécessite le module mod_rewrite pour Apache, ou Nginx ou Lighttp)

Pré-requis
==========
Que ce soit en local sur votre ordinateur ou sur internet, votre hébergement doit posséder les
éléments suivants pour pouvoir utiliser PluXml :

* PHP 5 ou supérieur
* Librairie GD pour la gestion des images
* Fonction PHP d'envoi d'emails autorisée (non obligatoire)
* Le module Apache mod_rewrite activé pour utiliser la réécriture d'url (non obligatoire)
* Droit en écriture pour le serveur dans le dossier hébergeant le site et le dossier des données

Installation
============
* Téléchargez l'archive pluxml-lastest.zip de la dernière version de PluXml sur le site officiel http://pluxml.org
* Installer PluXml en local sur son ordinateur
* Installer un serveur web de type AMP (Apache - MySQL - PHP). La base de données MySQL n'est pas nécessaire pour PluXml.
  Bien sûr, vous pouvez aussi utiliser Nginx ou LigHttp à la place d’Apache.
* Pour le serveur Nginx, quelques exemples de configuration sont disponibles sur le Wiki à http://wiki.pluxml.org/index.php?page=NGINX_PluXml.

Sous MAC OS
-----------
* Télécharger le logiciel MAMP sur http://www.mamp.info/en/index.html.
* Installer MAMP en copiant le dossier dans /Applications.
* Lancer le logiciel, la fenêtre de l’application s’ouvre.
* Afin d'éviter la perte de données au moment de la mise à jour de MAMP, ouvrir les préférence de MAMP et dans l'onglet Apache saisir le chemin vers le dossier Sites présent à la racine de votre compte utilisateur. Ce chemin est du type /Users/votrenom/Sites.
* Décompresser l'archive pluxml-lastest.zip précédemment téléchargée.
* Ouvrir le dossier /Sites/.
* Glisser le dossier pluxml dans ce répertoire /Sites/.
* Ouvrir votre navigateur à l’adresse suivante : http://localhost:8888/pluxml/.
* Suivre la procédure d’installation.

Sous Linux
----------
* Installer les outils LAMP : pour [Ubuntu/Debian](http:/doc.ubuntu-fr.org/lamp), [Fedora](http://doc.fedora-fr.org/wiki/LAMP) ou [Arch Linux](http://wiki.archlinux.fr/LAMP).
* Décompresser l'archive pluxml-lastest.zip précédemment téléchargée.
* Ouvrir le dossier /var/www/.
* Envoyer ou faire glisser le contenu du dossier pluxml dans le dossier de destination.
* Ouvrir votre navigateur à l’adresse : http://localhost ou http://127.0.0.1
* Suivre la procédure d’installation.

Sous Windows
------------
* Télécharger le logiciel EasyPHP sur http://www.easyphp.org/.
* Installer EasyPHP.
* Décompresser l'archive pluxml-lastest.zip précédemment téléchargée.
* Lancer EasyPHP, puis aller sur l'icône en bas à droite dans la barre de tâche.
* Cliquer-droit sur l’icône, puis allez sur Explore (ce qui ouvre le dossier www).
* Déplacez le contenu du dossier pluxml dans ce dossier www.
* Cliquez-droit sur l’icône EasyPHP, puis sur local web pour ouvrir votre navigateur.
* Suivre la procédure d’installation.

Installer PluXml sur son hébergeur
----------------------------------
* Décompresser l'archive pluxml-latest.zip sur votre ordinateur.
* Ouvrir votre logiciel de transfert FTP (Filezilla, SmartFTP, ...).
* Se connecter à votre hébergement, via votre compte FTP.
* Envoyer ou faire glisser le contenu du dossier pluxml dans le dossier de destination chez votre hébergeur (classiquement le dossier www).
* Ouvrir votre navigateur sur l’adresse de votre site.
* Suivre la procédure d’installation.
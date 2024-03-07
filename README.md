# PluXml
![Static Badge](https://img.shields.io/badge/https-pluxml.org-blue)
![GitHub all releases](https://img.shields.io/github/downloads/pluxml/pluxml/total?icon=github)
![GitHub License](https://img.shields.io/github/license/pluxml/pluxml?icon=php)
[![Mastodon](https://img.shields.io/badge/%40pluxml-6768f3?logo=mastodon&logoColor=%23ffffff)](https://hachyderm.io/@pluxml)

PluXml is a flat CMS. Build lightweight websites easily without database.

## Description

* User friendly backoffice
* Multi-users with grants levels
* Articles, pages with PHP scripts, categories, tags, users, archives on the last months or for every year.
* Comments management
* Medias manager
* Translated into 11 languages (French, German, English, Spanish, Italian, Dutch, Occitan, Polish, Portuguese, Romanian, Russian)
* Customizable themes
* Plugins
* No database required
* URL rewriting (requires Apache2 mod_rewrite module or NGinx)

## Demonstration

* [Blog](https://demo.pluxml.org/)
* [Backoffice](https://demo.pluxml.org/core/admin/auth.php?p=/core/admin/)

## Prerequisites

* PHP 5.6.34 or higher until PHP 8.1.2. PHP 7.2.5+ is required for PHPMailer.
* PHP GD library for resizing pictures and create thumbnails
* PHP XML library for parsing data files
* PHP email sending enabled (not required)
* HTTP server as Apache2 with mod_rewrite module enabled to use URL rewriting (not required), NGinx,..

## Installation

* Download the latest release in zip format from [Github](https://github.com/pluxml/PluXml/releases) or from [https://www.pluxml.org](https://www.pluxml.org/download/pluxml-latest.zip) and unzip it at the root of your site
* Connect to your site. Fill in the form for the first user as webmaster.
* Now your site in ready to serve.
* Follow the administration link at the bottom of the homepage to access to the back-office. Enjoy it !

## Update

* **IMPORTANT** : Backup your PluXml `data` folder
* Download and unzip the latest release as for a fresh installation
* Connect to your site and accept for upgrading your datas with the new version of PluXml

## Links

* [Official website](https://www.pluxml.org/)
* [Forum](https://forum.pluxml.org/)
* [Themes and plugins](https://ressources.pluxml.org/)
* [Documentation](https://wiki.pluxml.org/)
* [Support Us](https://pluxml.org/static3/support-us)
* [Repository on Github](https://github.com/pluxml/PluXml)

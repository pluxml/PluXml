<?php

if(!defined('PLX_ROOT') or !defined('PLX_SITE_LANG')) {
	exit;
}

# https://github.com/pluxml/PluXml/issues/333

/**
 * Classe plxThemes rassemblant les thèmes valides pour PluXml.
 *
 * un thème valide comprend :
 *  - un fichier infos.xml
 *  - un fichier de langue pour la langue du site, définie par la constante PLX_SITE_LANG, dans un dossier lang
 *  - un template pour afficher la homepage
 *  - un template erreur.php
 *
 * @package PLX
 * @author	Stephane F., Jean-Pierre Pourrez "bazooka07"
 **/
class plxThemes {

	public	$racineTheme;
	public	$activeTheme;
	public $homepage;
	public	$aThemes = array(); # liste des themes

	public function __construct($racineTheme, $activeTheme, $homepage='home.php') {
		$this->racineTheme = $racineTheme;
		$this->activeTheme = $activeTheme;
		$this->homepage = basename($homepage, '.php');
		$this->getThemes();
	}

	public function getThemes() {
		$homepage = preg_replace('#^(home|static|categorie)-?.*$#', '$1', $this->homepage);
		$themes = array_map(
			function($value) {
				return preg_replace('#.*/([^/]+)/infos\.xml$#', '$1', $value);
			},
			array_filter(
				glob($this->racineTheme . '*/infos.xml'),
				function($value) use($homepage) {
					$theme = preg_replace('#infos\.xml$#', '', $value);
					if(preg_match('#/mobile\.[\w-]*/$#', $theme)) {
						return false;
					}

					foreach(
						array(
							'lang/' . PLX_SITE_LANG,
							'erreur',
						) as $f
					) {
						if(!file_exists($theme . $f . '.php')) {
							return false;
						}
					}

					$files = glob($theme . $homepage . '*.php');
					if(empty($files)) {
						return false;
					}

					return true;
				}
			)
		);

		# tri par ordre alphabétique naturel et "sans casse" des dossiers de thème
		usort($themes, function($a, $b) {
			return strnatcasecmp($a, $b);
		});
		if(in_array($this->activeTheme, $themes)) {
			$themes = array_unique(array_merge(
				array($this->activeTheme),
				$themes
			));
		}

		foreach($themes as $t) {
			$this->aThemes[$t] = $t;
		}
	}

	public function getImgPreview($theme) {
		if(!in_array($theme, $this->aThemes)) {
			return '';
		}

		$src = PLX_CORE . 'admin/theme/images/theme.png';
		foreach(array('png', 'jpg', 'jpeg', 'gif', 'webp') as $ext) {
			$filename = $this->racineTheme . $theme . '/preview.' . $ext;
			if(file_exists($filename)) {
				$src = $filename;
				break;
			}
		}

		$current = ($theme == $this->activeTheme) ? ' current' : '';
		return '<img class="img-preview' . $current . '" src="' . $src . '" alt="" />';
	}

	public function getInfos($theme) {
		if(!in_array($theme, $this->aThemes)) {
			return false;
		}

		$filename = $this->racineTheme . $theme . '/infos.xml';
		if(!is_file($filename)) {
			return false;
		}

		$data = implode('', file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $data, $values, $iTags);
		xml_parser_free($parser);
		// unset($parser);

		$aInfos = array(
			'title' => isset($iTags['title']) ? plxUtils::getTagValue($iTags['title'], $values, $theme) : $theme,
			'filemtime' => date('Y-m-d\TH:i', filemtime($filename)),
		);
		foreach(array('author', 'version', 'date', 'site', 'description') as $k) {
			$aInfos[$k] = isset($iTags[$k]) ? trim(plxUtils::getTagValue($iTags[$k], $values)) : '';
		}
		if(empty($aInfos['date'])) {
			$aInfos['date'] = date('d/m/Y', filemtime($filename));
		}

		return $aInfos;
	}
}

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
		$homepage = $this->homepage;
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
							$homepage,
							'lang/' . PLX_SITE_LANG,
							'erreur',
						) as $f
					) {
						if(!file_exists($theme . $f . '.php')) {
							return false;
						}
					}

					return true;
				}
			)
		);

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

		$aInfos = array();
		$filename = $this->racineTheme.$theme.'/infos.xml';
		if(is_file($filename)){
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			$aInfos = array(
				'title'			=> (isset($iTags['title']) AND isset($values[$iTags['title'][0]]['value']))?$values[$iTags['title'][0]]['value']:'',
				'author'		=> (isset($iTags['author']) AND isset($values[$iTags['author'][0]]['value']))?$values[$iTags['author'][0]]['value']:'',
				'version'		=> (isset($iTags['version']) AND isset($values[$iTags['version'][0]]['value']))?$values[$iTags['version'][0]]['value']:'',
				'date'			=> (isset($iTags['date']) AND isset($values[$iTags['date'][0]]['value']))?$values[$iTags['date'][0]]['value']:'',
				'site'			=> (isset($iTags['site']) AND isset($values[$iTags['site'][0]]['value']))?$values[$iTags['site'][0]]['value']:'',
				'description'	=> (isset($iTags['description']) AND isset($values[$iTags['description'][0]]['value']))?$values[$iTags['description'][0]]['value']:'',
			);
		}
		return $aInfos;
	}
}

<?php

/**
 * Gestion des themes
 *
 * @package PLX
 * @author	Stephane F
 **/

include_once __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_themes.php');
	exit;
}

class plxThemes {

	public	$racineTheme;
	public	$activeTheme;
	public	$aThemes = array(); # liste des themes

	public function __construct($racineTheme, $activeTheme) {
		$this->racineTheme = $racineTheme;
		$this->activeTheme = $activeTheme;
		$this->getThemes();
	}

	public function getThemes() {
		# on mets le theme actif en début de liste
		if(is_dir($this->racineTheme.$this->activeTheme))
			$this->aThemes[$this->activeTheme] = $this->activeTheme;
		# liste des autres themes dispos
		$files = plxGlob::getInstance($this->racineTheme, true);

		if($styles = $files->query("/[a-z0-9-_\.\(\)]+/i", "", "sort")) {
			foreach($styles as $k=>$v) {
				if(is_file($this->racineTheme.$v.'/infos.xml')) {
					if(substr($v,0,7) != 'mobile.' AND $v!=$this->activeTheme)
						$this->aThemes[$v] = $v;
				}
			}
		}
	}

	public function getImgPreview($theme) {
		$img='';
		if(is_file($this->racineTheme.$theme.'/preview.png'))
			$img=$this->racineTheme.$theme.'/preview.png';
		elseif(is_file($this->racineTheme.$theme.'/preview.jpg'))
			$img=$this->racineTheme.$theme.'/preview.jpg';
		elseif(is_file($this->racineTheme.$theme.'/preview.gif'))
			$img=$this->racineTheme.$theme.'/preview.gif';

		$current = $theme == $this->activeTheme ? ' current' : '';
		if($img=='')
			return '<img class="img-preview'.$current.'" src="'.PLX_CORE.'admin/theme/images/theme.png" alt="" />';
		else
			return '<img class="img-preview'.$current.'" src="'.$img.'" alt="" />';
	}

	public function getInfos($theme) {
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

$plxThemes = new plxThemes(PLX_ROOT.$plxAdmin->aConf['racine_themes'], $plxAdmin->aConf['style']);

# Call the views (mainView must be the last to be called, because it's include the masterTemplate)
include_once __DIR__ .'/views/configThemesView.php';
include_once __DIR__ .'/views/mainView.php';
<?php

/**
 * Gestion des themes
 *
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

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

	public function __construct($racineTheme, $activeTheme, $lang=DEFAULT_LANG) {
		$this->racineTheme = $racineTheme;
		$this->activeTheme = $activeTheme;
		$this->lang = $lang;
		$this->getThemes();
	}

	public function getThemes() {
		# on met le theme actif en début de liste
		$activeTheme = $this->activeTheme;
		if(is_dir($this->racineTheme.$activeTheme))
			$this->aThemes[] = $activeTheme;
		else
			$activeTheme = false;
		$racineThemes = $this->racineTheme;
		$autresThemes = array_filter(
			array_map(
				function($item) use($racineThemes) {
					return preg_replace('@^'.$racineThemes.'([^/]*)/css/$@', '\1', $item);
				},
				glob($racineThemes.'*/css/', GLOB_ONLYDIR)
			),
			function($item) use($activeTheme) {
				return (($item !== $activeTheme) and !preg_match('@^mobile\..*$@i', $item));
			}
		);
		if(!empty($autresThemes))
			$this->aThemes = array_merge($this->aThemes, $autresThemes);

	}

	public function getImgPreview($theme) {
		$src = 	PLX_CORE.'admin/theme/images/theme.png';
		$current = '';
		foreach(explode(' ', 'png jpg gif') as $ext) {
			$filename = $this->racineTheme.$theme.'/preview.'.$ext;
			if(is_file($filename)) {
				$src = $filename;
				break;
			}
		}

		$current = $theme == $this->activeTheme ? ' current' : '';
		return <<< EOT
<img class="img-preview$current" src="$src" alt="preview" />
EOT;
	}

	public function getInfos($theme) {
		$aInfos = array('folder' => $theme);
		$filename = $this->racineTheme.$theme.'/infos.xml';
		if(is_file($filename)){
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			foreach(explode(' ', 'title author version date site description') as $field) {
				if(isset($iTags[$field]) AND !empty($values[$iTags[$field][0]]['value'])) {
					$aInfos[$field] = $values[$iTags[$field][0]]['value'];
				}
			}
		}
		return $aInfos;
	}

	private function _strInfos($theme) {
		$aInfos = $this->getInfos($theme);
		$lines = array();
		foreach(explode(' ', 'title date version site author description folder') as $field) {
			if(!empty($aInfos[$field])) {
				switch($field) {
					case 'title':
					  $lines[] = '<strong>'.ucFirst($aInfos[$field]).'</strong>';
					  break;
					case 'date':
						if(!empty($aInfos['version']))
							$lines[] = L_PLUGINS_VERSION.' : <strong>'.$aInfos['version'].'</strong> - '.$aInfos[$field];
						else
							$lines[] = L_PLUGINS_VERSION.' : <strong>'.$aInfos[$field].'</strong>';
						break;
					case 'version':
						if(empty($aInfos['date']))
							$lines[] = L_PLUGINS_VERSION.' : <strong>'.$aInfos[$field].'</strong>';
						break;
					case 'site':
						$caption = (!empty($aInfos['author'])) ? $aInfos['author'] : $aInfos['site'];
						$prefix = (!empty($aInfos['author'])) ?  L_PLUGINS_AUTHOR : 'Site';
						$lines[] = $prefix.' : <a href="'.$aInfos[$field].'">'.$caption.'</a>';
						break;
					case 'author':
						if(empty($aInfos['site']))
							$lines[] = L_PLUGINS_AUTHOR.' : '.$aInfos['author'];
						break;
					case 'description':
						$lines[] = $aInfos[$field];
						break;
					case 'folder':
						$title = (empty($aInfos['title'])) ? '<strong>'.ucFirst($aInfos[$field]).'</strong>'."<br />\n" : '';
						$lines[] =  $title.L_MEDIAS_FOLDER.' : '.$aInfos[$field];
						break;
				}
			}
		}
		return (implode("<br />\n", $lines));
	}

	private function _printTheme($theme) {
		$checked = ($theme == $this->activeTheme) ? ' checked="checked"' : '';
		$preview = $this->getImgPreview($theme);
		$aInfos = $this->_strInfos($theme);
		$filename = $this->racineTheme.$theme.'/lang/'.$this->lang.'-help.php';
		if(is_file($filename)) {
			echo "<!--\n$filename\n-->\n";
			$href = 'parametres_help.php?help=theme&page='.urlencode($theme);
			$help =  "<br />\n".'<a title="'.L_HELP_TITLE.'" href="'.$href.'">'.L_HELP.'</a>';
		} else
			$help = '';
		echo <<< EOT
				<tr>
					<td><input type="radio" id="id_$theme" name="style" value="$theme"$checked></td>
					<td><label for="id_$theme">$preview</label></td>
					<td>$aInfos$help</td>
				</tr>
EOT;
	}

	public function printThemes() {
		if(!empty($this->aThemes)) {
			foreach($this->aThemes as $theme) {
				$this->_printTheme($theme);
			}
			return true;
		} else
			return false;
	}

}

# On inclut le header
include(dirname(__FILE__).'/top.php');
$plxThemes = new plxThemes(PLX_ROOT.$plxAdmin->aConf['racine_themes'], $plxAdmin->aConf['style'], $plxAdmin->aConf['default_lang']);

?>
<form action="parametres_themes.php" method="post" id="form_settings">

	<div class="inline-form action-bar">
		<h2><?php echo L_CONFIG_VIEW_SKIN_SELECT ?> </h2>
		<p><?php echo L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
		<input type="submit" value="<?php echo L_CONFIG_THEME_UPDATE ?>" />
		&nbsp;&nbsp;&nbsp;
		<input onclick="window.location.assign('parametres_edittpl.php');return false" type="submit" value="<?php echo L_CONFIG_VIEW_FILES_EDIT_TITLE ?>" />
	</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="themes-table" class="full-width">
			<thead>
				<tr>
					<th colspan="2"><?php echo L_THEMES ?></th>
					<th style="width: 100%;">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
<?php
if(!$plxThemes->printThemes()) { ?>
				<tr>
					<td colspan="2" class="center"><?php echo L_NONE1; ?></td>
				</tr>
<?php
}
?>
			</tbody>
		</table>
	</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplay')) # Hook Plugins ?>
<?php echo plxToken::getTokenPostMethod() ?>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
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
				if(substr($v,0,7) != 'mobile.' AND $v!=$this->activeTheme)
					$this->aThemes[$v] = $v;
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

# On inclut le header
include(dirname(__FILE__).'/top.php');

$plxThemes = new plxThemes(PLX_ROOT.$plxAdmin->aConf['racine_themes'], $plxAdmin->aConf['style']);

?>
<form action="parametres_themes.php" method="post" id="form_settings">

	<div class="inline-form action-bar">
		<h2><?php echo L_CONFIG_VIEW_SKIN_SELECT ?> </h2>
		<p><?php echo L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
		<input type="submit" value="<?php echo L_CONFIG_VIEW_UPDATE ?>" />
		&nbsp;&nbsp;&nbsp;
		<input onclick="window.location.assign('parametres_edittpl.php');return false" type="submit" value="<?php echo L_CONFIG_VIEW_FILES_EDIT_TITLE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="themes-table" class="full-width">
			<thead>
				<tr>
					<th colspan="2"><?php echo L_THEMES ?></th>
					<th style="width: 100%">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($plxThemes->aThemes) {
					$num=0;
					foreach($plxThemes->aThemes as $theme) {
						echo '<tr>';
						# radio
						$checked = $theme==$plxAdmin->aConf['style'] ? ' checked="checked"' : '';
						echo '<td><input'.$checked.' type="radio" name="style" value="'.$theme.'" /></td>';
						# img preview
						echo '<td>'.$plxThemes->getImgPreview($theme).'</td>';
						# theme infos
						echo '<td class="wrap" style="vertical-align:top">';
							if($aInfos = $plxThemes->getInfos($theme)) {
								echo '<strong>'.$aInfos['title'].'</strong><br />';
								echo 'Version : <strong>'.$aInfos['version'].'</strong> - ('.$aInfos['date'].')<br />';
								echo L_PLUGINS_AUTHOR.' : '.$aInfos['author'].' - <a href="'.$aInfos['site'].'" title="">'.$aInfos['site'].'</a>';
								echo '<br />'.$aInfos['description'].'<br />';
							} else {
								echo '<strong>'.$theme.'</strong>';
							}
							# lien aide
							if(is_file(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$theme.'/lang/'.$plxAdmin->aConf['default_lang'].'-help.php'))
								echo '<a title="'.L_HELP_TITLE.'" href="parametres_help.php?help=theme&amp;page='.urlencode($theme).'">'.L_HELP.'</a>';

						echo '</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="2" class="center">'.L_NONE1.'</td></tr>';
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
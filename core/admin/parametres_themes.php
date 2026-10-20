<?php

/**
 * Gestion des themes
 *
 * @package PLX
 * @author	Stephane F
 **/

require 'prepend.php';

if(!isset($plxAdmin)) {
	redirectLogin();
}

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($_POST);
	reloadPage();
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

		if($styles = $files->query('#^(?!mobile\.)[\w\.\(\)-]+#i', '', 'sort')) {
			foreach($styles as $k=>$v) {
				if(is_file($this->racineTheme.$v.'/infos.xml')) {
					if($v != $this->activeTheme) {
						$this->aThemes[$v] = $v;
					}
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
			return '<img class="img-preview'.$current.'" src="theme/images/theme.png" alt="" />';
		else
			return '<img class="img-preview'.$current.'" src="'.$img.'" alt="" />';
	}

	public function getInfos($theme) {
		$aInfos = array();
		$filename = $this->racineTheme.$theme.'/infos.xml';
		if(is_file($filename)){
			$data = file_get_contents($filename);
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			if(PHP_VERSION_ID >= 80000) {
				unset($parser);
			} else {
				xml_parser_free($parser);
			}
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
require 'top.php';

$plxThemes = new plxThemes(PLX_ROOT . $plxAdmin->aConf['racine_themes'] , $plxAdmin->aConf['style']);

?>
<form method="post" id="form_settings">
	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_VIEW_SKIN_SELECT ?> </h2>
		<p><?= L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
		<input type="submit" value="<?= L_CONFIG_THEME_UPDATE ?>" />
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input onclick="window.location.assign('parametres_edittpl.php');return false" type="submit" value="<?= L_CONFIG_VIEW_FILES_EDIT_TITLE ?>" />
	</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>
	<div class="scrollable-table">
		<table id="themes-table" class="full-width">
			<thead>
				<tr>
					<th colspan="3"><?= L_THEMES ?></th>
				</tr>
			</thead>
			<tbody>
<?php
				if($plxThemes->aThemes) {
					foreach($plxThemes->aThemes as $id=>$theme) {
						$isHelpEnabled = is_file(PLX_ROOT . $plxAdmin->aConf['racine_themes'] . $theme . '/lang/' . $plxAdmin->aConf['default_lang'] . '-help.php');
?>
				<tr>
					<td><input type="radio" id="<?= $id ?>" name="style" value="<?= $theme ?>" <?php if($theme == $plxAdmin->aConf['style']) { echo 'checked'; } ?>/></td>
					<td><label for="<?= $id ?>"><?= $plxThemes->getImgPreview($theme) ?></label></td>
					<td class="wrap" style="vertical-align:top">
<?php
						if($aInfos = $plxThemes->getInfos($theme)) {
?>
						<strong><?= $aInfos['title'] ?></strong><br />
						Version : <strong><?= $aInfos['version'] ?></strong> - (<?= $aInfos['date'] ?>)<br />
						<?= L_PLUGINS_AUTHOR ?> : <?= $aInfos['author'] ?> - <a href="<?= $aInfos['site'] ?>"><?= $aInfos['site'] ?></a><br />
						<?= $aInfos['description'] ?><br />
<?php
						} else {
?>
						<strong><?= $theme ?></strong><br />
<?php
						}
						# lien aide
						if($isHelpEnabled) {
?>
						<a title="<?= L_HELP_TITLE ?>" href="parametres_help.php?help=theme&page=<?= urlencode($theme) ?>"><?= L_HELP ?></a>
<?php
						}
?>
					</td>
				</tr>
<?php
					}
				} else {
?>
				<tr>
					<td colspan="3" class="center"><?= L_NONE1 ?></td>
				</tr>
<?php
				}
?>
			</tbody>
		</table>
	</div>
<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplay')) # Hook Plugins ?>
	<?= plxToken::getTokenPostMethod() ?>
</form>
<script>
	(function() {
		'use strcit';
		const myForm = document.getElementById('form_settings');
		if(myForm) {
			myForm.addEventListener('change', (ev) => {
				if(ev.target.name == 'style') {
					ev.preventDefault();
					ev.currentTarget.submit();
				}
			});
		}
	})();
</script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));

# On inclut le footer
require 'foot.php';

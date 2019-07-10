<?php 
$adminTitle = L_CONFIG_VIEW_SKIN_SELECT;
$inputChecked = true;
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>

<?php ob_start(); ?>

<form action="parametres_themes.php" method="post" id="form_settings">

	<div class="inline-form admin-title">
		<p><?php echo L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
		<input type="submit" value="<?php echo L_CONFIG_THEME_UPDATE ?>" />
		&nbsp;&nbsp;&nbsp;
		<input onclick="window.location.assign('parametres_edittpl.php');return false" type="submit" value="<?php echo L_CONFIG_VIEW_FILES_EDIT_TITLE ?>" />
	</div>

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
?>

<?php $mainContent = ob_get_clean(); ?>
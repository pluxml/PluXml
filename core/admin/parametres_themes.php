<?php

/**
 * Gestion des themes
 *
 * @package PLX
 * @author	Stephane F
 **/

include 'prepend.php';

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

# On inclut le header
include 'top.php';

$homestatic = $plxAdmin->aConf['homestatic'];
$homepage = empty($homestatic) ? $plxAdmin->aConf['hometemplate'] : $plxAdmin->aStats[$homestatic]['template'];
$plxThemes = new plxThemes(PLX_ROOT.$plxAdmin->aConf['racine_themes'], $plxAdmin->aConf['style'], $homepage);

?>
<form action="parametres_themes.php" method="post" id="form_settings">

	<div class="inline-form action-bar">
		<h2><?= L_CONFIG_VIEW_SKIN_SELECT ?></h2>
		<p><?php printf(L_CONFIG_VIEW_PLUXML_RESSOURCES, PLX_RESSOURCES_THEMES_LINK); ?></p>
		<input type="submit" value="<?= L_CONFIG_THEME_UPDATE ?>" />
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input onclick="window.location.assign('parametres_edittpl.php');return false" type="submit" value="<?= L_CONFIG_VIEW_FILES_EDIT_TITLE ?>" />
	</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="themes-table" class="full-width">
			<thead>
				<tr>
					<th colspan="2"><?= L_THEMES ?></th>
					<th>
						<span>Tri par</span>
						<select id="sortSelect">
							<option value="id"><?= L_CANCEL ?></option>
							<option value="title"><?= L_ARTICLE_TITLE ?></option>
							<option value="filemtime"><?= L_ARTICLE_LIST_DATE ?> (infos.xml)</option>
							<option value="site"><?= L_COMMENT_SITE_FIELD ?></option>
						</select>
					</th>
				</tr>
			</thead>
			<tbody>
<?php
				if($plxThemes->aThemes) {
					$count = 0;
					foreach($plxThemes->aThemes as $theme) {
						# radio
						$checked = ($theme == $plxAdmin->aConf['style']) ? ' checked="checked"' : '';
						$id = str_pad($count, 3, '0', STR_PAD_LEFT);
						$aInfos = $plxThemes->getInfos($theme);
?>
				<tr data-id="<?= $id ?>" data-filemtime="<?= $aInfos['filemtime'] ?>" data-site="<?= trim($aInfos['site']) ?>" data-title="<?= strtolower(trim($aInfos['title'])) ?>">
					<td><input<?= $checked ?> type="radio" id="style-<?= $id ?>" name="style" value="<?= $theme ?>" /></td>
					<td><label for="style-<?= $id ?>"><?= $plxThemes->getImgPreview($theme) ?></label></td>
					<td class="wrap" style="vertical-align:top">
<?php
						# theme infos
						if($aInfos) {
?>
						<strong><?= $aInfos['title'] ?></strong><br />
						Version : <strong><?= $aInfos['version'] ?></strong> - (<em><?= $aInfos['date'] ?></em>)<br />
						<?= L_PLUGINS_AUTHOR ?> : <?= $aInfos['author'] ?>
<?php
							if(!empty($aInfos['site'])) {
?>
								- <a href="<?= $aInfos['site'] ?>" title=""><?= $aInfos['site'] ?></a>
<?php
							}
?>
						<br /><?= $aInfos['description'] ?><br />
<?php
						} else {
?>
						<strong><?= $theme ?></strong>
<?php
						}

						# lien aide
						if(is_file(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$theme.'/lang/'.$plxAdmin->aConf['default_lang'].'-help.php')) {
?>
						<a title="<?= L_HELP_TITLE ?>" href="parametres_help.php?help=theme&amp;page=<?= urlencode($theme) ?>"><?= L_HELP ?></a>
<?php
						}
?>
					</td>
				</tr>
<?php
						$count++;
					}
				} else {
?>
				<tr>
					<td colspan="2" class="center"><?= L_NONE1 ?></td>
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

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminThemesDisplayFoot'));
?>
<script>
	(function() {
		const select = document.getElementById('sortSelect');
		if(select) {
			select.addEventListener('change', function(ev) {
				const choice = select.value;
				const table = document.getElementById('themes-table');
				const tBody = table.tBodies[0];
				const sortedRows = Array.from(tBody.rows).sort(function(row1, row2) {
					if(choice == 'filemtime') {
						return row2.dataset.filemtime.localeCompare(row1.dataset.filemtime); // reverse date
					}
					if(row1.dataset[choice].length == 0) {
						return 1;
					}
					if(row2.dataset[choice].length == 0) {
						return -1;
					}
					return row1.dataset[choice].localeCompare(row2.dataset[choice]);
				});
				sortedRows.forEach(function(row) {
					tBody.appendChild(row);
				});

				table.scrollIntoView();
			});
		}
	})();
</script>
<?php

# On inclut le footer
include 'foot.php';

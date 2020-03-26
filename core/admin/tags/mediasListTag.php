<?php
/**
 * Medias list mode tag
 * @author	Pedro "P3ter" CADETE"
 **/

use Pluxml\PlxDate;
use Pluxml\PlxUtils;

?>

<div style="clear:both" class="scrollable-table">
	<table id="medias-table" class="full-width">
		<thead>
			<tr>
				<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idFile[]')" /></th>
				<th>&nbsp;</th>
				<th><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[0].sort.value='<?= $sort_title ?>';document.forms[0].submit();return true;"><?= L_MEDIAS_FILENAME ?></a></th>
				<th><?= L_MEDIAS_EXTENSION ?></th>
				<th><?= L_MEDIAS_FILESIZE ?></th>
				<th><?= L_MEDIAS_DIMENSIONS ?></th>
				<th><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[0].sort.value='<?= $sort_date ?>';document.forms[0].submit();return true;"><?= L_MEDIAS_DATE ?></a></th>
			</tr>
		</thead>
		<tbody>
			<?php
				# Initialisation de l'ordre
				$num = 0;
				# Si on a des fichiers
				if($plxMedias->aFiles) {
					foreach($plxMedias->aFiles as $v) { # Pour chaque fichier
						$isImage = in_array(strtolower($v['extension']), $plxMedias->img_supported);
						$title = pathinfo($v['name'], PATHINFO_FILENAME);
						echo '<tr>';
						echo '<td><input type="checkbox" name="idFile[]" value="'.$v['name'].'" /></td>';
						echo '<td class="icon">';
							if(is_file($v['path']) AND $isImage) {
								echo '<a class="overlay" title="'.$title.'" href="'.$v['path'].'"><img alt="'.$title.'" src="'.$v['.thumb'].'" class="thumb" /></a>';
							}
							else
								echo '<img alt="" src="'.$v['.thumb'].'" class="thumb" />';
						echo '</td>';
						echo '<td>';
							echo '<a class="imglink" onclick="'."this.target='_blank'".'" title="'.$title.'" href="'.$v['path'].'">'.$title.$v['extension'].'</a>';
							echo '<div onclick="copy(this, \''.str_replace(PLX_ROOT, '', $v['path']).'\')" title="'.L_MEDIAS_LINK_COPYCLP.'" class="ico">&#8629;<div>'.L_MEDIAS_LINK_COPYCLP_DONE.'</div></div>';
							echo '<div id="btnRenameImg'.$num.'" onclick="ImageRename(\''.$v['path'].'\')" title="'.L_RENAME_FILE.'" class="ico">&perp;</div>';
							echo '<br />';
							$href = PlxUtils::thumbName($v['path']);
							if($isImage AND is_file($href)) {
								echo L_MEDIAS_THUMB.' : '.'<a onclick="'."this.target='_blank'".'" title="'.$title.'" href="'.$href.'">'.PlxUtils::strCheck(basename($href)).'</a>';
								echo '<div onclick="copy(this, \''.str_replace(PLX_ROOT, '', $href).'\')" title="'.L_MEDIAS_LINK_COPYCLP.'" class="ico">&#8629;<div>'.L_MEDIAS_LINK_COPYCLP_DONE.'</div></div>';
							}
						echo '</td>';
						echo '<td>'.strtoupper($v['extension']).'</td>';
						echo '<td>';
							echo PlxUtils::formatFilesize($v['filesize']);
							if($isImage AND is_file($href)) {
								echo '<br />'.PlxUtils::formatFilesize($v['thumb']['filesize']);
							}
						echo '</td>';
						$dimensions = '&nbsp;';
						if($isImage AND (isset($v['infos']) AND isset($v['infos'][0]) AND isset($v['infos'][1]))) {
							$dimensions = $v['infos'][0].' x '.$v['infos'][1];
						}
						if($isImage AND is_file($href)) {
							$dimensions .= '<br />'.$v['thumb']['infos'][0].' x '.$v['thumb']['infos'][1];
						}
						echo '<td>'.$dimensions.'</td>';
						echo '<td>'.PlxDate::formatDate(PlxDate::timestamp2Date($v['date'])).'</td>';
						echo '</tr>';
					}
				}
				else echo '<tr><td colspan="7" class="center">'.L_MEDIAS_NO_FILE.'</td></tr>';
			?>
		</tbody>
	</table>
</div>
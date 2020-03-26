<?php
/**
 * Medias list mode tag
 * @author	Pedro "P3ter" CADETE"
 **/
?>

<?php
	if($plxMedias->aFiles) {
		foreach($plxMedias->aFiles as $v) { # Pour chaque fichier
			$isImage = in_array(strtolower($v['extension']), $plxMedias->img_supported);
			$title = pathinfo($v['name'], PATHINFO_FILENAME);
			if(is_file($v['path']) AND $isImage) {
				echo '<a class="overlay" title="'.$title.'" href="'.$v['path'].'"><img alt="'.$title.'" src="'.$v['.thumb'].'" class="thumb" /></a>';
			}
			else
				echo '<img alt="" src="'.$v['.thumb'].'" class="thumb" />';
		}
	}
	else echo L_MEDIAS_NO_FILE;
?>
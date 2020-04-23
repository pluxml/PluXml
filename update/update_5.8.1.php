<?php
/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * Release on on 7 Jan 2020
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE, J.P. Pourrez
 **/
class update_5_8_1 extends plxUpdate{

	public function step1() {
		$root = dirname(PLX_CONFIG_PATH);
		$folder = $root . '/templates';
		if(!is_dir(PLX_ROOT . $folder)) {
			@mkdir(PLX_ROOT . $folder, 0755, true);
?>
		<p><?php printf(L_UPDATE_NEW_FOLDER, $folder); ?></p>
<?php
		}
		return true;
	}
}

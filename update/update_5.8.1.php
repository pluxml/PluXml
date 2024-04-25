<?php
/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
class update_5_8_1 extends plxUpdate {
	const NEW_FIELDS = array(
			'XMLFILE_CATEGORIES'	=> array(
				'tag'		=> 'categorie',
				'search'	=> '#</thumbnail>#',
				'new_tags'	=> <<< EOT
		<thumbnail></thumbnail>
		<thumbnail_title></thumbnail_title>
		<thumbnail_alt></thumbnail_alt>
EOT,
			),
			'XMLFILE_USERS'			=> array(
				'tag'		=> 'user',
				'search'	=> '#</password_token>#',
				'new_tags'	=> <<< EOT
		<password_token></password_token>
		<password_token_expiry></password_token_expiry>
EOT,
			),
			'XMLFILE_STATICS'		=> array(
				'tag'		=> 'statique',
				'search'	=> '#</date_creation>#',
				'new_tags'	=> <<< EOT
		<date_creation></date_creation>
		<date_update></date_update>
EOT,
			),
		);

	/**
	 * Update category file with new fields thumbnail, thumbnail_title, thumbnail_alt
	 * Update users file with new fields password_token, password_token_expiry
	 *
	 * @return boolean
	 */
	public function step1() {
		foreach(self::NEW_FIELDS as $k=>$v) {
			$filename = path($k);
?>
		<li><?= L_UPDATE_FILE ?> <em><?= basename($filename) ?></em></li>
<?php
			$data = file_get_contents($filename);
			if(!preg_match($v['search'], $data)) {
				$output = preg_replace('#(\s*</' . $v['tag'] . '>)#', PHP_EOL . $v['new_tags'] . '$1', $data);
				if(empty($output) or !plxUtils::write($output, $filename)) {
?>
				<p class="error"><?= L_UPDATE_ERR_FILE ?></p>
<?php
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Create data/templates folder if is missing
	 *
	 * @return boolean
	 */
	public function step2() {
		$dest = PLX_ROOT . preg_replace('#^([\w-]+/).*#', '${1}templates', PLX_CONFIG_PATH);
		if(!is_dir($dest)) {
			@mkdir($dest, 0755, true);
		}

		# nouveaux paramètres
		return $this->updateParameters();
	}
}

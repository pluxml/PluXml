<?php
/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
class update_5_8_1 extends plxUpdate{

	private const TEMPLATES_FOLDER = 'data/templates';

	/**
	 * Update category file with new fields thumbnail, thumbnail_title, thumbnail_alt
	 * @return boolean
	 */
	public function step1() {
		$filename = path('XMLFILE_CATEGORIES');
?>
			<li><?= L_UPDATE_FILE ?> : <?= substr($filename, strlen(PLX_ROOT)) ?></li>
<?php
		$data = file_get_contents($filename);
		$tag = 'categorie';
		if(preg_match_all('{<'.$tag.'[^>]*>(.*?)</'.$tag.'>}', $data, $matches, PREG_PATTERN_ORDER)) {
			foreach($matches[0] as $match) {
				if(!preg_match('/<thumbnail>/', $match)) {
					$str = str_replace('</'.$tag.'>', '<thumbnail></thumbnail><thumbnail_title></thumbnail_title><thumbnail_alt></thumbnail_alt></'.$tag.'>', $match);
					$data = str_replace($match, $str, $data);
				}
			}
			if(!plxUtils::write($data, $filename)) {
				echo '<p class="error">' . L_UPDATE_ERR_FILE . '</p>' . PHP_EOL;
				return false;
			}
		}
		return true;
	}

	/**
	 * Update users file with new fields password_token, password_token_expiry
	 * @return boolean
	 */
	public function step2() {
		$filename = path('XMLFILE_USERS');
?>
			<li><?= L_UPDATE_FILE ?> : <?= substr($filename, strlen(PLX_ROOT)) ?></li>
<?php
		$data = file_get_contents($filename);
		$tag = 'user';
		if(preg_match_all('{<'.$tag.'[^>]*>(.*?)</'.$tag.'>}', $data, $matches, PREG_PATTERN_ORDER)) {
			foreach($matches[0] as $match) {
				if(!preg_match('/<password_token>/', $match)) {
					$str = str_replace('</'.$tag.'>', '<password_token></password_token><password_token_expiry></password_token_expiry></'.$tag.'>', $match);
					$data = str_replace($match, $str, $data);
				}
			}
			if(!plxUtils::write($data, $filename)) {
				echo '<p class="error">' . L_UPDATE_ERR_FILE . '</p>';
				return false;
			}
		}
		return true;
	}

	/**
	 * Create data/templates folder if is missing
	 * @return boolean
	 */
	public function step3() {
		if(!is_dir(PLX_ROOT . self::TEMPLATES_FOLDER)) {
?>			<li>New folder : <?= self::TEMPLATES_FOLDER ?></li>
<?php
			return mkdir(PLX_ROOT . self::TEMPLATES_FOLDER, 0755, true);
		}

		return true;
	}
}

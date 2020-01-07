<?php
/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
class update_5_8_1 extends plxUpdate{

	/**
	 * Update category file with new fields thumbnail, thumbnail_title, thumbnail_alt
	 * @return boolean
	 */
	public function step1() {
		echo L_UPDATE_FILE." (".path('XMLFILE_CATEGORIES').")<br />";
		$data = file_get_contents(path('XMLFILE_CATEGORIES'));
		$tag = 'categorie';
		if(preg_match_all('{<'.$tag.'[^>]*>(.*?)</'.$tag.'>}', $data, $matches, PREG_PATTERN_ORDER)) {
			foreach($matches[0] as $match) {
				if(!preg_match('/<thumbnail>/', $match)) {
					$str = str_replace('</'.$tag.'>', '<thumbnail><![CDATA[]]></thumbnail><thumbnail_title><![CDATA[]]></thumbnail_title><thumbnail_alt><![CDATA[]]></thumbnail_alt></'.$tag.'>', $match);
					$data = str_replace($match, $str, $data);
				}
			}
			if(!plxUtils::write($data, path('XMLFILE_CATEGORIES'))) {
				echo '<p class="error">'.L_UPDATE_ERR_FILE.'</p>';
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
		echo L_UPDATE_FILE." (".path('XMLFILE_USERS').")<br />";
		$data = file_get_contents(path('XMLFILE_USERS'));
		$tag = 'user';
		if(preg_match_all('{<'.$tag.'[^>]*>(.*?)</'.$tag.'>}', $data, $matches, PREG_PATTERN_ORDER)) {
			foreach($matches[0] as $match) {
				if(!preg_match('/<password_token>/', $match)) {
					$str = str_replace('</'.$tag.'>', '<password_token><![CDATA[]]></password_token><password_token_expiry><![CDATA[]]></password_token_expiry></'.$tag.'>', $match);
					$data = str_replace($match, $str, $data);
				}
			}
			if(!plxUtils::write($data, path('XMLFILE_CATEGORIES'))) {
				echo '<p class="error">'.L_UPDATE_ERR_FILE.'</p>';
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
		if(!is_dir(PLX_ROOT.'data/templates')) {
			@mkdir(PLX_ROOT.'data/templates',0755,true);
		}
		return true;
	}
}

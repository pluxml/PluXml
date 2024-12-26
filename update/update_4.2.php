<?php
/**
 * Classe de mise a jour pour PluXml version 4.2
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_4_2 extends plxUpdate {
	const NEW_PARAMS = array(
		'clef'			=> '',
		'miniatures_l'	=> '200',
		'miniatures_h'	=> '100',
		'tri_coms'		=> 'asc',
		'style_mobile'	=> 'mobile.defaut',
	);

	function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		return $this->updateParameters(self::NEW_PARAMS);
	}

}

<?php
/**
 * Classe de mise a jour pour PluXml version 4.2
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_4_2 extends plxUpdate{

	function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php
		return $this->updateParameters(array(
			'clef' => null,
			'miniatures_l' => '200',
			'miniatures_h' => '100',
			'tri_coms' => 'asc',
			'style_mobile' => 'mobile.defaut'
		));
	}

}


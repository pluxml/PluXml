<?php

/**
 * Classe de mise a jour pour PluXml version 4.2
 *
 * @package PLX
 * @author	Stephane F
 **/

use PluxmlUpdater\PlxUpdate;

class update_4_2 extends PlxUpdate{

	function step1() {

		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";

		$new_parameters = array(
			'clef' => null,
			'miniatures_l' => '200',
			'miniatures_h' => '100',
			'tri_coms' => 'asc',
			'style_mobile' => 'mobile.defaut'
		);
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

}
?>
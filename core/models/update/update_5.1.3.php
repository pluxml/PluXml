<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.3
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_3 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# nouveaux parametres
		$new_parameters = array(
			'images_l' => 800,
			'images_h' => 600,
		);
		# mise à jour du fichier des parametres
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

}
?>
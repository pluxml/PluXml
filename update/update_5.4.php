<?php
/**
 * Classe de mise a jour pour PluXml version 5.4
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_4 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# nouveaux parametres
		$new_parameters = array();
		$new_parameters['custom_admincss_file'] = '';
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

}
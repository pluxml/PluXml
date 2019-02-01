<?php
/**
 * Classe de mise a jour pour PluXml version 5.5
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_8 extends plxUpdate{

	# mise à jour fichier parametres.xml (récupération du mot de passe)
	public function step1() {
	    echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
	    
	    $new_parameter = array();
	    $new_parameters['lostpassword'] = '1';
	    $this->updateParameters($new_parameters);

		return true;
	}
}
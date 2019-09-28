<?php
/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
class update_5_8 extends plxUpdate{

	# mise à jour fichier parametres.xml (récupération du mot de passe)
	public function step1() {
	    echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
	    
	    $new_parameters = array();
	    $new_parameters['lostpassword'] = '1';
	    $new_parameters['smtp_activation'] = '0';
	    $new_parameters['smtp_server'] = '';
	    $new_parameters['smtp_username'] = '';
	    $new_parameters['smtp_password'] = '';
	    $new_parameters['smtp_port'] = '465';
	    $new_parameters['smtp_security'] = 'ssl';
	    $this->updateParameters($new_parameters);

		return true;
	}
}
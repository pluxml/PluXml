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
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php

		$new_parameters = array();
		$new_parameters['enable_rss'] = '1';
		$new_parameters['lostpassword'] = '1';
		$new_parameters['email_method'] = 'sendmail';
		$new_parameters['smtp_server'] = '';
		$new_parameters['smtp_username'] = '';
		$new_parameters['smtp_password'] = '';
		$new_parameters['smtp_port'] = '465';
		$new_parameters['smtp_security'] = 'ssl';
		$new_parameters['smtpOauth2_emailAdress'] = '';
		$new_parameters['smtpOauth2_clientId'] = '';
		$new_parameters['smtpOauth2_clientSecret'] = '';
		$new_parameters['smtpOauth2_refreshToken'] = '';
		$this->updateParameters ( $new_parameters );

		return true;
	}
}

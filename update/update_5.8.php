<?php
/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * Release on 5 Jan 2020
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
class update_5_8 extends plxUpdate{

	# mise à jour fichier parametres.xml (récupération du mot de passe)
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";

		echo $this->updateParameters(array(
			'enable_rss'				=> '1',
			'lostpassword'				=> '1',
			'email_method'				=> 'sendmail',
			'smtp_server'				=> '',
			'smtp_username'				=> '',
			'smtp_password'				=> '',
			'smtp_port'					=> '465',
			'smtp_security'				=> 'ssl',
			'smtpOauth2_emailAdress'	=> '',
			'smtpOauth2_clientId'		=> '',
			'smtpOauth2_clientSecret'	=> '',
			'smtpOauth2_refreshToken'	=> ''
		));

		return true;
	}
}

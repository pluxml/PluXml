<?php
/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
class update_5_8 extends plxUpdate {
	const VERSION = '5.8';
	const NEW_PARAMS = array(
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
		'smtpOauth2_refreshToken'	=> '',
		'version'					=> self::VERSION,
	);

	/*
	 * mise à jour fichier parametres.xml (récupération du mot de passe)
	 * */
	public function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> <p style="max-width: 50em;"><em></em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></p></li>
<?php
		return $this->updateParameters(self::NEW_PARAMS);
	}
}

<?php
/**
 * Classe plxToken responsable du controle des formulaires
 *
 * @package PLX
 * @author	Stephane F, Pedro "P3ter" CADETE, J.P. Pourrez
 **/
class plxToken {
	const TEMPLATE = 'abcdefghijklmnpqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const TEMPLATE_LENGTH = 61; // strlen(self::TEMPLATE);
	const LIFETIME = 3600; // seconds

	/**
	 * Méthode qui affiche le champ input contenant le token
	 *
	 * @return	stdio/null
	 * @author	Stephane F, J.P. Pourrez
	 **/
	public static function getTokenPostMethod($length=32, $html=true) {

		$token = substr(
			str_shuffle(self::TEMPLATE),
			mt_rand(0, self::TEMPLATE_LENGTH - $length),
			$length
		);
		$_SESSION['formtoken'][$token] = time();

		return '<input name="token" value="'.$token.'" type="hidden" />';
	}

	/**
	 * Méthode qui valide la durée de vide d'un token
	 *
	 * @param	$request	(deprecated)
	 * @return	stdio/null
	 * @author	Stephane F, J.P. Pourrez
	 **/
	public static function validateFormToken($request='') {

		if($_SERVER['REQUEST_METHOD']=='POST' AND isset($_SESSION['formtoken'])) {

			if(empty($_POST['token']) OR plxUtils::getValue($_SESSION['formtoken'][$_POST['token']]) < time() - 3600) { # 3600 seconds
				unset($_SESSION['formtoken']);
				die('Security error : invalid or expired token');
			}
			unset($_SESSION['formtoken'][$_POST['token']]);

			// cleanup old tokens
			if(!empty($_SESSION['formtoken'])) {
				foreach($_SESSION['formtoken'] as $token=>$lifetime) {
					if($lifetime < $limit) {
						unset($_SESSION['formtoken'][$token]);
					}
				}
			}
		}
	}

	/**
	 * Generate Token expiry date
	 *
	 * @param	int		hours before expiration
	 * @return	string	expiry date
	 * @author	Pedro "P3ter" CADETE
	 */
	public static function generateTokenExperyDate($hours = 24) {
		return date('YmdHis', mktime(date('H')+$hours, date('i'), date('s'), date('m'), date('d'), date('Y')));
	}

}
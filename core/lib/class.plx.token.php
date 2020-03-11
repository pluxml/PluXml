<?php
/**
 * Classe plxToken responsable du controle des formulaires
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxToken {
	const TEMPLATE = 'abcdefghijklmnpqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const LIFETIME = 3600; // seconds

	/**
	 * Méthode qui affiche le champ input contenant le token
	 *
	 * @return	stdio/null
	 * @author	J.P. Pourrez, Stephane F
	 **/
	public static function getTokenPostMethod($length=32, $html=true) {
		$range = strlen(plxToken::TEMPLATE);
		$result = array();
		mt_srand((float)microtime() * 1000000);
		for($i=0; $i<$length; $i++) {
			$result[] = self::TEMPLATE[mt_rand() % $range];
		}
		$token = implode('', $result);
		$_SESSION['formtoken'][$token] = time();
		return ($html) ? '<input name="token" value="'.$token.'" type="hidden" />' : $token;
	}

	/**
	 * Méthode qui valide la durée de vide d'un token
	 *
	 * @param	$request	(deprecated)
	 * @return	stdio/null
	 * @author	J.P. Pourrez, Stephane F
	 **/
	public static function validateFormToken($request='') {

		if($_SERVER['REQUEST_METHOD']=='POST' AND isset($_SESSION['formtoken'])) {
			$limit = time() - self::LIFETIME;

			if(empty($_POST['token']) OR plxUtils::getValue($_SESSION['formtoken'][$_POST['token']]) < $limit) {
				unset($_SESSION['formtoken']);
				die('Security error : invalid or expired token');
			}
			unset($_SESSION['formtoken'][$_POST['token']]);
			// autoclean up !
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
	 * Create a token to reset user password
	 *
	 * @return	string	the token
	 * @author	Pedro "P3ter" CADETE
	 */
	public static function generateToken() {
		return sha1(mt_rand(0, 1000000));
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

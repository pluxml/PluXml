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
	const LIFETIME = 1800; // seconds - See session.gc_maxlifetime in php.ini

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

		return ($html) ? '<input name="token" value="'.$token.'" type="hidden" />' : $token;
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
			$limit = time() - self::LIFETIME;
			if(empty($_POST)) {
				return;
			}
			if(empty($_POST['token']) OR plxUtils::getValue($_SESSION['formtoken'][$_POST['token']]) < $limit) {
				unset($_SESSION['formtoken']);
				die('Security error : invalid or expired token');
			}

			// cleanup old tokens. But keep last 2 items for refresh the current HTML page ( Hit F5 key in the navigator )
			if(!empty($_SESSION['formtoken'])) {
				while(count($_SESSION['formtoken']) > 2) {
					$oldTime = array_shift($_SESSION['formtoken']);
				}
			}
		}
	}

	/**
	 * Generate Token expiry date
	 *
	 * @param	int		hours before expiration
	 * @return	string	expiry date
	 * @author	Pedro "P3ter" CADETE, Jean-Pierre Pourrez @bazooka07
	 */
	public static function generateTokenExperyDate($hours = 24) {
		// return date('YmdHis', mktime(date('H')+$hours, date('i'), date('s'), date('m'), date('d'), date('Y')));
		return date('YmdHis', time() + $hours * 3600);
	}

}

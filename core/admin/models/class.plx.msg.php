<?php

/**
 * Classe plxMsg qui gère les messages d'informations ou d'erreurs
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxMsg {

	/**
	 * Méthode qui mémorise un message d'information
	 *
	 * @param	msg			message d'info
	 * @return	boolean		true (pas d'erreur)
	 * @author	Stephane F
	 **/
	public static function Info($msg='') {
		$_SESSION['info'] = $msg;
		return true;
	}

	/**
	 * Méthode qui mémorise un message d'erreur
	 *
	 * @param	msg			message d'info
	 * @return	boolean		false (erreur)
	 * @author	Stephane F
	 **/
	public static function Error($msg='') {
		$_SESSION['error'] = $msg;
		return false;
	}

	/**
	 * Méthode qui affiche le message en mémoire
	 *
	 * @param	null
	 * @return	stdout
	 * @author	Stephane F
	 **/
	public static function Display() {

		if(isset($_SESSION['error']) AND !empty($_SESSION['error']))
			echo '<p id="msg" class="notification error">'.$_SESSION['error'].'</p>';
		elseif(isset($_SESSION['info']) AND !empty($_SESSION['info']))
			echo '<p id="msg" class="notification success">'.$_SESSION['info'].'</p>';
		unset($_SESSION['error']);
		unset($_SESSION['info']);
	}
}
?>
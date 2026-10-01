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
	 * @param	string $msg			message d'info
	 * @return	bool		true (pas d'erreur)
	 * @author	Stephane F
	 **/
	public static function Info($msg='') {
		$_SESSION['info'] = $msg;
		return true;
	}

	/**
	 * Méthode qui mémorise un message d'erreur
	 *
	 * @param	string $msg			message d'info
	 * @return	bool		false (erreur)
	 * @author	Stephane F
	 **/
	public static function Error($msg='') {
		$_SESSION['error'] = $msg;
		return false;
	}

	/**
	 * Méthode qui affiche le message en mémoire
	 *
	 * @return	void
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

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
		if (empty($msg))
			return true;
		if (!isset($_SESSION['info']))
			$_SESSION['info'] = array();
		$_SESSION['info'][] = $msg;
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
		if (empty($msg))
			return false;
		if (!isset($_SESSION['error']))
			$_SESSION['error'] = array();
		$_SESSION['error'][] = $msg;
		return false;
	}

	/**
	 * Méthode qui affiche le message en mémoire
	 *
	 * @param	null
	 * @return	void
	 * @author	Stephane F
	 **/
	public static function Display() {

		if(isset($_SESSION['error']) AND !empty($_SESSION['error'])) {
			echo '<p id="msg_error" class="notification error">'. implode('<br />', $_SESSION['error']) ."</p>";
		elseif(isset($_SESSION['info']) AND !empty($_SESSION['info']))
			echo '<p id="msg_info" class="notification success">'. implode('<br />', $_SESSION['info']) ."</p>";
		unset($_SESSION['error']);
		unset($_SESSION['info']);
	}
}

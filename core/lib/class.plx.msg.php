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
	 * @return	void
	 * @author	Stephane F, Pedro "P3ter" CADETE
	 **/
	public static function Display() {

	    if(isset($_SESSION['error']) AND !empty($_SESSION['error'])) {
            $class = "error";
	        $icon = "icon-cancel-circled";
            $message = $_SESSION['error'];
        }
		elseif(isset($_SESSION['info']) AND !empty($_SESSION['info'])) {
            $class = "success";
	        $icon = "icon-info-circled";
            $message = $_SESSION['info'];
        }
	    if (!empty($message))
            echo '
                <section id="msg" class="notification '.$class.' active flex-container">
                    <div class="ptm prm pbm"><i class="'.$icon.'"></i><strong>'.$message.'</strong></div>
                </section>';
		unset($_SESSION['error']);
		unset($_SESSION['info']);
	}
}
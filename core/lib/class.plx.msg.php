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
		if (empty(trim($msg))) {
			return true;
        }

		if (!isset($_SESSION['info'])) {
			$_SESSION['info'] = array($msg);
        } else {
            $_SESSION['info'][] = $msg;
        }
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
		if (empty(trim($msg))) {
			return false;
        }

		if (!isset($_SESSION['error'])) {
			$_SESSION['error'] = array($msg);
        } else {
            $_SESSION['error'][] = $msg;
        }
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

        foreach(array('error'=>'error', 'info'=>'success') as $f=>$g) {
            if (!empty($_SESSION[$f])) {
                $content = is_array($_SESSION[$f]) ? implode('</p>' . PHP_EOL . '<p>', $_SESSION[$f]) : $_SESSION[$f];
?>
<div id="msg" class="notification <?= $g ?>">
    <p><?= $content ?></p>
</div>
<?php
                break;
            }
        }

        unset($_SESSION['error']);
		unset($_SESSION['info']);
	}
}

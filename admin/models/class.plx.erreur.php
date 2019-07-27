<?php

/**
 * Classe plxErreur responsable des erreurs de traitement
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/
class plxErreur {

	private $message = false; # Message d'erreur

	/**
	 * Constructeur qui initialise la variable de classe
	 *
	 * @param	erreur	message d'erreur
	 * @return	null
	 * @author	Florent MONTHEL
	 **/
	public function __construct($erreur) {

		# Initialisation des variables de classe
		$this->message = $erreur;
	}

	/**
	 * Méthode qui retourne le message d'erreur
	 *
	 * @return	string
	 * @author	Florent MONTHEL
	 **/
	public function getMessage() {

		# On retourne le message d'erreur
		return $this->message;
	}

}
?>
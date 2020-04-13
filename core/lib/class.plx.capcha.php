<?php

/**
 * Classe plxCapcha responsable du traitement antispam
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Stéphane F, J.P. Pourrez
 **/
class plxCapcha {

	const TEMPLATE = 'abcdefghijklmnpqrstuvwxyz0123456789';
	const TEMPLATE_LENGTH = 36; // strlen(self::TEMPLATE)
	private $_word = false; # Mot du capcha
	private $_num = false; # Numero de la lettre selectionne
	private $_numletter = false; # Traduction du numero de la lettre

	/**
	 * Constructeur qui initialise les variables de classe
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN
	 **/
	public function __construct() {

		# Initialisation des variables de classe
		$this->createWord();
		// $this->num = $this->chooseNum();
		$this->_numletter = $this->num2letter();
	}

	/**
	 * Méthode qui génère un mot
	 *
	 * @param	min		longueur mini du mot
	 * @param	max		longueur maxi du mot
	 * @return	string	mot composant le capcha
	 * @author	Anthony GUÉRIN, Stephane F
	 **/
	public function createWord($min=5, $max=8) {

		# On genere une taille compris entre min et max
		$size = mt_rand($min,$max);

		# On retourne la valeur
		$this->_word = substr(
			str_shuffle(self::TEMPLATE),
			mt_rand(0, self::TEMPLATE_LENGTH - $size),
			$size
		);

		$this->_num = mt_rand(0, $size - 1);
	}

	/**
	 * DEPRECATED !
	 *
	 * Méthode qui choisit un numéro de lettre dans le mot chois
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function chooseNum() {

		# On choisit un numero entre 1 et la taille du mot
		return mt_rand(1,strlen($this->_word));
	}

	/**
	 * Méthode qui convertit le numéro en chaîne de caractère
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function num2letter() {

		# Num = derniere lettre du mot
		if($this->_num == strlen($this->_word) - 1) {
			return L_LAST;
		}

		# On genere un tableau associatif
		$letters = array(
			L_FIRST,
			L_SECOND,
			L_THIRD,
			L_FOURTH,
			L_FIFTH,
			L_SIXTH,
			L_SEVENTH,
			L_EIGTH,
			L_NINTH,
			L_TENTH
		);

		return ($this->_num < count($letters)) ? $letters[$this->_num] : ($this->_num - 1) . L_NTH;
	}

	/**
	 * Méthode qui génère la question du capcha
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN, Stéphane F
	 **/
	public function q() {
		# Generation de la question capcha
		$_SESSION['capcha_token'] = sha1(uniqid(rand(), true));
		$_SESSION['capcha'] = sha1($this->_word[$this->_num]);
		return sprintf(L_CAPCHA_QUESTION, $this->_numletter, $this->_word);
	}

	/**
	 * Méthode qui retourne la réponse du capcha (sha1)
	 * DEPRECATED
	 *
	 **/
	public function r() {
		# Generation du hash de la reponse
		return sha1($this->_word[$this->_num - 1]);
	}

}

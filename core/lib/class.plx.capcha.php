<?php

/**
 * Classe plxCapcha responsable du traitement antispam
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Stéphane F
 **/
class plxCapcha {

	private $min = false; # Longueur min du mot
	private $max = false; # Longueur max du mot
	private $word = false; # Mot du capcha
	private $num = false; # Numero de la lettre selectionne
	private $numletter = false; # Traduction du numero de la lettre

	/**
	 * Constructeur qui initialise les variables de classe
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN
	 **/
	public function __construct() {

		# Initialisation des variables de classe
		$this->min = 4;
		$this->max = 6;
		$this->word = $this->createWord();
		$this->num = $this->chooseNum();
		$this->numletter = $this->num2letter();
	}

	/**
	 * Méthode qui génère un mot
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN
	 **/
	public function createWord() {

		# On genere une taille compris entre min et max
		$size = mt_rand($this->min,$this->max);
		# Definition de l'alphabet
		$alphabet = 'abcdefghijklmnopqrstuvwxyz';
		$size_a = strlen($alphabet);
		# On genere un tableau word
		for($i = 0; $i < $size; $i++)
			$word[ $i ] = $alphabet[ mt_rand(0,$size_a-1) ];
		# On serialise le tableau et on retourne la valeur
		return implode('',$word);
	}

	/**
	 * Méthode qui choisit un numéro de lettre dans le mot chois
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function chooseNum() {

		# On choisit un numero entre 1 et la taille du mot
		return mt_rand(1,strlen($this->word));
	}

	/**
	 * Méthode qui convertit le numéro en chaîne de caractère
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function num2letter() {

		# Num = derniere lettre du mot
		if($this->num == strlen($this->word))
			return L_LAST;
		# On genere un tableau associatif
		$array = array(
			'1' => L_FIRST,
			'2' => L_SECOND,
			'3' => L_THIRD,
			'4' => L_FOURTH,
			'5' => L_FIFTH,
			'6' => L_SIXTH,
			'7' => L_SEVENTH,
			'8' => L_EIGTH,
			'9' => L_NINTH,
			'10' => L_TENTH);
		# La valeur existe dans le tableau
		if(isset($array[ $this->num ]))
			return $array[ $this->num ];
		else # Sinon on retourne une valeur generique
			return $this->num.L_NTH;
	}

	/**
	 * Méthode qui génère la question du capcha
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN, Stéphane F
	 **/
	public function q() {
		# Generation de la question capcha
		return sprintf(L_CAPCHA_QUESTION,$this->numletter,$this->word);
	}

	/**
	 * Méthode qui retourne la réponse du capcha (sha1)
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN
	 **/
	public function r() {
		# Generation du hash de la reponse
		return sha1($this->word[$this->num-1]);
	}

}
?>
<?php

/**
 * Classe plxCapcha responsable du traitement antispam
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Stéphane F, J.P. Pourrez
 **/
class plxCapcha {

	const TEMPLATE = 'abcdefghijklmnpqrstuvwxyz0123456789';
	const TEMPLATE_LENGTH = 35;
	const LETTERS = array(
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
	const MAX_LENGTH = 8;
	# for captcha image
	const CHAR_WIDTH = 32;
	const CHAR_HEIGHT = 40;
	private $_word = false; # Mot du capcha
	private $_num = 0; # position de la lettre sélectionnée de 1 à la dernière lettre de $_word
	/**
	 * Constructeur qui initialise les variables de classe
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN
	 **/
	public function __construct() {

		# Initialisation des variables de classe
		$this->createWord();
	}

	/**
	 * Méthode qui génère un mot
	 *
	 * @param	min		longueur mini du mot
	 * @param	max		longueur maxi du mot
	 * @return	string	mot composant le capcha
	 * @author	Anthony GUÉRIN, Stephane F
	 **/
	public function createWord($min=5, $max=self::MAX_LENGTH) {

		# On genere une taille compris entre min et max
		$size = mt_rand($min, $max);

		# On retourne la valeur
		$this->_word = substr(
			str_shuffle(self::TEMPLATE),
			mt_rand(0, self::TEMPLATE_LENGTH - $size - 1),
			$size
		);
	}

	/**
	 * DEPRECATED since PluXml 5.8.3 (2020)
	 *
	 * Méthode qui choisit un numéro de lettre dans le mot choisi
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function chooseNum() {

		# On choisit un numero entre 1 et la taille du mot
		return mt_rand(1, strlen($this->_word));
	}

	/**
	 * Méthode qui convertit le numéro en chaîne de caractère
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function num2letter() {

		# Num = derniere lettre du mot
		if($this->_num == strlen($this->_word)) {
			return L_LAST;
		}

		$n = $this->_num - 1;
		return ($n < count(self::LETTERS)) ? self::LETTERS[$n] : ($this->_num) . L_NTH;
	}

	/**
	 * Méthode qui génère la question du capcha
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN, Stéphane F
	 **/
	public function q() {
		# Generation de la question capcha
		$this->_num = $this->chooseNum();
		$_SESSION['capcha_token'] = sha1(uniqid(rand(), true));
		$_SESSION['capcha'] = sha1($this->_word[$this->_num - 1]);
		#return sprintf(L_CAPCHA_QUESTION, $this->num2letter(), '<span class="capcha-word">' . $this->_word . '</span>');
		return sprintf(L_CAPCHA_QUESTION, $this->num2letter(), $this->createImage(true));
	}

	/**
	 * DEPRECATED
	 *
	 * Méthode qui retourne la réponse du capcha (sha1)
	 *
	 **/
	public function r() {
		# Generation du hash de la reponse
		return sha1($this->_word[$this->_num - 1]);
	}

	// https://blog.alphorm.com/creer-un-captcha-securise-en-php-8s
	public function createImage($encode=false) {
		$length = self::MAX_LENGTH;
		if($length < strlen($this->_word)) {
			$length = strlen($this->_word);
		}

		$imgWidth = (self::CHAR_WIDTH * $length) + 30;
		$img = imagecreate($imgWidth, self::CHAR_HEIGHT);
		# background-color of image
		$bg = imagecolorallocate($img, rand(230, 255), rand(230, 255), rand(230, 255));

		# draw the word
		$dx = intval($imgWidth / strlen($this->_word));
		for($i=0; $i<strlen($this->_word); $i++) {
			$color = imagecolorallocate($img, rand(0,200), rand(0,200), rand(0,200));
			$fontSize = rand(25, 30);
			$fontIndex = rand(1, 10);
			$x = rand(10, 20) + ($dx * $i);
			$x = rand($x - 5, $x + 5);
			$y = rand(28, self::CHAR_HEIGHT - 8);
			$angle = rand(-25, 25);
			imagettftext($img, $fontSize, $angle, $x, $y ,  $color, PLX_CORE . 'lib/fonts/' . $fontIndex . '.ttf', $this->_word[$i]);
		}

		# Add random lines
		$iMax = rand(10, 20);
		for($i=0; $i<$iMax; $i++) {
			$color = imagecolorallocate($img, rand(0,255), rand(0,255), rand(0,255));
			imageline($img, rand(0, $imgWidth), rand(0, self::CHAR_HEIGHT), rand(0, $imgWidth), rand(0, self::CHAR_HEIGHT), $color);
			// $x = rand(1, intval($imgWidth / 2));
			// imageline($img, $x, rand(1, self::CHAR_HEIGHT), $x + rand(1, intval($imgWidth / 2)), rand(1, self::CHAR_HEIGHT), $color);
		}
		if(!$encode) {
			header('Content-Type: image/png');
			imagepng($img);
			imagedestroy($img);
			return '';
		}

		ob_start();
		imagepng($img);
		imagedestroy($img);
		$data = base64_encode(ob_get_clean());
		return '<img class="capcha-img" src="data:image/png;base64,' . $data . '" width="' . $imgWidth . '" height="' . self::CHAR_HEIGHT . '">';
	}

}

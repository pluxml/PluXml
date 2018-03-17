<?php
/**
 * Classe plxCapchaImage
 *
 **/
class plxCapcharImage extends plxModule {

	/**
	 * Constructeur de la classe
	 *
	 * @return	null
	 * @author	Stéphane F.
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxModule (obligatoire)
		parent::__construct($default_lang);

		# Ajouts des hooks
		$this->addHook('plxShowCapchaQ', 'plxShowCapchaQ');
		$this->addHook('plxShowCapchaR', 'plxShowCapchaR');
		$this->addHook('plxMotorNewCommentaire', 'plxMotorNewCommentaire');
		$this->addHook('ThemeEndHead', 'ThemeEndHead');
		$this->addHook('IndexEnd', 'IndexEnd');

	}

	/**
	 * Méthode qui génère le code du capcha
	 *
	 * @return	string		code du capcha
	 * @author	Stéphane F.
	 **/
	private function _getCode($length) {
		$chars = '23456789abcdefghjklmnpqrstuvwxyz'; // Certains caractères ont été enlevés car ils prêtent à confusion
		$rand_str = '';
		for ($i=0; $i<$length; $i++) {
			$rand_str .= $chars{ mt_rand( 0, strlen($chars)-1 ) };
		}
		return strtolower($rand_str);
	}

	/**
	 * Méthode qui affiche l'image du capcha
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxShowCapchaQ() {
		$_SESSION['capcha']=$this->_getCode(5);
		$_SESSION['capcha_token'] = sha1(uniqid(rand(), true));
		echo '<img src="'.$this->URL().'capcha.php" alt="Capcha" id="capcha" />';
		echo '<a id="capcha-reload" href="javascript:void(0)" onclick="document.getElementById(\'capcha\').src=\''.	$this->URL().'capcha.php?\' + Math.random(); return false;"><img src="'.$this->URL().'reload.png" title="" /></a><br />';
		$this->lang('L_MESSAGE');
		echo '<input type="hidden" name="capcha_token" value="'.$_SESSION['capcha_token'].'" />';
		echo '<?php return true; ?>'; # pour interrompre la fonction CapchaQ de plxShow
	}

	/**
	 * Méthode qui encode le capcha en sha1 pour comparaison
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxMotorNewCommentaire() {
		echo '<?php $_SESSION["capcha"]=sha1($_SESSION["capcha"]); ?>';
	}

	/**
	 * Méthode qui retourne la réponse du capcha // obsolète
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxShowCapchaR() {
		echo '<?php return true; ?>';  # pour interrompre la fonction CapchaR de plxShow
	}

	/**
	 * Méthode qui modifie la taille et le nombre maximum de caractères autorisés dans la zone de saisie du capcha
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function IndexEnd() {
		echo '<?php
			if(preg_match("/<input(?:.*?)name=[\'\"]rep[\'\"](?:.*)maxlength=([\'\"])([^\'\"]+).*>/i", $output, $m)) {
				$o = str_replace("maxlength=".$m[1].$m[2], "maxlength=".$m[1]."5", $m[0]);
				$output = str_replace($m[0], $o, $output);
			}
			if(preg_match("/<input(?:.*?)name=[\'\"]rep[\'\"](?:.*)size=([\'\"])([^\'\"]+).*>/i", $output, $m)) {
				$o = str_replace("size=".$m[1].$m[2], "size=".$m[1]."5", $m[0]);
				$output = str_replace($m[0], $o, $output);
			}
		?>';
	}

	/**
	 * Méthode qui applique un effet css sur le bouton de rechargement du captcha
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function ThemeEndHead() {
		echo "\n<style>
#capcha {border:1px solid #cecece;border-right:0}
#capcha-reload img {background:#fafafa;border:1px solid #cecece}
#capcha-reload:hover{opacity: 0.7; filter: alpha(opacity=70);}
</style>\n";
	}
}
?>
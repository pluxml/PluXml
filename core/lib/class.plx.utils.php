<?php
if (!defined('PLX_CORE')) { exit; }

/**
 * Classe plxUtils rassemblant les fonctions utiles à PluXml
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Jean-Pierre Pourrez @bazooka07
 **/

/*
 * Pour voir les dépendances à propos de PHPMailer :
 * composer show --tree
 *
 * Lire à ce propos le fichier core/lisezmoi.txt
 * */

$autoloader = PLX_CORE . 'vendor/autoload.php';
if(file_exists($autoloader)) {
	require $autoloader;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use Hayageek\OAuth2\Client\Provider\Yahoo;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Greew\OAuth2\Client\Provider\Azure;

class plxUtils {

	const REMOVE_WORDS = array(
		'en' => 'an?|as|at|before|but|by|for|from|is|in(?:to)?|like|off?on(?:to)?|per|since|than|the|this|that|to|up|via|with',
		'de' => 'das|der|die|fur|am',
		'fr' => 'a|de?|des|du|e?n|la|le|une?|vers'
	);
	const MINIFY_PATTERNS = array(
		# Supprime les commentaires
		'!/\*[^*]*\*+([^/][^*]*\*+)*/!' => '',
		# Supprime les espaces superflus autour d'une ponctuation, excepté ":"
		'#\s*([\{\},;\>])\s*#m' => '$1',
		# supprime espaces en début de ligne (heading spaces)
		'#^\s+#' => '',
		# idem en fin de ligne (trailing spaces)
		'#\s+$#' => '',
		# réduit le nombre d'espaces si >= 2
		'#\s{2,}#' => ' ',
		# Minify HEX color code
		'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i' => '$1$2$3',
		# Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
		'#(?<=[\s:,-])0+(\.\d+)#s' => '$1',
		# Reduit une succession de 0
		'#(?<=:)\s*0(?:\s+0){1,3}\s*;#m' => '0',
	);
	# https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Errors/Regex_character_class_escape_in_class_range
	const FILTER_PROFIL_LOGIN = '\p{L}{3,16}([\s@\.\-](\p{L}|\d){2,20}){0,4}';
	const FILTER_PROFIL = array(
		'id'		=> array('\d{3}', L_UNKNOWN_ERROR),
		# https://www.regular-expressions.info/unicodecategory.html
		# https://fr.javascript.info/regexp-unicode
		'fullname'	=> array(self::FILTER_PROFIL_LOGIN, L_ERR_INVALID_USERNAME),
		# On accepte une adresse E-mail comme login sans vérification, caractères UTF-8 et chiffres, -, ., espace
		'login'		=> array(self::FILTER_PROFIL_LOGIN, L_ERR_INVALID_LOGIN),
		'password'	=> array('.{8,64}', L_ERR_INVALID_PASSWORD),
		'password2'	=> array('.{8,64}', L_ERR_INVALID_PASSWORD),
	);


	/**
	 * Méthode qui vérifie si une variable est définie.
	 * Renvoie la valeur de la variable ou la valeur par défaut passée en paramètre
	 *
	 * @param	string|null $var			variable à tester
	 * @param	string|int $default		valeur par défaut
	 * @return	string		valeur de la variable ou valeur par défaut passée en paramètre
	*/
	public static function getValue(&$var, $default='') {
		return ($var !== null) ? $var : $default;
	}

	/**
	 * Méthode qui retourne un tableau contenu les paramètres passés dans l'url de la page courante
	 *
	 * @return	string	tableau avec les paramètres passés dans l'url de la page courante
	 **/
	public static function getGets() {

		if(!empty($_SERVER['QUERY_STRING']))
			return strip_tags($_SERVER['QUERY_STRING']);
		else
			return '';
	}

	/**
	 * Méthode qui supprime les antislashs
	 *
	 * @param	string|array $content				variable ou tableau ( 2 dimensions maxi )
	 * @return	array|string		tableau ou variable avec les antislashs supprimés
	 * @author  J.P. Pourrez aka bazooka07
	 **/
	public static function unSlash($content) {

		if(is_string($content)) {
			# On traite une chaine
			return stripslashes($content);
		}

		 # On traite un tableau
		$new_content = array();
		foreach($content as $k=>$v) { # On parcourt le tableau
			if(is_array($v)) {
				$buf1 = array();
				foreach($v as $key=>$val) {
					if(is_array($val)) {
						$buf2 = array();
						foreach($val as $i=>$w) {
							$buf2[$i] = stripslashes($w);
						}
						$buf1[$key]  = $buf2;
					} else {
						$buf1[$key] = stripslashes($val);
					}
				}
				$new_content[$k] = $buf1;
			} else {
				$new_content[$k] = stripslashes($v);
			}
		}
		return $new_content;
	}

	/**
	 * Méthode qui vérifie le bon formatage d'une adresse email
	 *
	 * @param	string $mail		adresse email à vérifier
	 * @return	bool		vrai si adresse email bien formatée
	 **/
	public static function checkMail($mail) {

		if (strlen($mail) > 80)
			return false;
		return preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $mail);
	}

	/**
	 * Méthode qui vérifie si l'url passée en paramètre correspond à un format valide
	 *
	 * @param	string $site		url d'un site
	 * @param	bool $reset		reset $site si échec
	 * @return	bool		vrai si l'url est bien formatée
	 **/
	public static function checkSite(&$site, $reset=true, $parse=true) {

		if(empty(trim($site))) {
			return true;
		}

		$url = preg_replace('#([\'"].*)#', '', $site);
		if(is_string($url) AND $url[0] == '?') {
			$site = $url;
			return true; # url interne commençant par ?
		}

		$url = filter_var($site, FILTER_VALIDATE_URL);
		if($url === false) {
			if($reset) {
				$site = '';
			}
			return false;
		}

		return true;
	}

	/**
	 * Méthode qui vérifie le format d'une adresse ip
	 *
	 * @param	string $ip			adresse ip à vérifier
	 * @return	bool		vrai si format valide
	 **/
	public static function isValidIp($ip) {

		if($ip=='::1') return false;
		$ipv4 = '/((^|\.)(2[0-5]{2}|[01]\d{2}|\d{1,2})(?=\.|$)){4}/';
		$ipv6 = '/^:?([a-fA-F0-9]{1,4}(:|.)?){0,8}(:|::)?([a-fA-F0-9]{1,4}(:|.)?){0,8}$/';
		return (preg_match($ipv4, $ip) OR preg_match($ipv6, $ip));

	}

	/**
	 * Méthode qui retourne l'adresse ip d'un visiteur
	 *
	 * @return	string		adresse ip d'un visiteur
	 **/
	public static function getIp() {

		if(!empty($_SERVER['HTTP_CLIENT_IP'])) # check ip from share internet
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) # to check ip is pass from proxy
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			$ip=$_SERVER['REMOTE_ADDR'];

		if(version_compare(phpversion(), '5.3.0', '<'))
			$localIP = getHostByName(php_uname('n'));
		else
			$localIP = getHostByName(getHostName());

		return plxUtils::isValidIp($ip) ? $ip : $localIP;
	}

	public static function checkProfil(&$content) {
		# name, login, password, email, lang
		# contrôles
		# plxAdmin::editUser() : id, email, lang
		# plxAdmin::editProfil() : name, email, lang ( id == $_SESSION['user'] )
		# plxAdmin::editUsers() : id, name, login, password, email, profil, active
		# install/index.php : name, login, password, password2, email ( id == '001' )

		$withMsg = class_exists('plxMsg', false);

		# controle de l'"id", du "name", du "login", du "password" et du "password2" de l'user
		foreach(self::FILTER_PROFIL as $field=>$infos) {
			list($mask, $msg) = $infos;
			if(isset($content[$field]) and !preg_match('#^' . $mask . '$#u', $content[$field])) {
				return ($withMsg ? plxMsg::Error($msg) : $msg);
			}
		}

		if(isset($content['password']) and isset($content['password2']) and $content['password'] != $content['password2']) {
			# Mauvaise confirmation du mot de passe
			return ($withMsg ? plxMsg::Error(L_ERR_PASSWORD_CONFIRMATION) : L_ERR_PASSWORD_CONFIRMATION);
		}

		# controle de l'"email"
		if(isset($content['email'])) {
			$email = filter_var(trim($content['email']), FILTER_VALIDATE_EMAIL);
			if(empty($email)) {
				return ($withMsg ? plxMsg::Error(L_ERR_INVALID_EMAIL) : L_ERR_INVALID_EMAIL);
			}
		}

		# controle de la "lang" sélectionnée
		if(isset($content['lang']) and !self::lang_exists($content['lang'])) {
			return ($withMsg ? plxMsg::Error(L_UNKNOWN_ERROR) : L_UNKNOWN_ERROR);
		}

		return true;
	}

	/**
	 * Méthode qui affiche une liste de sélection dans une balise <select>
	 *
	 * @param	string $name		nom de la balise
	 * @param	array $array		valeurs de la liste sous forme de tableau (nom, valeur)
	 * @param	string $value	valeur par défaut
	 * @param	bool $readonly	vrai si la liste est en lecture seule (par défaut à faux)
	 * @param	string $class		class css à utiliser pour formater l'affichage
	 * @param	bool|string $id			si vrai génère un id à partir du nom du champ, sinon génère l'id à partir du paramètre
	 * @return	void
	 **/
	public static function printSelect($name, $array, $value='', $readonly=false, $class='', $id=true) {
		if(is_bool($id))
			$id = ($id ? ' id="id_'.$name.'"' : '');
		else
			$id = ($id!='' ? ' id="'.$id.'"' : '');

		if($readonly) {
			$class .= ' readonly';
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}
?>
<select <?= $id ?> name="<?= $name ?>" <?= $disabled ?> class="<?= $class ?>">
<?php
		foreach($array as $a => $b) {
			if(is_array($b)) {
?>
	<optgroup label="<?= $a ?>">
<?php
				foreach($b as $c=>$d) {
					$selected = ($c == $value) ? ' selected' : '';
?>
		<option value="<?= $c ?>" <?= $selected ?> <?php if($c == '-') { echo 'disabled'; } ?>><?= $d ?></option>
<?php
				}
?>
	</optgroup>
<?php
			} else {
				$selected = ($a == $value) ? 'selected' : '';
?>
	<option value="<?= $a ?>" <?= $selected ?> <?php if($a == '-') { echo 'disabled'; } ?>><?= $b ?></option>
<?php
			}
		}
?>
</select>
<?php
	}

	/**
	 * Méthode qui affiche une zone de saisie
	 *
	 * @param	string $name		nom de la zone de saisie
	 * @param	string $value		valeur contenue dans la zone de saisie
	 * @param	string $type		type du champ (text, password, hidden)
	 * @param	string $sizes		longueur du champ - nombre maximal de caractères pouvant être saisis (par défaut 50-255)
	 * @param	bool $readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	string $className	class css à utiliser pour formater l'affichage
	 * @param	string $placeholder valeur du placeholder du champ (html5)
	 * @param	string $extra		extra paramètre pour du javascript par exemple (onclick)
	 * @param	bool $required	permet de rendre le champ obligatoire
	 * @return	void
	 * @author	unknown, Pedro "P3ter" CADETE
	 **/
	public static function printInput($name, $value='', $type='text', $sizes='50-255', $readonly=false, $className='', $placeholder='', $extra='', $required=false) {

		 $params = array(
			'id="id_'.$name.'"',
			'name="'.$name.'"',
			'type="'.$type.'"'
		 );

		 if($type != 'hidden' and array_key_exists($name, self::FILTER_PROFIL)) {
			 $params[] = 'pattern="' . self::FILTER_PROFIL[$name][0] . '"';
			 $params[] = 'title="' . self::FILTER_PROFIL[$name][0] . '"';
			 if($type == 'password' and $required == false) {
				 $params[] = 'required';
			 }
		 }

		 if(strlen($value) > 0)
			 $params[] = 'value="'.$value.'"';
		 if(!empty($extra))
			 $params[] = $extra;
		 if($type != 'hidden') {
			if($required === true)
				$params[] = 'required="required"';
			if($readonly === true)
				$params[] = 'readonly="readonly" class="readonly"';
			if(!empty($className))
				$params[] = 'class="'.$className.'"';
			if(!empty($placeholder))
				$params[] = 'placeholder="'.$placeholder.'"';
			if(!empty($sizes) AND (strpos($sizes, '-') !== false)) {
				list($size, $maxlength) = explode('-', $sizes);
				if(!empty($size))
					$params[] = 'size="'.$size.'"';
				if(!empty($maxlength))
					$params[] = 'maxlength="'.$maxlength.'"';
			}
		 }
		 echo '<input '.implode(' ', $params).'/>';
	}

	/**
	 * Méthode qui affiche des boutons radio
	 *
	 * @param	string $name		 nom des radio boutons
	 * @param	array $array
	 * @param	string $value		valeur correspond au radio bouton
	 * @param	string $className	class css à utiliser pour formater l'affichage
	 * @param	string $extra
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 **/
	public static function printInputRadio($name, $array, $value='', $className='', $extra='') {

		$params = array();
		if(!empty($extra)) {
			$params[] = $extra;
		}
		if(!empty($className)) {
			$params[] = 'class="'.$className.'"';
		}
		foreach($array as $a => $b) {
?>
<input type="radio" id="id_<?= $name ?>" name="<?= $name ?>" value="<?= $a ?>" <?php if(!empty($params)) { echo implode(' ', $params); } ?><?php if($a == $value) { echo ' checked'; }?>>&nbsp;<?= $b ?><br>
<?php
		}
	}

	/**
	 * Méthode qui affiche une zone de texte
	 *
	 * @param	string	$name		nom de la zone de texte
	 * @param	?string	$value		valeur contenue dans la zone de texte
	 * @param	?int	$cols		nombre de caractères affichés par colonne
	 * @param	?int	$rows		nombre de caractères affichés par ligne
	 * @param	bool	$readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	string	$className	class css à utiliser pour formater l'affichage
	 * @param	?string	$extra		extra permet d'ajouter un élément HTML (exemple : un "onclick" en javascript)
	 * @return	void
	 */
	public static function printArea($name, $value=null, $cols=null, $rows=null, $readonly=false, $className='full-width', $extra=null) {
		$attrs = array (
				'id="id_' . $name . '"',
				'name="' . $name . '"'
		);
		if (is_integer($cols)) {
			$attrs [] = 'cols="' . $cols . '"';
		}
		if (is_integer($rows)) {
			$attrs[] = 'rows="' . $rows . '"';
		}
		$classList = array();
		if ($readonly === true) {
			$classList[] = 'readonly';
		}
		if (strlen(trim($className)) > 0) {
			$classList[] = trim($className);
		}
		if (!empty($classList)) {
			$attrs[] = 'class="' . implode(' ', $classList) . '"';
		}
		if (!empty($extra)) {
			$attrs[] = $extra;
		}
		echo '<textarea ' . implode(' ', $attrs) . '>' . $value . '</textarea>';
	}

	public static function printPasswordStrength($id) {
		$translations = array(L_PWD_VERY_WEAK, L_PWD_WEAK, L_PWD_GOOD, L_PWD_STRONG);
?>
<span id="id_<?= $id ?>_strength" class="password-strength" data-lang="<?= implode('|', $translations) ?>"></span>
<?php
	}

	/**
	 * Méthode qui teste si un fichier est accessible en écriture
	 *
	 * @param	string $file		emplacement et nom du fichier à tester
	 * @param	string $format		format d'affichage
	 **/
	public static function testWrite($file, $format="<li><span style=\"color:#color\">#symbol #message</span></li>\n") {

		$yes = is_writable($file);
		echo strtr($format, array(
			'#color'	=> $yes ? 'green' : 'red',
			'#symbol'	=> $yes ? '&#10004;' : '&#10007;',
			'#message'	=> sprintf($yes ? L_WRITE_ACCESS : L_WRITE_NOT_ACCESS, $file),
		));
	}

	/**
	 * Méthode qui teste si le module apache mod_rewrite est disponible
	 *
	 * @param	bool $io		affiche à l'écran le résultat du test si à VRAI
	 * @param	string $format	format d'affichage
	 * @return	bool			vrai si le module apache mod_rewrite est disponible
	 * @author	Stephane F
	 **/
	public static function testModRewrite($io=true, $format='<li><span style="color:#color">#symbol #message</span></li>' . PHP_EOL) {

		if(function_exists('apache_get_modules')) {
			$test = in_array("mod_rewrite", apache_get_modules());
			if($io==true) {
				if($test) {
					$output = str_replace('#color', 'green', $format);
					$output = str_replace('#symbol', '&#10004;', $output);
					$output = str_replace('#message', L_MODREWRITE_AVAILABLE, $output);
					echo $output;
				} else {
					$output = str_replace('#color', 'red', $format);
					$output = str_replace('#symbol', '&#10007;', $output);
					$output = str_replace('#message', L_MODREWRITE_NOT_AVAILABLE, $output);
					echo $output;
				}
			}
			return $test;
		}
		else return true;
	}

	/**
	 * Méthode qui teste si la fonction php mail est disponible
	 *
	 * @param	bool $io		affiche à l'écran le résultat du test si à VRAI
	 * @param	string $format	format d'affichage
	 * @return	bool			retourne vrai si la fonction php mail est disponible
	 * @author	Stephane F
	 **/
	public static function testMail($io=true, $format='<li><span style="color:#color">#symbol #message</span></li>' . PHP_EOL) {

		if($return = function_exists('mail')) {
			if(!empty($io)) {
				echo strtr(
					$format, array(
						'#color'	=> 'green',
						'#symbol'	=> '&#10004;',
						'#message'	=> L_MAIL_AVAILABLE
					)
				);
			}
		} else {
			if(!empty($io)) {
				echo strtr(
					$format, array(
						'#color'	=> 'red',
						'#symbol'	=> '&#10007;',
						'#message'	=> L_MAIL_NOT_AVAILABLE
					)
				);
			}
		}

		return $return;
	}

	/**
	 * Méthode qui teste si la bibliothèque GD est installé
	 *
	 * @param	string $format		format d'affichage
	 * @author	Stephane F
	 **/
	public static function testLibGD($format='<li><span style="color:#color">#symbol #message</span></li>' . PHP_EOL) {

		if(function_exists('imagecreatetruecolor')) {
			$output = str_replace('#color', 'green', $format);
			$output = str_replace('#symbol', '&#10004;', $output);
			$output = str_replace('#message', L_LIBGD_INSTALLED, $output);
			echo $output;
		} else {
			$output = str_replace('#color', 'red', $format);
			$output = str_replace('#symbol', '&#10007;', $output);
			$output = str_replace('#message', L_LIBGD_NOT_INSTALLED, $output);
			echo $output;
		}
	}

	/**
	 * Méthode qui teste si la bibliothèque XML est installée
	 *
	 * @param	string $format		format d'affichage
	 *
	 **/
	public static function testLibXml($format='<li><span style="color:#color">#symbol #message</span></li>' . PHP_EOL) {

		if(function_exists('xml_parser_create')) {
			$output = str_replace('#color', 'green', $format);
			$output = str_replace('#symbol', '&#10004;', $output);
			$output = str_replace('#message', L_LIBXML_INSTALLED, $output);
			echo $output;
		} else {
			$output = str_replace('#color', 'red', $format);
			$output = str_replace('#symbol', '&#10007;', $output);
			$output = str_replace('#message', L_LIBXML_NOT_INSTALLED, $output);
			echo $output;
		}
	}

	/**
	 * Méthode qui formate une chaine de caractères en supprimant des caractères non valides
	 *
	 * @param	string $str			chaine de caractères à formater
	 * @param	string $charset		charset à utiliser dans le formatage de la chaine (par défaut utf-8)
	 * @return	string		chaine formatée
	 **/
	public static function removeAccents($str, $charset=PLX_CHARSET) {

		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$str = str_replace($a, $b, $str);
		$str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml|uro)\;#', '\1', $str);
		$str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); # pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#\&[^;]+\;#', '', $str); # supprime les entités HTML
		return $str;
	}

	/**
	 * Method to translitterate (transform a string with only ASCII characters)
	 * Inspired by https://github.com/jbroadway/urlify/blob/master/URLify.php
	 * @param	string		$str	the string to translitterate
	 * @param	boolean		$reverse
	 * @return	string
	 * @author J.P. Pourrez (bazooka07)
	 */
	public static function translitterate($str, $reverse=false) {

		$alphabets = array(
			'de' => array(
				'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue',
				'ß' => 'ss', 'ẞ' => 'SS'
			),
			'ro' => array(
				'ă'=> 'a', 'î'=> 'i', 'ș'=> 's', 'ț'=> 't', 'â'=> 'a',
				'Ă'=> 'A', 'Î'=> 'I', 'Ș'=> 'S', 'Ț'=> 'T', 'Â'=> 'A'
			),
			'pl' => array(
				'ą'=> 'a', 'ć'=> 'c', 'ę'=> 'e', 'ł'=> 'l', 'ń'=> 'n', 'ó'=> 'o', 'ś'=> 's',
				'ź'=> 'z', 'ż'=> 'z',
				'Ą'=> 'A', 'Ć'=> 'C', 'Ę'=> 'E', 'Ł'=> 'L', 'Ń'=> 'N', 'Ó'=> 'O', 'Ś'=> 'S',
				'Ź'=> 'Z', 'Ż'=> 'Z'
			),
			'ru' => array(
				'а'=> 'a', 'б'=> 'b', 'в'=> 'v', 'г'=> 'g', 'д'=> 'd', 'е'=> 'e', 'ё'=> 'yo',
				'ж'=> 'zh', 'з'=> 'z', 'и'=> 'i', 'й'=> 'j', 'к'=> 'k', 'л'=> 'l', 'м'=> 'm',
				'н'=> 'n', 'о'=> 'o', 'п'=> 'p', 'р'=> 'r', 'с'=> 's', 'т'=> 't', 'у'=> 'u',
				'ф'=> 'f', 'х'=> 'h', 'ц'=> 'c', 'ч'=> 'ch', 'ш'=> 'sh', 'щ'=> 'sh', 'ъ'=> '',
				'ы'=> 'y', 'ь'=> '', 'э'=> 'e', 'ю'=> 'yu', 'я'=> 'ya',
				'А'=> 'A', 'Б'=> 'B', 'В'=> 'V', 'Г'=> 'G', 'Д'=> 'D', 'Е'=> 'E', 'Ё'=> 'Yo',
				'Ж'=> 'Zh', 'З'=> 'Z', 'И'=> 'I', 'Й'=> 'J', 'К'=> 'K', 'Л'=> 'L', 'М'=> 'M',
				'Н'=> 'N', 'О'=> 'O', 'П'=> 'P', 'Р'=> 'R', 'С'=> 'S', 'Т'=> 'T', 'У'=> 'U',
				'Ф'=> 'F', 'Х'=> 'H', 'Ц'=> 'C', 'Ч'=> 'Ch', 'Ш'=> 'Sh', 'Щ'=> 'Sh', 'Ъ'=> '',
				'Ы'=> 'Y', 'Ь'=> '', 'Э'=> 'E', 'Ю'=> 'Yu', 'Я'=> 'Ya'
			)
		);

		if(!(defined('PLX_SITE_LANG')) or !array_key_exists(PLX_SITE_LANG, $alphabets)) {
			return $str;
		}

		if (!$reverse) {
			return strtr($str, $alphabets[PLX_SITE_LANG]);
		}

		arsort($alphabets[PLX_SITE_LANG]); # Hack against str_replace

		return str_replace(
			array_values($alphabets[PLX_SITE_LANG]),
			array_keys($alphabets[PLX_SITE_LANG]),
			$str
		);
	}

	/**
	 * Transform a string in a valid URL using transliteration
	 *
	 * @param	string	$url		characters string to clean
	 * @param	boolean	$remove		set true to remove non-semantic word
	 * @param	string	$replace	the character used to clean the URL
	 * @param	boolean	$lower		set true to lower characters
	 * @return	string	valid URL
	 * @author	J.P. Pourrez (bazooka07), Pedro (P3ter) CADETE
	 * */
	public static function urlify($url, $remove=false, $replace='-', $lower=true) {

		if(!empty(parse_url($url, PHP_URL_SCHEME))) {
			return $url;
		}

		$clean_url = plxUtils::translitterate(trim(html_entity_decode($url)));

		if($remove && defined('PLX_SITE_LANG') && array_key_exists(PLX_SITE_LANG, self::REMOVE_WORDS)) {
			$clean_url = preg_replace('@\b(' . self::REMOVE_WORDS[PLX_SITE_LANG] . ')\b@u', $replace, $clean_url);
		}

		// remove accents
		$clean_url = plxUtils::removeAccents($clean_url, PLX_CHARSET);

		// remove whitespace
		$clean_url = preg_replace('@[\s' . $replace . ']+@', $replace, $clean_url);

		// remove non-alphanumeric character
		$clean_url = trim(preg_replace('@[^\w-]+@', '', $clean_url), '-');

		if($lower) {
			$clean_url = strtolower($clean_url);
		}

		return $clean_url;
	}

	/**
	 * Méthode qui convertit une chaine de caractères au format valide pour une url
	 *
	 * @param	string $str			chaine de caractères à formater
	 * @return	string		nom d'url valide
	 **/
	public static function title2url($str) {

		$str = strtolower(plxUtils::removeAccents($str,PLX_CHARSET));
		$str = preg_replace('/[^[:alnum:]]+/',' ',$str);
		return strtr(trim($str), ' ', '-');
	}

	/**
	 * Méthode qui convertit une chaine de caractères au format valide pour un nom de fichier
	 *
	 * @param	string $str			chaine de caractères à formater
	 * @return	string		nom de fichier valide
	 **/
	public static function title2filename($str) {

		$str = strtolower(plxUtils::removeAccents($str,PLX_CHARSET));
		$str = str_replace('|','',$str);
		$str = preg_replace('/\.{2,}/', '.', $str);
		$str = preg_replace('/[^[:alnum:]|.|_]+/',' ',$str);
		return strtr(ltrim(trim($str),'.'), ' ', '-');
	}

	/**
	 * Méthode qui convertit un chiffre en chaine de caractères sur une longueur de n caractères, completée par des 0 à gauche
	 *
	 * @param	int $num					chiffre à convertir
	 * @param	int $length				longueur de la chaine à retourner
	 * @return	string				chaine formatée
	 **/
	public static function formatRelatif($num, $length) {

		$fnum = str_pad(strval(abs($num)), $length, '0', STR_PAD_LEFT);
		if($num > -1)
			return '+'.$fnum;
		else
			return '-'.$fnum;
	}

	/**
	 * Méthode qui écrit dans un fichier
	 * Mode écriture seule; place le pointeur de fichier au début du fichier et réduit la taille du fichier à 0. Si le fichier n'existe pas, on tente de le créer.
	 *
	 * @param	string $content					contenu du fichier
	 * @param	string $filename			emplacement et nom du fichier
	 * @return	bool				retourne vrai si l'écriture s'est bien déroulée
	 **/
	public static function write($content, $filename) {

		$target = $filename;
		if(file_exists($filename)) {
			$target .= '.new';
		}

		$hdle = fopen($target, 'w');
		if($hdle === false) {
			# Impossible d'ouvrir le fichier en écriture
			return false;
		}
		# On écrit
		if(
			fwrite($hdle, trim($content)) === false or
			fclose($hdle) === false or
			(
				$target != $filename and
				(
					# On renomme le fichier temporaire avec le nom de l'ancien
					!unlink($filename) or
					!rename($target, $filename)
				)
			)
		) {
			# Il y a des erreurs
			return false;
		}

		# On ajuste les bones permissions
		return chmod($filename, 0644);
	}

	/**
	 * Méthode qui formate l'affichage de la taille d'un fichier
	 *
	 * @param	int $bytes				taille en octets d'un fichier
	 * @return	string				chaine d'affichage formatée
	 **/
	public static function formatFilesize($bytes) {

		$levels = array(
			1024,
			1024 * 1024,
			1024  * 1024 * 1024,
		);

		if ($bytes < $levels[0]) {
			return $bytes.' B';
		}

		if ($bytes < $levels[1]) {
			return round($bytes / $levels[0], 2).' Kb';
		}

		if ($bytes < $levels[2]) {
			return round($bytes / $levels[1], 2).' Mb';
		}

		return round($bytes / $levels[2], 2).' Gb';
	}

	/**
	 * Méthode qui crée la miniature d'une image
	 *
	 * @param	string $src_image		emplacement et nom du fichier source
	 * @param	string $dest_image		emplacement et nom de la miniature créée
	 * @param	int $thumb_width		largeur de la miniature
	 * @param	int $thumb_height		hauteur de la miniature
	 * @param	int $jpg_quality		qualité de l'image
	 * @return	boolean					vrai si image créée
	 * @author	unknown, Pedro "P3ter" CADETE
	 **/
	public static function makeThumb($src_image, $dest_image, $thumb_width = 48, $thumb_height = 48, $jpg_quality = 90) {

		if(!function_exists('imagecreatetruecolor')) {
			return false;
		}

		# Get dimensions of existing image
		$image = getimagesize($src_image);

		# Check for valid dimensions
		if($image === false || $image[0] <= 0 || $image[1] <= 0) {
			return false;
		}

		# Determine format from MIME-Type
		$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

		# calcul du ration si nécessaire
		if($thumb_width != $thumb_height) {
			# Calcul du ratio
			$x_offset = $y_offset = 0;
			$square_size_w = $image[0];
			$square_size_h = $image[1];
			$ratio_w = $thumb_width / $image[0];
			$ratio_h = $thumb_height / $image[1];
			if($thumb_width == 0)
				$thumb_width = round($image[0] * $ratio_h);
			elseif($thumb_height == 0)
				$thumb_height = round($image[1] * $ratio_w);
			elseif($ratio_w < $ratio_h AND $ratio_w < 1) {
				$thumb_width = round($ratio_w * $image[0]);
				$thumb_height = round($ratio_w * $image[1]);
			} elseif($ratio_h < 1) {
				$thumb_width = round($ratio_h * $image[0]);
				$thumb_height = round($ratio_h * $image[1]);
			} else {
				$thumb_width = $image[0];
				$thumb_height = $image[1];
			}
		} else {
			# Calculate measurements (square crop)
			if($image[0] > $image[1]) {
				# For landscape images
				$x_offset = round(($image[0] - $image[1]) / 2);
				$y_offset = 0;
				$square_size_w = $square_size_h = round($image[0] - $x_offset * 2);
			} else {
				# For portrait and square images
				$x_offset = 0;
				$y_offset = round(($image[1] - $image[0]) / 2);
				$square_size_w = $square_size_h = round($image[1] - $y_offset * 2);
			}
		}

		$canvas = imagecreatetruecolor($thumb_width, $thumb_height);

		# Import image
		set_error_handler(
			function ($errno, $errstr, $errfile) use($src_image) {
				# certains formats d'image ne sont pas gérés par PHP - Ex: webp animé
				if($errfile == __FILE__) {
					plxMsg::Error(PLX_DEBUG ? $errstr : L_PLXMEDIAS_RECREATE_THUMB_ERR . ' ' . basename($src_image)); /** @phpstan-ignore ternary.alwaysFalse */
					return true;
				}

				# Erreur non traitée
				return false;
			},
			E_WARNING
		);

		$isAlpha = false;
		switch( $image['format'] ) {
			case 'jpg':
			case 'jpeg':
				$image_data = imagecreatefromjpeg($src_image);
				break;
			case 'png':
				$image_data = imagecreatefrompng($src_image);
				$isAlpha = true;
				break;
			case 'gif':
				$image_data = imagecreatefromgif($src_image);
				$isAlpha = true;
				break;
			case 'webp':
				$image_data = imagecreatefromwebp($src_image);
				$isAlpha = true;
				break;
			case 'x-ms-bmp':
				if(function_exists('imagecreatefrombmp')) {
					# Only from PHP 7.2.0
					$image_data = imagecreatefrombmp($src_image);
				} else {
					return false;
				}
				break;
			default:
				return false; # Unsupported format
		}

		if($isAlpha) {
			$color = imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
			imagefill($canvas, 0, 0, $color);
			imagesavealpha($canvas, true);
		}

		restore_error_handler();

		# Verify import
		if($image_data == false) return false;

		# Resize and crop
		imagecopyresampled(
			$canvas,
			$image_data,
			0,
			0,
			$x_offset,
			$y_offset,
			$thumb_width,
			$thumb_height,
			$square_size_w,
			$square_size_h
		);

		# Create thumbnail
		switch( strtolower(pathinfo($dest_image, PATHINFO_EXTENSION)) ) {
			case 'jpg':
			case 'jpeg':
				return (imagejpeg($canvas, $dest_image, $jpg_quality) AND is_file($dest_image));
			case 'png':
				return (imagepng($canvas, $dest_image) AND is_file($dest_image));
			case 'gif':
				return (imagegif($canvas, $dest_image) AND is_file($dest_image));
			case 'bmp':
				return (imagebmp($canvas, $dest_image) AND is_file($dest_image));
			case 'webp':
				return (imagewebp($canvas, $dest_image, $jpg_quality) AND is_file($dest_image));
		}

		return false;# Unsupported format
	}

	/**
	 * Méthode qui affiche un message
	 *
	 * @param	string	$msg à afficher
	 * @param	string	$class css à utiliser pour formater l'affichage du message
	 * @param	string	$format_start des balises avant le message
	 * @param	string	$format_end des balises après le message
	 **/
	public static function showMsg($msg, $class='',$format_start='<p class="#CLASS">',$format_end='</p>') {
		$format_start = str_replace('#CLASS',($class != '' ? $class : 'msg'),$format_start);
		echo $format_start.$msg.$format_end;
	}

	/**
	 * Méthode qui retourne l'url de base du site
	 *
	 * exemple de .htaccess avec redirection d'url :
	 * <IfModule mod_rewrite.c>
	 *     RewriteEngine on
	 *     RewriteBase "/"
	 *     RewriteRule ^blog/(core/admin|update)$ apps/PluXml/$1/ [L]
	 *     RewriteRule ^blog/(.*)$  apps/PluXml/$1 [L]
	 *     RewriteRule ^blog$  apps/PluXml/ [L]
	 * </IfModule>
	 *
	 * @param	boolean $with_hostname précise le protocole et le nom d'hôte dans le résultat
	 * @return	string	url de base du site
	 * @author	Jean-Pierre Pourrez @bazooka07
	 **/
	public static function getRacine($with_hostname=true) {

		$pattern1 = null;
		if(!empty($_SERVER['REDIRECT_URL'])) {
			# Ré-écriture de l'url ( urlrewriting )
			$path1 = $_SERVER['REDIRECT_URL'];
			if(!defined('PLX_ADMIN') and !empty($_SERVER['REDIRECT_QUERY_STRING'])) {
				$path1 = preg_replace('#' . $_SERVER['REDIRECT_QUERY_STRING'] . '$#', '', $path1);
				$pattern1 = basename($_SERVER['SCRIPT_NAME'], '.php') . '/';
			}
		} else {
			$path1 = $_SERVER['PHP_SELF'];
			$pattern1 = basename($_SERVER['SCRIPT_NAME']);
		}

		$prefix = '';
		if($with_hostname) {
			# On a besoin du nom d'hôte (hostname), ... Par exemple: sitemap.php, feed.php
			$protocol = (!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) AND strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' )? 'https': 'http';
			$prefix = $protocol . '://' . $_SERVER['HTTP_HOST'];
			if(
				!empty($_SERVER['SERVER_PORT']) and
				is_integer($_SERVER['SERVER_PORT']) and
				$_SERVER['SERVER_PORT'] != 80
			) {
				$prefix .= ':' . $_SERVER['SERVER_PORT'];
			}
		}

		if(defined('PLX_ADMIN')) {
			# Pas de redirection
			return $prefix . preg_replace('#(?:/\w[\w-]+){2}(?:/\w[\w-]+\.php|/)?$#', '/', $path1);
		}

		return $prefix . preg_replace('#' . $pattern1 . '$#', '', $path1);
	}

	/**
	 * Méthode qui retourne une chaine de caractères au hasard
	 *
	 * @param	int $taille	nombre de caractère de la chaine à retourner (par défaut sur 10 caractères)
	 * @return	string	chaine de caractères au hasard
	 **/
	public static function charAleatoire($taille=10) {

		$string = '';
		$chaine = 'abcdefghijklmnpqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		# srand(intval(microtime(true) * 1000000.0));
		srand(); # https://www.php.net/manual/fr/function.srand.php
		for($i=0; $i<$taille; $i++)
			$string .= $chaine[ mt_rand()%strlen($chaine) ];
		return $string;
	}

	/**
	 * Méthode qui coupe une chaine de caractères sur n caractères ou sur n mots
	 *
	 * @param	$str			chaine de caractères à couper
	 * @param	int $length		nombre de caractères ou nombre de mots à garder (par défaut 25)
	 * @param	string $type		à renseigner avec la valeur 'word' pour couper en nombre de mots. Par défaut la césure se fait en nombre de caractères
	 * @param	string $add_text	texte à ajouter après la chaine coupée (par défaut '...' est ajouté)
	 * @return	string		chaine de caractères coupée
	 **/
	public static function strCut($str='', $length=25, $type='', $add_text='...') {
		if($type == 'word') { # On coupe la chaine en comptant le nombre de mots
			$content = explode(' ',$str);
			$length = sizeof($content) < $length ? sizeof($content) : $length;
			return implode(' ',array_slice($content,0,$length)).$add_text;
		} else { # On coupe la chaine en comptant le nombre de caractères
			if(strlen($str) <= $length) {
				return $str;
			}
			return substr($str, 0, $length) . $add_text;
		}
	}

	/**
	 * Méthode qui retourne une chaine de caractères formatée en fonction du charset
	 *
	 * @param	string $str		chaine de caractères
	 * @return	string	chaine de caractères tenant compte du charset
	 **/
	public static function strCheck($str) {

		return htmlspecialchars(trim($str), ENT_SUBSTITUTE | ENT_HTML5, PLX_CHARSET, false);
	}

	/**
	 * Méthode qui retourne une chaine de caractères nettoyée des cdata
	 *
	 * @param	string $str		chaine de caractères à nettoyer
	 * @param	bool $noscript remplace la balise script par la balise div
	 * @return	string	chaine de caractères nettoyée
	 * @author	Stephane F, J.P. Pourrez @bazooka07
	 **/
	public static function cdataCheck($str, $noscript=false) {
		if($noscript) {
			$str = preg_replace('#(<\?,)script#i', '$1div', $str);
		}
		return strtr($str, array(
			'<![CDATA['	=> '&lt;![CDATA[',
			']]>'		=> ']]&gt;',
		));
	}

	/**
	 * Méthode qui retourne une chaine de caractères HTML en fonction du charset
	 *
	 * @param	string $str		chaine de caractères
	 * @return	string	chaine de caractères tenant compte du charset
	 **/
	public static function strRevCheck($str) {

		return html_entity_decode($str,ENT_QUOTES,PLX_CHARSET);
	}

	/**
	 * Méthode qui retourne le type de compression disponible
	 *
	 * @return	string or boolean
	 * @author	Stephane F., Amaury Graillat
	 **/
	public static function httpEncoding() {
		if(headers_sent()){
			$encoding = false;
		}elseif(isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false){
			$encoding = 'gzip';
		}else{
			$encoding = false;
		}
		return $encoding;
	}

	/**
	 * Méthode qui converti les liens relatifs en liens absolus
	 *
	 * @param	string $base	url du site qui sera rajoutée devant les liens relatifs
	 * @param	string $html	chaine de caractères à convertir
	 * @return	string	chaine de caractères modifiée
	 * @author	Stephane F., Amaury Graillat, J.P. Pourrez
	 **/
	public static function rel2abs($base, $html) {

		if (substr($base, -1) != '/')
			$base .= '/';

		# Ne pas convertir Les liens commençant avec href="#....". Liens internes à la page !
		# on protège tous les liens externes au site, et on transforme tous les liens relatifs en absolus
		# on ajoute le hostname si nécessaire
		$mask = '=<<>>=';
		$patterns = array(
			'@(href|src|<object\s[^>]*data)=("|\')(#|[a-z]+:)@i', # lien interne ou utilisation d'un protocole type href="xxxx:...." à protéger.
			'@(href|src|<object\s[^>]*data)=("|\')(?:\./)?([^/])@i' # lieu relatif à transformer
		);
		$replaces = array(
			'$1'.$mask.'$2$3',
			'$1=$2'.$base.'$3'
		);
		$result = preg_replace($patterns, $replaces, $html);

		# on retire la protection des liens externes. Expressions régulières lentes et inutiles
		return str_replace($mask, '=', $result);

	}

	/**
	 * Méthode qui retourne la liste des langues disponibles dans un tableau
	 *
	 * @return	array
	 * @author	J.P. Pourrez, Stephane F.
	 **/
	public static function getLangs() {
		$result = array();
		foreach(
			array_map(
				function($dir1) {
					return preg_replace('#.*/([a-z]{2})$#', '$1', $dir1);
				},
				glob(PLX_CORE . 'lang/*', GLOB_ONLYDIR)
			) as $lang
		) {
			$result[$lang] = (!empty(FLAGS[$lang]) ? FLAGS[$lang] : '🏴‍☠️') . ' ' . $lang;
		}
		return $result;
	}

	/*
	 * Checks if core.php file exists for a language
	 *
	 * @param string $lang for checking
	 * @return boolean
	 * @author J.P. Pourrez @bazooka07
	 **/
	public static function lang_exists($lang) {
		return file_exists(preg_replace('#/lib$#', '/lang/' . $lang . '/core.php', __DIR__));
	}

	/**
	 * Méthode qui empeche de mettre en cache une page
	 *
	 * @param	string $type	type de source
	 * @param	string $charset 		type d'encodage
	 * @return	void
	 * @author	Stephane F., Thomas Ingles
	 **/
	public static function cleanHeaders($type='text/html', $charset=PLX_CHARSET) {
		header_remove();
		header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
		header('Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Cache: no-cache');
		header('Pragma: no-cache');
		header('Content-Type: '.$type.'; charset='.$charset);
	}

	/*
	 * Méthode qui vérifie que la librairie PHPMailer est installée
	 *
	 * @param	none
	 * @return	bool
	 * @author	Jean-Pierre Pourrez @bazooka07
	 **/
	public static function isPHPMailer() {
		return class_exists('PHPMailer\\PHPMailer\\PHPMailer', false);
	}

	/*
	 * Vérifie que la librairie pour se connecter à un serveur STMP de Google est installée
	 *
	 * Authentification OAuth2 - Dépend de PHPMailer
	 * @param	string $provider
	 * @return	bool
	 * @author	Jean-Pierre Pourrez @bazooka07
	 **/
	public static function isOauth2Enabled($provider='Google') {
		# Possible values for $provider : Google, Yahoo, Facebook, ...
		return class_exists('League\\OAuth2\\Client\\Provider\\' . $provider, false);
	}

	/**
	* Méthode d'envoi de mail
	*
	* @param	string $name 			Nom de l'expéditeur
	* @param	string $from 			Email de l'expéditeur
	* @param	string|array $to		Adresse(s) du(des) destinataires(s)
	* @param	string $subject			Objet du mail
	* @param	string $body			contenu du mail
	* @return	bool	renvoie FAUX en cas d'erreur d'envoi
	* @author	J.P. Pourrez (aka bazooka07), Amaury Graillat
	**/
	public static function sendMail($name, $from, $to, $subject, $body, $contentType="text", $cc=false, $bcc=false) {

		if(empty(trim($to)) or empty(trim($subject))) { return false; }

		$headers = array(
			'MIME-Version'				=> '1.0',
			'Content-Type'				=> (($contentType === 'html') ? 'text/html' : 'text/plain') . ';charset=' . PLX_CHARSET,
			'Content-Transfer-Encoding'	=> '8bit',
			'Date'						=> date('D, j M Y G:i:s O'), # Sat, 7 Jun 2001 12:35:58 -0700
			'X-Mailer'					=> 'PHP/' . phpversion()
		);

		if(!empty($from)) {
			$headers['From'] = (!empty($name)) ? $name . " <$from>" : $from;
			$headers['Reply-To'] = $from;
		}

		if(!empty($cc)) {
			$headers['Cc'] = (is_array($cc)) ? implode(', ', $cc) : $cc;
		}

		if(!empty($bcc)) {
			$headers['Bcc'] = (is_array($bcc)) ? implode(', ', $bcc) : $bcc;
		}

		return mail($to, $subject, $body,
			version_compare(PHP_VERSION, '7.2', '>=') ?
				$headers :
				implode("\r\n", array_map(
					function($k, $v) { return "$k: $v"; },
					array_keys($headers),
					array_values($headers)
				)) . "\r\n"
		);
	}

	/**
	* Send an e-mail with PhpMailer class
	* @param string $name Sender's name
	* @param string $from Sender's e-mail address
	* @param string $to Destination e-mail address
	* @param string $subject E-mail subject
	* @param string $body E-mail body content
	* @param boolean $isHtml True if body content use HTML
	* @param array $conf PHPMailer configuration (username, password, ...)
	* @return boolean
	* @author Pedro "P3ter" CADETE, Jean-Pierre Pourrez @bazooka07
	* @throws \PHPMailer\PHPMailer\Exception
	**/
	public static function sendMailPhpMailer($name, $from, $to, $subject, $body, $isHtml, $conf, $debug=false) {
		if(!self::isPHPMailer()) {
			return false;
		}

		$mail = new PHPMailer();
		if ($debug) {
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		}
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->setFrom($from, $name);
		$mail->addAddress($to);
		$mail->Mailer = $conf['email_method'];
		$mail->CharSet = 'UTF-8';
		if ($isHtml) {
			$mail->isHTML(true);
		}
		switch ($conf['email_method']) {
			case 'smtp':
				$mail->isSMTP();
				$mail->Host = $conf['smtp_server'];
				$mail->Port = $conf['smtp_port'];
				$mail->SMTPAuth = true;
				$mail->Username = $conf['smtp_username'];
				$mail->Password = $conf['smtp_password'];
				$mail->SMTPDebug = SMTP::DEBUG_CONNECTION; # https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging
				if(in_array($conf['smtp_security'], array(PHPMailer::ENCRYPTION_STARTTLS, PHPMailer::ENCRYPTION_SMTPS,))) {
					$mail->SMTPSecure = $conf['smtp_security'];
				}
				break;
			case 'smtpoauth':
				if(!self::isOauth2Enabled()) {
					if(class_exists('plxMsg', false)) {
						return plxMsg::Error('Method with OAuth2 denied');
					}

					return false;
				}
				$mail->isSMTP();
				$mail->SMTPAuth = true;
				/*
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
				$mail->Port = 465;
				*/
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
				$mail->Port = 587;
				$mail->AuthType = 'XOAUTH2';
				$params = array(
					'clientId' => $conf['smtpOauth2_clientId'],
					'clientSecret' => $conf['smtpOauth2_clientSecret'],
				);
				switch($conf['smtpOauth2_provider']) {
					case 'Google':
						# https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail_xoauth.phps
						# https://mailtrap.io/blog/phpmailer-gmail/
						$mail->Host = 'smtp.gmail.com';
						$provider = new Google($params);
						break;
					case 'Yahoo':
						$mail->Host = 'smtp.mail.yahoo.com';
						$provider = new Yahoo($params);
						break;
					case 'Microsoft':
						$mail->Host = 'smtp.office365.com';
						$provider = new Microsoft($params);
						break;
					case 'Azure' :
						# https://github.com/PHPMailer/PHPMailer/blob/master/examples/azure_xoauth2.phps
						$mail->Host = 'smtp.office365.com';
						$params['tenantId'] = $conf['smtpOauth2_tenantId'];
						$provider = new Azure($params);
						break;
					default:
						return false;
				}
				$mail->setOAuth(
						new OAuth(
							[
								'provider' => $provider,
								'clientId' => $conf['smtpOauth2_clientId'],
								'clientSecret' => $conf['smtpOauth2_clientSecret'],
								'refreshToken' => $conf['smtpOauth2_refreshToken'],
								'userName' => $conf['smtpOauth2_emailAdress'],
							]
						)
					);
				break;
		}
		return $mail->send();
	}

	/**
	* Méthode qui formate un lien pour la barre des menus
	*
	* @param	string $name	titre du menu
	* @param	string $href	lien du menu
	* @param	string $title	contenu de la balise title
	* @param	string $class	contenu de la balise class
	* @param	string $onclick	contenu de la balise onclick
	* @param	string $extra	texte à afficher
	* @return	string	balise <a> formatée
	* @author	Stephane F., Thomas Ingles
	**/
	public static function formatMenu($name, $href, $title=null, $class=null, $onclick=null, $extra='', $highlight=true) {
		$menu = '';
		$basename = explode('?', basename($href));
		$active = ($highlight AND ($basename[0] == basename($_SERVER['SCRIPT_NAME']))) ? ' active':'';
		if($basename[0]=='plugin.php' AND isset($_GET['p']) AND $basename[1]!='p='.$_GET['p']) $active='';
		$title = $title ? ' title="'.$title.'"':'';
		$class = $class ? ' '.$class:'';
		$onclick = $onclick ? ' onclick="'.$onclick.'"':'';
		$id = ($basename[0] == 'plugin.php') ? strtr($basename[1],'p=','') : strtr($basename[0],'.php','');
		$menu = '<li id="mnu_'.$id.'" class="menu'.$active.$class.'"><a href="'.$href.'"'.$onclick.$title.'>'.$name.$extra.'</a></li>' . PHP_EOL;
		return $menu;
	}

	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ending if the text is longer than length.
	 *
	 * @param	string	$text String to truncate.
	 * @param	integer	$length Length of returned string, including ellipsis.
	 * @param	string	$ending Ending to be appended to the trimmed string.
	 * @param	boolean	$exact If false, $text will not be cut mid-word
	 * @param	boolean	$considerHtml If true, HTML tags would be handled correctly
	 * @return	string	Trimmed string.
	*/
	public static function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
		if ($considerHtml) {

			$lines = '';
			$tag_matchings = '';
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			$entities = '';

			# if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}

			# splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

			foreach ($lines as $line_matchings) {
				# if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					# if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						# do nothing
					# if tag is a closing tag (f.e. </b>)
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						# delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
					# if tag is an opening tag (f.e. <b>)
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						# add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					# add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}

				# calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#\d{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					# the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					# search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#\d{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						# calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								# no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					# maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}

				# if the maximum length is reached, get off the loop
				if($total_length>= $length) {
					break;
				}
			}
		} else {
			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}

		# if the words shouldn't be cut in the middle...
		if (!$exact) {
			# ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (is_integer($spacepos)) {
				# ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}

		# add the defined ending to the text
		$truncate .= $ending;
		/*
		if($considerHtml) {
			# close all unclosed html-tags
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		*/
		return $truncate;

	}

	/**
	 * Protège une chaine contre un null byte
	 *
	 * @param	string $string	chaine à nettoyer
	 * @return	string	chaine nettoyée
	*/
	public static function nullbyteRemove($string) {
		return str_replace("\0", '', $string);
	}

	/**
	 * Contrôle le nom d'un fichier ou d'un dossier
	 *
	 * @param	?string  $src nom d'un fichier
	 * @paraaam bool	$isDir
	 * @return	bool validité du nom du fichier ou du dossier
	*/
	public static function checkSource($src, $isDir=true) {

		if (is_null($src) OR !strlen($src) OR substr($src,-1,1)=="." OR false!==strpos($src, "..")) {
			return false;
		}

		$regex = $isDir ? ',(/\.)|[[:cntrl:]]|(//)|(\\\\)|([\\:\*\?\'\<\>\|]),' : ',[[:cntrl:]]|[/\\:\*\?\'\<\>\|],';
		return !preg_match($regex, $src);
	}

	/**
	 * Formate le nom d'une miniature à partir d'un nom de fichier
	 *
	 * @param	string	$filename nom d'un fichier
	 * @return	string	nom de la miniature au format fichier.tb.ext
	*/
	public static function thumbName($filename) {

		$matches = '';
		if(preg_match('/^(.*\.)(jpe?g|png|gif|bmp|webp)$/iD', $filename, $matches)) {
			return $matches[1].'tb.'.$matches[2];
		} else {
			return $filename;
		}
	}

	/**
	 * Méthode qui minifie un buffer
	 *
	 * @param	string	$buffer chaine de caractères à minifier
	 * @return	string	chaine de caractères minifiée
	 * @author	Frédéric Kaplon, Jean-Pierre Pourrez @bazooka07
	 **/
	public static function minify($buffer) {
		return preg_replace(array_keys(self::MINIFY_PATTERNS), array_values(self::MINIFY_PATTERNS), $buffer);
	}

	/**
	 * Méthode qui converti les urls contenus dans une chaine en liens cliquables.
	 *
	 * @param	string	$str chaîne d'entrée
	 * @param	array	$attributes Optionnel. Si spécifié, ce paramètre doit être un tableau associatif de format $arr['attribute'] = $value.
	 * @return	string	Retourne une copie de la chaîne str dont les urls ont été encapsulées dans des balises <a>.
	 * @author	http://code.seebz.net/p/autolink-php/
	 *	Exemple 1:
	 *		$str = 'A link : http://example.com/?param=value#anchor.';
	 *		$str = autolink($str);
	 *		echo $str; # A link : <a href="http://example.com/?param=value#anchor">http://example.com/?param=value#anchor</a>.
	 *	Exemple 2:
	 *		$str = 'http://example.com/';
	 *		$str = autolink($str, array("target"=>"_blank","rel"=>"nofollow"));
	 *		echo $str; # <a href="http://example.com/" target="_blank" rel="nofollow">http://example.com/</a>
	 **/
	public static function autolink($str, $attributes=array()) {
		$attrs = '';
		foreach ($attributes as $attribute => $value) {
			$attrs .= " {$attribute}=\"{$value}\"";
		}
		$str = ' ' . $str;
		$str = preg_replace('#([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])#i', '$1<a href="$2"'.$attrs.'>$2</a>', $str);
		$str = substr($str, 1);
		return $str;
	}

	public static function debug($obj) {
		echo "<pre>";
		if(is_array($obj) OR is_object($obj))
			print_r($obj);
		else
			echo $obj;
		echo "</pre>";
	}

	/**
	 * Envoie un message vers la console javascript pour aider au déboggage.
	 * @author	J.P. Pourrez alias bazooka07
	 * @version	2017-06-09
	 * */
	public static function debugJS($obj, $msg='') {

		if(!empty($msg)) $msg .= ' = ';
		$msg .= (is_array($obj) OR is_object($obj)) ? print_r($obj, true) : ((is_string($obj)) ? "\"$obj\"" : $obj);
		echo <<< EOT
			<script type="text/javascript">
				console.log(`$msg`);
			</script>
EOT;
	}

	/**
	 * Fonction privée statique recursive qui imprime les options d'une arborescence de fichiers ou dossiers.
	 * @param	string	$root nom du dossier
	 * @param	integer	$level			niveau de profondeur dans l'arborescence des dossiers
	 * @param	string	$prefixParent	prefixe pour l'affichage de la valeur de l'option
	 * @param	string	$choice1		sélection initiale de l'utilisateur. Utilisé seulement au niveau 0
	 * @param	boolean	$modeDir1		mode pour afficher uniquement les dossiers
	 * @return	void					on envoie directemenr le code HTML en sortie
	 * @author	J.P. Pourrez alias bazooka07
	 * */
	private static function _printSelectDir($root, $level, $prefixParent, $choice1='', $modeDir1=true, $textOnly= true) {

		static $firstRootLength = 0;
		static $modeDir = true;
		static $extsText = false;
		static $currentValue = '';

		# initialisation des variables statiques
		if($level == 0) {
			$firstRootLength = strlen($root);
			$modeDir = $modeDir1;
			if(!$modeDir1 and $textOnly) {
				$extsText = 'php css html htm xml js json txt me md';
				# plxUtils::debugJS($extsText, 'extsText');
			}
			$currentValue = $choice1;
		}

		$children = array_filter(scandir($root),
			function ($item) use(&$modeDir, &$root, &$extsText) {# détermine s'il s'agit de fichier ou dossier php 5.3+
				$ext = pathinfo($item,PATHINFO_EXTENSION);
				return  ($item[0] != '.' and
					( (is_dir($root.$item) ) or
						(!$modeDir and (!empty($ext) and (strpos($extsText,$ext) !== false) or empty($extsText)))
					)
				);
			}
		);
		natsort($children);

		if(!empty($children)) {
			$level++;
			$cnt = count($children);
			foreach($children as $child) {
				$cnt--;
				$prefix = $prefixParent;
				# http://www.utf8-chartable.de/unicode-utf8-table.pl?start=9472&unicodeinhtml=dec
				if($cnt<=0) {
					$prefix .= '└ '; # espace insécable !
					$next = ' '; # espace insécable !
				} else {
					$prefix .= '├ '; # espace insécable !
					$next = '│'; # espace insécable !
				}
				$dirOk = (is_dir($root.$child));
				$next .= str_repeat(' ', 3); # espace insécable ! 3 = strlen($prefix.$next)
				$dataLevel = 'level-'.str_repeat('X', $level);
				$value = substr($root.$child, $firstRootLength);
				$selected = ($value == rtrim($currentValue, '/')) ? ' selected' : '';
				$caption = basename($value);
				$classList = array();
				#if(strpos($currentValue, dirname($value)) === 0)
				if(strpos($value, dirname($value)) === 0)
					$classList[] = 'visible';
				if(!$modeDir and $dirOk)
					$classList[] = 'folder';

				$classAttr = (!empty($classList)) ? ' class="'.implode(' ', $classList).'"' : '';

				if($dirOk) { # pour un dossier
					if($modeDir) {
						echo <<<EOT
							<option value="$value/"$classAttr data-level="$dataLevel" $selected>$prefix$caption/</option>

EOT;
					} else {
						echo <<<EOT
							<option disabled value=""$classAttr data-level="$dataLevel">$prefix$caption/</option>

EOT;
					}
					plxUtils::_printSelectDir($root.$child.'/', $level, $prefixParent.$next);
				} else { # pour un fichier
					echo <<<EOT
						<option value="$value"$classAttr data-level="$dataLevel"$selected>$prefix$caption</option>

EOT;
				}
			}
		}
	}

	/**
	 * Function publique pour afficher l'arborescence de dossiers et fichiers dans un tag <select..>.
	 * Since 5.8
	 * @param	string $name nom de l'input dans le formulaire
	 * @param	string $currentValue sélection initiale de l'utilisateur
	 * @param	string $root dossier initial dans l'arborescence
	 * @param	string $class Classe css a appliquer au sélecteur #sudwebdesign
	 * @param	boolean $modeDir évite l'affichage des fichiers (dans la gestion des médias, par Ex., à la différence d'un thème)
	 * @param	bool|string $id : si vrai génère un id à partir du nom du champ, sinon génère l'id à partir du paramètre name
	 * @return	void
	 * @author	J.P. Pourrez alias bazooka07, T. Ingles @sudwebdesign
	 * $modeDir=true	pour ne choisir que les dossiers : voir plxMedias contentFolder()
	 * $modeDir=false	pour ne choisir que les fichiers du thème
	 * */
	public static function printSelectDir($name, $currentValue, $root, $class='', $modeDir=true, $id=true) {
	   $value = ($modeDir) ? '.' : '';
		$selected = ($value == $currentValue) ? ' selected': '';
		$disabled = (!$modeDir) ? ' disabled': '';
		$class = (!empty($class) ? $class . ' ' : '') . 'scan-folders fold';
		if(!$modeDir) {
			$class .= ' data-files';
		}
?>
<select id="<?= ($id === true) ? 'id_' . $name : $id ?>" name="<?= $name ?>" class="<?= $class ?>" onchange="this.form.submit();">
	<option <?= $disabled ?> value="<?= $value ?>" <?= $selected ?>><?= L_PLXMEDIAS_ROOT ?>/</option>
<?php
		if(substr($root, -1) != '/') {
			$root .= '/';
		}
		plxUtils::_printSelectDir($root, 0, str_repeat(' ', 3), $currentValue, $modeDir);
?>
</select>
<?php
	}

	static private function _printLinkCss($aFiles, $mask, $root) {
		foreach($aFiles as $filename) {
			if(!file_exists($filename)) {
				continue;
			}

			$minify = preg_replace('#\.css$#', '.min.css', $filename);
			$href = ($minify != $filename and file_exists($minify) and filemtime($minify) >= filemtime($filename)) ? $minify : $filename;
			if(!empty($mask)) {
				$href = preg_replace($mask , '', $href);
			}
			if(is_string($root)) {
				$href = preg_replace('#^' . PLX_ROOT . '#' , $root, $href);
			}
			if(PLX_DEBUG) { // @phpstan-ignore if.alwaysFalse
				$href .= '?d='.base_convert(strval(filemtime($href) & 4194303), 10, 36);
			}
?>
	<link rel="stylesheet" type="text/css" href="<?= $href ?>" media="screen" />
<?php
		}

		# extra
		$href = PLX_ROOT . 'favicon.png';
		if(file_exists($href)) {
			if(!empty($mask)) {
				$href = preg_replace($mask , '', $href);
			}
			if(is_string($root)) {
				$href = preg_replace('#^' . PLX_ROOT . '#' , $root, $href);
			}
?>
	<link rel="icon" href="<?= $href ?>" />
<?php
		}
	}

	/**
	 * Méthode qui affiche la balise <link> partir d'un nom de fichier
	 * @param	string	$file	nom d'un fichier
	 * @return	void
	 * @author J.P. Pourrez alias bazooka07, T. Ingles @sudwebdesign
	 */
	public static function printLinkCss($file=null) {

		if(empty($file)) {
			$mask = false;
			$root = false;
			if(!class_exists('plxShow', false)) {
				/*
				 * Mode intelligent pour les fichiers :
				 *
				 * install/index.php
				 * update/index.php
				 * core/admin/auth.php
				 * core/admin/top.php
				 * */

				if(PLX_ROOT == '../../' and defined('PLX_ADMIN')) { // @phpstan-ignore equal.alwaysTrue
					$adminPath = PLX_ROOT . preg_replace('#.*/([^/\.]+/[^/\.]+)(?:/\w+\.php|/)?$#', '$1/', $_SERVER['SCRIPT_NAME']);
					$mask = '#^' . $adminPath . '#';
				} elseif(PLX_ROOT == '../') { // @phpstan-ignore equal.alwaysFalse
					$css = 'theme/plucss.css';
					$files = glob(PLX_ROOT . '*/*/' . $css);
					if(empty($files)) {
						return;
					}
					$adminPath = preg_replace('#' . $css . '$#', '', $files[0]);
				} else {
					return;
				}

				$list = array(
					$adminPath . 'theme/plucss.css',
					$adminPath . 'theme/theme.css',
					$adminPath . 'theme/fonts/fontello.css',
				);
				if(defined('PLX_CUSTOM_ADMINCSS_FILE')) {
					$list[] = PLX_CUSTOM_ADMINCSS_FILE;
				}
				if(defined('PLX_PLUGINS_CSS_PATH')) {
					$list[] = PLX_ROOT . PLX_PLUGINS_CSS_PATH . 'admin.css';
				}
			} else {
				# côté site
				$plxShow = plxShow::getinstance();
				$list = array_filter(
					glob(PLX_ROOT . $plxShow->plxMotor->aConf['racine_themes'] . $plxShow->plxMotor->style . '/css/*.css'),
					function ($value) {
						return !preg_match('#\.min\.css$#', $value);
					}
				);
				if(!empty($list) and defined('PLX_PLUGINS_CSS_PATH')) {
					$list[] = PLX_ROOT . PLX_PLUGINS_CSS_PATH . 'site.css';
				}
				$root = ($plxShow->plxMotor->aConf['urlrewriting'] != 1) ? '' : $plxShow->plxMotor->racine_path;
			}
			self::_printLinkCss($list, $mask, $root);
		} elseif(is_file(PLX_ROOT . $file)) {
			if(defined('PLX_ADMIN')) {
				$adminPath = PLX_ROOT . preg_replace('#.*/([^/\.]+/[^/\.]+)(?:/\w+\.php|/)?$#', '$1/', $_SERVER['SCRIPT_NAME']);
				$href = preg_replace('#^' . $adminPath . '#', '', $file);
			} else {
				$plxMotor = plxMotor::getinstance();
				$href = $plxMotor->urlRewrite($file);
			}
			$href .= '?d='.base_convert(strval(filemtime(PLX_ROOT.$file) & 4194303), 10, 36); # 4194303 === 2 puissance 22 - 1; base_convert(4194303, 10, 16) -> 3fffff; => 48,54 jours
?>
	<link rel="stylesheet" type="text/css" href="<?= $href ?>" media="screen" />
<?php
		}
	}

}

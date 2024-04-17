<?php

/**
 * Classe plxUtils rassemblant les fonctions utiles à PluXml
 *
 * @package PLX
 * @author	Florent MONTHEL et Stephane F
 **/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

const AUTOLOADER = PLX_CORE . 'vendor/autoload.php';
# See vendor/composer/platform_check.php
if (PHP_VERSION_ID >= 80100 and file_exists(AUTOLOADER)) { # required by Composer
	require AUTOLOADER;
}

class plxUtils {

	const REMOVE_WORDS = array(
		'en' => 'an?|as|at|before|but|by|for|from|is|in(?:to)?|like|off?on(?:to)?|per|since|than|the|this|that|to|up|via|with',
		'de' => 'das|der|die|fur|am',
		'fr' => 'a|de?|des|du|e?n|la|le|une?|vers'
	);
	const DELTA_PAGINATION = 3;
	const RANDOM_STRING = 'abcdefghijklmnpqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const THUMB_WIDTH = 48;
	const THUMB_HEIGHT = 48;
	const PATTERN_PAGINATION = '#\bpage=%d\b#'; # for hacking against printf()
	const ALLOWED_HTML_TAGS = '<p><div><ul><li><ol><br><a><img><i><em><sup><span><strong>';

	/**
	 * Méthode qui vérifie si une variable est définie.
	 * Renvoie la valeur de la variable ou la valeur par défaut passée en paramètre
	 *
	 * @param	var			string	variable à tester
	 * @param	default		string	valeur par défaut
	 * @return	string		valeur de la variable ou valeur par défaut passée en paramètre
	*/
	public static function getValue(&$var, $default='') {
		return isset($var) ? $var : $default;
	}

	/**
	 * Méthode qui vérifie si une variable est définie sous forme de tableau à 2 dimensions.
	 * Renvoie la valeur de la variable ou la valeur par défaut passée en paramètre.
	 *
	 * Utilisé pour l'analyse des fichiers xml avec xml_parder.
	 * @param	values		array	tableau à tester
	 * @param	tag		    array   index dans le tableau ci-dessus
	 * @param   index       integer index dans le tableau tag
	 * @param   string	    default valeur par défaut si le tableau ou la cellule n'existent pas
	 * @return	string		valeur de la cellule par défaut
	 * @author  Jean-Pierre Pourrez "bazooka07"
	*/
	public static function getTagIndexValue(&$tag, &$values, $index, $default='') {
		if(!isset($tag) or !is_array($tag) or empty($tag) or !isset($values) or !isset($values[$tag[$index]]['value'])) {
			return $default;
		}

		return $values[$tag[$index]]['value'];
	}

	/**
	 * Wrapper de la methode getTagIndexValue() avec $index = 0.
	 *
	 * */
	public static function getTagValue(&$tag, &$values, $default='') {
		return self::getTagIndexValue($tag, $values, 0, $default);
	}

	/**
	 * Méthode qui retourne un tableau contenu les paramètres passés dans l'url de la page courante
	 *
	 * @return	array	tableau avec les paramètres passés dans l'url de la page courante
	 **/
	public static function getGets() {

		if(!empty($_SERVER['QUERY_STRING']))
			return htmlspecialchars(strip_tags($_SERVER['QUERY_STRING']),  ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, false);
		else
			return false;
	}

	/**
	 * Méthode qui supprime les antislashs
	 *
	 * @param	content				variable ou tableau
	 * @return	array ou string		tableau ou variable avec les antislashs supprimés
	 * @author  J.P. Pourrez aka bazooka07
	 **/
	public static function unSlash($content) {

		 # On traite un tableau
		if(is_array($content)) {
			$new_content = array();
			foreach($content as $k=>$v) { # On parcourt le tableau
				if(is_array($v)) {
					$new_content[$k] = array();
					foreach($v as $key=>$val)
						$new_content[$k][$key] = self::unSlash($val);
				} else {
					$new_content[$k] = stripslashes($v);
				}
			}
			return $new_content;
		}

		# On traite une chaine
		return stripslashes($content);
	}

	/**
	 * Méthode qui vérifie le bon formatage d'une adresse email
	 *
	 * @param	mail		adresse email à vérifier
	 * @return	boolean		vrai si adresse email bien formatée
	 **/
	public static function checkMail($mail) {

		if (strlen($mail) > 80) {
			return false;
		}

		return filter_var($mail, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Méthode qui vérifie si l'url passée en paramètre correspond à un format valide
	 *
	 * @param	site		url d'un site
	 * @return	boolean		vrai si l'url est bien formatée
	 **/
	public static function checkSite(&$site, $reset=true) {

		$site = preg_replace('#([\'"].*)#', '', $site);

		if(isset($site[0]) AND $site[0]=='?') return true; # url interne commençant par ?
		# On vérifie le site via une expression régulière
		# Méthode imme_emosol - http://mathiasbynens.be/demo/url-regex
		# modifiée par Amaury Graillat pour prendre en compte les tirets dans les urls
		if(preg_match('@(https?|s?ftp)://(-\.)?([^\s/?\.#]+\.?)+([/?][^\s]*)?$@iS', $site))
				return true;
		else {
			if($reset) $site='';
			return false;
		}
	}

	/**
	 * Méthode qui vérifie le format d'une adresse ip
	 *
	 * @param	ip			adresse ip à vérifier
	 * @return	boolean		vrai si format valide
	 **/
	public static function isValidIp($ip) {

		if($ip=='::1') return false;
		$ipv4 = '/((^|\.)(2[0-5]{2}|[01][0-9]{2}|[0-9]{1,2})(?=\.|$)){4}/';
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

		return self::isValidIp($ip) ? $ip : $localIP;
	}

	/**
	 * Méthode qui affiche une liste de sélection
	 *
	 * @param	name		nom de la liste
	 * @param	array		valeurs de la liste sous forme de tableau (nom, valeur)
	 * @param	selected	valeur par défaut
	 * @param	readonly	vrai si la liste est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @param	id			si vrai génère un id à partir du nom du champ, sinon génère l'id à partir du paramètre
	 * @return	self
	 **/
	public static function printSelect($name, $array, $selected='', $readonly=false, $class='', $id=true) {

		if(!is_array($array)) $array=array();

		if(is_bool($id))
			$id = ($id ? ' id="id_'.$name.'"' : '');
		else
			$id = ($id!='' ? ' id="'.$id.'"' : '');

		if($readonly)
			echo '<select'.$id.' name="'.$name.'" disabled="disabled" class="readonly'.($class!=''?' '.$class:'').'">'."\n";
		else
			echo '<select'.$id.' name="'.$name.'"'.($class!=''?' class="'.$class.'"':'').'>'."\n";
		foreach($array as $a => $b) {
			if(is_array($b)) {
				echo '<optgroup label="'.$a.'">'."\n";
				foreach($b as $c=>$d) {
					if($c == $selected)
						echo "\t".'<option value="'.$c.'" selected="selected">'.$d.'</option>'."\n";
					else
						echo "\t".'<option value="'.$c.'">'.$d.'</option>'."\n";
				}
				echo '</optgroup>'."\n";
			} else {
				if(strval($a) == $selected)
					echo "\t".'<option value="'.$a.'" selected="selected">'.$b.'</option>'."\n";
				else
					echo "\t".'<option value="'.$a.'">'.$b.'</option>'."\n";
			}
		}
		echo '</select>'."\n";
	}

	/**
	 * Méthode qui affiche une zone de saisie
	 *
	 * @param	name		nom de la zone de saisie
	 * @param	value		valeur contenue dans la zone de saisie
	 * @param	type		type du champ (text, password, hidden)
	 * @param	size		longueur du champ - nombre maximal de caractères pouvant être saisis (par défaut 50-255)
	 * @param	readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @param	placeholder valeur du placeholder du champ (html5)
	 * @param	extra		extra paramètre pour du javascript par exemple (onclick)
	 * @param	required	permet de rendre le champ obligatoire
	 * @return	self
	 * @author	unknow, Pedro "P3ter" CADETE
	 **/
	public static function printInput($name, $value='', $type='text', $sizes='50-255', $readonly=false, $className='', $placeholder='', $extra='', $required=false) {

		 $params = array(
			'id="id_'.$name.'"',
			'name="'.$name.'"',
			'type="'.$type.'"'
		 );
		 if($value != '') # take care with 0 value
			 $params[] = 'value="'.$value.'"';
		 if(!empty($extra))
			 $params[] = $extra;
		 if($type != 'hidden') {
			$className = explode(' ', trim($className));
			if($readonly === true)
				$params[] = $className[] = 'readonly';
			elseif($required === true)
				$params[] = $className[] = 'required';# Note : [L'attribut required n'est pas autorisé sur les entrées pour lesquelles l'attribut readonly est spécifié.](https://developer.mozilla.org/fr/docs/Web/HTML/Attributes/readonly#sect2)
			if(!empty($className)) # fix double attribut 'class' class="readonly"
				$params[] = 'class="'.$className = implode(' ', $className).'"';
			if(in_array($type, explode(' ','text search url tel email password number')))
				$params[] = 'placeholder="'.($placeholder?$placeholder:' ').'"';# Petit hack pour trouver les champs vide en css input:not(:placeholder-shown)
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
	 * @param	string $value		valeur correspond au radio bouton
	 * @param	string $className	class css à utiliser pour formater l'affichage
	 * @param	string $checked		valeur par défaut
	 * @param	boolean $required	permet de rendre le champ obligatoire
	 * @return	self
	 * @author	Pedro "P3ter" CADETE
	 **/
	public static function printInputRadio($name, $array, $checked='', $className='', $extra='') {

		$params = array(
			'id="id_'.$name.'"',
			'name="'.$name.'"',
		);
		if(!empty($extra)) {
			$params[] = $extra;
		}
		if(!empty($className)) {
			$params[] = 'class="'.$className.'"';
		}
		foreach($array as $a => $b) {
			if ($a == $checked) {
				echo '<input type="radio" value="'.$a.'" '.implode(' ', $params).' checked>&nbsp;'.$b.'<br>';
			}
			else {
				echo '<input type="radio" value="'.$a.'" '.implode(' ', $params).'>&nbsp;'.$b.'<br>';
			}
		}
	}

	/**
	 * Méthode qui affiche une zone de texte
	 *
	 * @param	string	name		nom de la zone de texte
	 * @param	string	value		valeur contenue dans la zone de texte
	 * @param	string	cols		nombre de caractères affichés par colonne
	 * @param	string	rows		nombre de caractères affichés par ligne
	 * @param	boolean	readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	string	class		class css à utiliser pour formater l'affichage
	 * @param	boolean	extra		extra permet d'ajouter un élément HTML (exemple : un "onclick" en javascript)
	 * @return	self
	 */
	public static function printArea($name, $value='', $cols='', $rows='', $readonly=false, $className='full-width', $extra='') {
		$attrs = array (
				'id="id_' . $name . '"',
				'name="' . $name . '"'
		);
		if (!empty($cols) and is_integer($cols)) {
			$attrs[] = 'cols="' . $cols . '"';
		}
		if (!empty($rows) and is_integer($rows)) {
			$attrs[] = 'rows="' . $rows . '"';
		}
		$classList = explode(' ', trim($className));
		if ($readonly === true) {
			$attrs[] = $classList[] = 'readonly';
		}
		if (!empty($classList)) {
			$attrs[] = 'class="' . implode(' ', $classList) . '"';
		}
		if (!empty($extra)) {
			$attrs[] = $extra;
		}
		echo '<textarea ' . implode(' ', $attrs) . '>' . $value . '</textarea>';
	}

	/**
	 * Méthode qui teste si un fichier est accessible en écriture
	 *
	 * @param	file		emplacement et nom du fichier à tester
	 * @param	format		format d'affichage
	 **/
	public static function testWrite($file, $format="<li><span style=\"color:#color\">#symbol #message</span></li>\n") {

		if(is_writable($file)) {
			$output = str_replace('#color', 'green', $format);
			$output = str_replace('#symbol', '&#10004;', $output);
			$output = str_replace('#message', sprintf(L_WRITE_ACCESS, $file), $output);
			echo $output;
		} else {
			$output = str_replace('#color', 'red', $format);
			$output = str_replace('#symbol', '&#10007;', $output);
			$output = str_replace('#message', sprintf(L_WRITE_NOT_ACCESS, $file), $output);
			echo $output;
		}
	}

	/**
	 * Méthode qui teste si le module apache mod_rewrite est disponible
	 *
	 * Cette fonction ne marche que si PHP est un module d'Apache.
	 * Dans le cas contraire, on retourne true par défaut ( serveur php-fpm ou mode fast-CGI )
	 * @param	io			affiche à l'écran le résultat du test si à VRAI
	 * @param	format		format d'affichage
	 * @return	boolean		retourne vrai si le module apache mod_rewrite est disponible
	 * @author	Stephane F, Jean-Pierre Pourrez "bazooka07"
	 **/
	public static function testModRewrite($io=true, $format='<li><span style="color:#color">#symbol #message</span></li>' . PHP_EOL) {

		if ($io == true) {
			$replaces = array(
				'#color'	=> '#c87913',
				'#symbol'	=> '?',
				'#message'	=> L_MODREWRITE_AVAILABLE,
			);
		}

		if (function_exists('apache_get_modules')) {
			$test = in_array('mod_rewrite', apache_get_modules());
			if ($io != true) {
				return $test;
			}
			if ($test) {
				# Success !
				$replaces = array(
					'#color'	=> 'green',
					'#symbol'	=> '&#10004;',
					'#message'	=> L_MODREWRITE_AVAILABLE,
				);
			} else {
				# Echec !
				$replaces = array(
					'#color'	=> 'red',
					'#symbol'	=> '&#10008;',
					'#message'	=> L_MODREWRITE_NOT_AVAILABLE,
				);
			}
		} else {
			# Impossible d'évaluer si Apache a un module "rewrite" activé.
			# On autorise l'urlrewriting par défaut
			if ($io != true) {
				return true;
			}
		}

		echo strtr($format, $replaces);
	}

	/**
	 * Méthode qui teste si la fonction php mail est disponible
	 *
	 * @param	io			affiche à l'écran le résultat du test si à VRAI
	 * @param	format		format d'affichage
	 * @return	boolean		retourne vrai si la fonction php mail est disponible
	 * @author	Stephane F
	 **/
	public static function testMail($io=true, $format="<li><span style=\"color:#color\">#symbol #message</span></li>\n") {

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
	 * @param	format		format d'affichage
	 * @author	Stephane F
	 **/
	public static function testLibGD($format="<li><span style=\"color:#color\">#symbol #message</span></li>\n") {

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
	 * @param	format		format d'affichage
	 *
	 **/
	public static function testLibXml($format="<li><span style=\"color:#color\">#symbol #message</span></li>\n") {

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
	 * @param	str			chaine de caractères à formater
	 * @param	charset		charset à utiliser dans le formatage de la chaine (par défaut utf-8)
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

		if (preg_match('#^(?:https?|s?ftp)://#', $url)) {
			# adresse url absolue
			return $url;
		}

		$clean_url = self::translitterate(trim(html_entity_decode($url)));

		if($remove && defined('PLX_SITE_LANG') && array_key_exists(PLX_SITE_LANG, self::REMOVE_WORDS)) {
			$clean_url = preg_replace('@\b(' . self::REMOVE_WORDS[PLX_SITE_LANG] . ')\b@u', $replace, $clean_url);
		}

		// remove accents
		$clean_url = self::removeAccents($clean_url, PLX_CHARSET);

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
	 * @param	str			chaine de caractères à formater
	 * @return	string		nom d'url valide
	 **/
	public static function title2url($str) {

		$str = strtolower(self::removeAccents($str,PLX_CHARSET));
		$str = preg_replace('/[^[:alnum:]]+/',' ',$str);
		return strtr(trim($str), ' ', '-');
	}

	/**
	 * Méthode qui convertit une chaine de caractères au format valide pour un nom de fichier
	 *
	 * @param	str			chaine de caractères à formater
	 * @return	string		nom de fichier valide
	 **/
	public static function title2filename($str) {

		$str = strtolower(self::removeAccents($str,PLX_CHARSET));
		$str = str_replace('|','',$str);
		$str = preg_replace('/\.{2,}/', '.', $str);
		$str = preg_replace('/[^[:alnum:]|.|_]+/',' ',$str);
		return strtr(ltrim(trim($str),'.'), ' ', '-');
	}

	/**
	 * Méthode qui convertit un chiffre en chaine de caractères sur une longueur de n caractères, completée par des 0 à gauche
	 *
	 * @param	num					chiffre à convertir
	 * @param	length				longueur de la chaine à retourner
	 * @return	string				chaine formatée
	 **/
	public static function formatRelatif($num, $lenght) {

		$fnum = str_pad(abs($num), $lenght, '0', STR_PAD_LEFT);
		if($num > -1)
			return '+'.$fnum;
		else
			return '-'.$fnum;
	}

	/**
	 * Méthode qui écrit dans un fichier
	 * Mode écriture seule; place le pointeur de fichier au début du fichier et réduit la taille du fichier à 0. Si le fichier n'existe pas, on tente de le créer.
	 *
	 * @param	xml					contenu du fichier
	 * @param	filename			emplacement et nom du fichier
	 * @return	boolean				retourne vrai si l'écriture s'est bien déroulée
	 **/
	public static function write($content, $filename) {

		try {
			$newFilename = $filename . '.tmp';
			if(file_exists($filename)) {
				# On crée le fichier temporaire
				file_put_contents($newFilename, trim($content));
				unlink($filename);
				rename($newFilename, $filename); # On renomme le fichier temporaire avec le nom de l'ancien
			} else {
				file_put_contents($filename, trim($content));
			}
			# On place les bons droits
			chmod($filename,0644);
			# On retourne le résultat
			return (file_exists($filename) AND !file_exists($newFilename));
		} catch(Exception $e) {
			return false;
		}
	}

	/**
	 * Méthode qui formate l'affichage de la taille d'un fichier
	 *
	 * @param	filsize				taille en octets d'un fichier
	 * @return	string				chaine d'affichage formatée
	 **/
	public static function formatFilesize($bytes) {

		if ($bytes < 1024) return $bytes.' B';
		elseif ($bytes < 1048576) return round($bytes / 1024, 2).' Kb';
		elseif ($bytes < 1073741824) return round($bytes / 1048576, 2).' Mb';

	}

	/**
	 * Méthode qui crée la miniature d'une image
	 *
	 * @param	src_image		emplacement et nom du fichier source
	 * @param	dest_image		emplacement et nom de la miniature créée
	 * @param	thumb_width		largeur de la miniature
	 * @param	thumb_height	hauteur de la miniature
	 * @param	quality			qualité de l'image
	 * @return	boolean			vrai si image créée
	 * @author	unknown, Pedro "P3ter" CADETE
	 **/
	public static function makeThumb($src_image, $dest_image, $thumb_width = self::THUMB_WIDTH, $thumb_height = self::THUMB_HEIGHT, $jpg_quality = 90) {

		if(!function_exists('imagecreatetruecolor')) return false;

		# Get dimensions of existing image
		$image = getimagesize($src_image);

		# Check for valid dimensions
		if(!$image || $image[0] <= 0 || $image[1] <= 0) return false;

		# Determine format from MIME-Type
		$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

		$alpha = true; # Allow Transparency
		$image_data = false;

		# Import image
		switch( $image['format'] ) {
			case 'jpg':
			case 'jpeg':
				$image_data = imagecreatefromjpeg($src_image);
				$alpha = false;# No Transparency
				break;
			case 'png':
				$image_data = imagecreatefrompng($src_image);
				break;
			case 'gif':
				$image_data = imagecreatefromgif($src_image);
				break;
			case 'webp':# Unsupported Animated WebP (VP8X) warn hidden
				$image_data = @imagecreatefromwebp($src_image);# PHP 5.4 min
				break;
			case 'x-ms-bmp':
				if(function_exists('imagecreatefrombmp')) {# PHP 7.2 min
					$image_data = @imagecreatefrombmp($src_image);
				}
				break;
			default:
				return false; # Unsupported format
		}

		# Verif import
		if(!$image_data) return false;

		$x_offset = $y_offset = 0;
		# calcul du ratio si nécessaire
		if($thumb_width!=$thumb_height) {
			# Calcul du ratio
			$square_size_w = $image[0];
			$square_size_h = $image[1];
			$ratio_w = $thumb_width / $image[0];
			$ratio_h = $thumb_height / $image[1];
			if($thumb_width == 0)
				$thumb_width = $image[0] * $ratio_h;
			elseif($thumb_height == 0)
				$thumb_height = $image[1] * $ratio_w;
			elseif($ratio_w < $ratio_h AND $ratio_w < 1) {
				$thumb_width = $ratio_w * $image[0];
				$thumb_height = $ratio_w * $image[1];
			} elseif($ratio_h < 1) {
				$thumb_width = $ratio_h * $image[0];
				$thumb_height = $ratio_h * $image[1];
			} else {
				$thumb_width = $image[0];
				$thumb_height = $image[1];
			}
			$thumb_width = intval($thumb_width);
			$thumb_height = intval($thumb_height);
		}

		# Calculate measurements (square crop)
		if($thumb_width==$thumb_height) {
			if($image[0] > $image[1]) {
				# For landscape images
				$x_offset = intval(($image[0] - $image[1]) / 2);
				$square_size_w = $square_size_h = $image[0] - ($x_offset * 2);
			} else {
				# For portrait and square images
				$y_offset = intval(($image[1] - $image[0]) / 2);
				$square_size_w = $square_size_h = $image[1] - ($y_offset * 2);
			}
		}

		# Create canvas
		$canvas = imagecreatetruecolor($thumb_width, $thumb_height);
		if($alpha) {# Transparency
			$alpha = imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
			imagefill($canvas, 0, 0, $alpha);
			imagealphablending($canvas, false);
			imagesavealpha($canvas, true);
		}

		# Resize and crop
		if( imagecopyresampled(
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
		)) {

			# Create thumbnail
			switch( strtolower(preg_replace('/^.*\./', '', $dest_image)) ) {
				case 'jpg':
				case 'jpeg':
					return (imagejpeg($canvas, $dest_image, $jpg_quality) AND is_file($dest_image));
					break;
				case 'png':
					return (imagepng($canvas, $dest_image) AND is_file($dest_image));
					break;
				case 'gif':
					return (imagegif($canvas, $dest_image) AND is_file($dest_image));
					break;
				case 'bmp':
					return (imagebmp($canvas, $dest_image) AND is_file($dest_image));
					break;
				case 'webp':
					return (imagewebp($canvas, $dest_image, $jpg_quality) AND is_file($dest_image));
					break;
				default:
					return false;# Unsupported format
				break;
			}

		} else {
			return false;
		}

	}

	/**
	 * Méthode qui affiche un message
	 *
	 * @param	string	message à afficher
	 * @param	string	classe css à utiliser pour formater l'affichage du message
	 * @param	string	format des balises avant le message
	 * @param	string	format des balises après le message
	 **/
	public static function showMsg($msg, $class='',$format_start='<p class="#CLASS">',$format_end='</p>') {
		$format_start = str_replace('#CLASS',($class != '' ? $class : 'msg'),$format_start);
		echo $format_start.$msg.$format_end;
	}

	/**
	 * Méthode qui retourne l'url de base du site
	 *
	 * @return	string	url de base du site
	 **/
	public static function getRacine() {
		$protocol = (!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) AND strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' )? 'https://': 'http://';
		$servername = $_SERVER['HTTP_HOST'];
		$serverport = (preg_match('@:\d+@', $servername) OR $_SERVER['SERVER_PORT'])=='80' ? '' : ':'.$_SERVER['SERVER_PORT'];
		$dirname = preg_replace('@/(?:core|plugins)/.*@', '', dirname($_SERVER['SCRIPT_NAME']));
		$racine = rtrim($protocol . $servername . $serverport . $dirname, '/\\') . '/';
		if(!self::checkSite($racine, false)) {
			die('Error: wrong or invalid url');
		}

		return $racine;
	}

	/**
	 * Méthode qui retourne une chaine de caractères au hasard
	 *
	 * @param	taille	nombre de caractère de la chaine à retourner (par défaut sur 10 caractères)
	 * @return	string	chaine de caractères au hasard
	 **/
	public static function charAleatoire($taille=10) {
		mt_srand((float) microtime() * 1000000);
		$mod = strlen(self::RANDOM_STRING);
		$string = '';
		for($i=$taille; $i > 0; $i--) {
			$string .= self::RANDOM_STRING[ mt_rand() % $mod ];
		}

		return $string;
	}

	/**
	 * Méthode qui coupe une chaine de caractères sur n caractères ou sur n mots
	 *
	 * @param	str			chaine de caractères à couper
	 * @param	length		nombre de caractères ou nombre de mots à garder (par défaut 25)
	 * @param	type		à renseigner avec la valeur 'word' pour couper en nombre de mots. Par défaut la césure se fait en nombre de caractères
	 * @param	add_text	texte à ajouter après la chaine coupée (par défaut '...' est ajouté)
	 * @return	string		chaine de caractères coupée
	 **/
	public static function strCut($str='', $length=25, $type='', $add_text='...') {
		if($type == 'word') { # On coupe la chaine en comptant le nombre de mots
			$content = explode(' ',$str);
			$length = sizeof($content) < $length ? sizeof($content) : $length;
			return implode(' ',array_slice($content,0,$length)).$add_text;
		} else { # On coupe la chaine en comptant le nombre de caractères
			return strlen($str) > $length ? substr($str, 0, $length) . $add_text : $str;
		}
	}

	/**
	 * Méthode qui retourne une chaine de caractères formatée en fonction du charset
	 *
	 * @param	str		chaine de caractères
	 * @param	cdata	encapsule str dans <![CDATA[ ]]> si true et str non nulle
	 * @param	tags	balises HTML autorisées dans <![CDATA[]]> ou null
	 * @return	string	chaine de caractères tenant compte du charset
	 **/
	public static function strCheck($str, $cdata=false, $tags=self::ALLOWED_HTML_TAGS) {

		$str = trim($str);
		if ($str === '') {
			return '';
		}

		if ($cdata) {
			# caractère " interdit. Remplacer par &quot; si besoin à la saisie
			return '<![CDATA[' . strip_tags($str, $tags) . ']]>';
		}

		# ENT_COMPAT : Convertit les guillemets doubles, et ignore les guillemets simples.
		# les caractères suivants seont convertis en entités HTML : & > < "
		return htmlspecialchars($str, ENT_COMPAT | ENT_HTML5, PLX_CHARSET);
	}

	/**
	 * Méthode qui retourne une chaine de caractères nettoyée des cdata
	 *
	 * @param	str		chaine de caractères à nettoyer
	 * @return	string	chaine de caractères nettoyée
	 * @author	Stephane F
	 **/
	public static function cdataCheck($str) {
		$str = str_ireplace('!CDATA', '&#33;CDATA', $str);
		$str = str_replace(']]>', ']]&gt;', $str);
		return self::sanitizePhp($str);
	}

	/**
	 * Méthode qui retourne une chaine de caractères HTML en fonction du charset
	 *
	 * @param	str		chaine de caractères
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
	 * @param	base	url du site qui sera rajoutée devant les liens relatifs
	 * @param	html	chaine de caractères à convertir
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
			$result[$lang] = FLAGS[$lang] . ' ' . $lang;
		}
		return $result;
	}

	public static function getI18nUrls($key='L_(CATEGORY|USER|TAG|COMMENTS|ARTICLE)_URL') {
		$result = array();
		$pattern = '#^\s*const\s*' . $key . '\s*=\s*\'([^\']+)\'#';
		foreach(array_keys(self::getLangs()) as $lang) {
			$lines = file(PLX_ROOT . 'core/lang/' . $lang . '/core.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if(empty($lines)) {
				continue;
			}
			$article = '';
			$comment = '';
			foreach($lines as $ll) {
				if(preg_match($pattern, $ll, $matches)) {
					$key = 'L_' . $matches[1] . '_URL';
					if(!array_key_exists($key, $result)) {
						# First translation that matches
						$result[$key] = array($matches[2]);
					} else {
						$result[$key][] = $matches[2];
					}
					if($key == 'L_ARTICLE_URL') {
						$article = $matches[2];
					} elseif($key == 'L_COMMENTS_URL') {
						$comment = $matches[2];
					}
				}
			}
			# for feed
			if(!empty($article) and !empty($comment)) {
				$result['L_COMMENTS_ARTICLE_URL'][] = $comment . '/' . $article;
			}
		}

		# suppression des doublons entre langue pour une même traduction
		foreach($result as $key=>$dict) {
			$result[$key] = array_unique($result[$key]);
		}

		return $result;
	}

	/**
	 * Méthode qui empeche de mettre en cache une page
	 *
	 * @param	type	string 		type de source
	 * @param	charset	string 		type d'encodage
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

	/**
	* Méthode d'envoi de mail
	*
	* @param	name	string 			Nom de l'expéditeur
	* @param	from	string 			Email de l'expéditeur
	* @param	to		array/string	Adresse(s) du(des) destinataires(s)
	* @param	subject	string			Objet du mail
	* @param	body	string			contenu du mail
	* @return	boolean	renvoie FAUX en cas d'erreur d'envoi
	* @author	J.P. Pourrez (aka bazooka07), Amaury Graillat
	**/
	public static function sendMail($name, $from, $to, $subject, $body, $contentType="text", $cc=false, $bcc=false) {

		if(empty(trim($to)) or empty(trim($subject))) { return; }

		$headers = array(
			'MIME-Version'				=> '1.0',
			'Content-Type'				=> (($contentType === 'html') ? 'text/html' : 'text/plain') . ';charset=' . PLX_CHARSET,
			'Content-Transfer-Encoding'	=> '8bit',
			'Date'						=> date('D, j M Y G:i:s O'), # Sat, 7 Jun 2001 12:35:58 -0700
			'X-Mailer'					=> 'PluXml',
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
	* @author Pedro "P3ter" CADETE
	* @throws \PHPMailer\PHPMailer\Exception
	**/
	public static function sendMailPhpMailer($name, $from, $to, $subject, $body, $isHtml, $conf, $debug=false) {
		$mail = new PHPMailer();
		if ($debug) {
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		}
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->setFrom($from, $name);
		$mail->addAddress($to);
		$mail->Mailer = $conf['email_method'];
		$mail->CharSet = "UTF-8";
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
				$mail->SMTPDebug;
				if ($conf['smtp_security'] == 'ssl' or $conf['smtp_security'] == 'tls') {
					$mail->SMTPSecure = $conf['smtp_security'];
				}
				break;
			case 'smtpoauth':
				$mail->isSMTP();
				$mail->Host = 'smtp.gmail.com';
				$mail->Port = 587;
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
				$mail->AuthType = 'XOAUTH2';
				$provider = new Google(
					[
						'clientId' => $conf['smtpOauth2_clientId'],
						'clientSecret' => $conf['smtpOauth2_clientSecret'],
					]
				);
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
	* @param	name	string	titre du menu
	* @param	href	string	lien du menu
	* @param	title	string	contenu de la balise title
	* @param	class	string	contenu de la balise class
	* @param	onclick	string	contenu de la balise onclick
	* @param	extra	string	extra texte à afficher
	* @return	string	balise <a> formatée
	* @author	Stephane F., Jean-Pierre Pourrez @bazooka07
	**/
	public static function formatMenu($name, $href, $title=false, $class=false, $onclick=false, $extra='', $highlight=true) {
		# $classList = array('menu');
		$classList = array();
		if(!empty($class)) {
			$classList[] = $class;
		}

		$parts = parse_url($href);
		$id = basename($parts['path'], '.php');
		if(basename($parts['path']) !=  'plugin.php') {
			if(basename($parts['path']) == basename($_SERVER['SCRIPT_NAME'])) {
				$classList[] = 'active';
			}
		} else {
			$classList[] = 'menu-plugin';
			# plugin
			if(array_key_exists('query', $parts)) {
				if(!empty($_GET['p']) and $parts['query'] == 'p=' . $_GET['p']) {
					$classList[] = 'active';
				}
				$id = str_replace('p=', '', $parts['query']);
			}
		}

		$className = !empty($classList) ? ' class="' . implode(' ', $classList) . '"' : '';

		$attrs = array();
		if(!empty($title)) {
			$attrs[] = 'title="' . $title . '"';
		}
		if(!empty($onclick)) {
			$attrs[] = 'onclick="' . $onclick. '"';
		}

		ob_start();
?>
			<li id="mnu_<?= $id ?>"<?=  $className ?>><a href="<?= $href ?>" <?= implode(' ', $attrs) ?>><?= $name . $extra ?></a></li>
<?php
		return ob_get_clean();
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
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					# the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					# search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
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
			if (isset($spacepos)) {
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
	 * @param	string	chaine à nettoyer
	 * @return	string	chaine nettoyée
	*/
	public static function nullbyteRemove($string) {
		return str_replace("\0", '', $string);
	}

	/**
	 * Contrôle le nom d'un fichier ou d'un dossier
	 *
	 * @param	string  nom d'un fichier
	 * @return	boolean validité du nom du fichier ou du dossier
	*/
	public static function checkSource($src, $type='dir') {

		if (is_null($src) OR !strlen($src) OR substr($src,-1,1)=="." OR false!==strpos($src, "..")) {
			return false;
		}

		if($type=='dir')
			$regex = ",(/\.)|[[:cntrl:]]|(//)|(\\\\)|([\\:\*\?\"\<\>\|]),";
		elseif($type=='file')
			$regex = ",[[:cntrl:]]|[/\\:\*\?\"\<\>\|],";

		if (preg_match($regex, $src)) {
			return false;
		}
		return true;
	}

	/**
	 * Formate le nom d'une miniature à partir d'un nom de fichier
	 *
	 * @param	string	nom d'un fichier
	 * @return	string	nom de la miniature au format fichier.tb.ext
	*/
	public static function thumbName($filename) {

		$extensions = '(jpe?g|png|gif|bmp|webp)';
		if (
			preg_match('#^https?://#', $filename) or
			preg_match('#.*\.tb\.' . $extensions . '$#iD', $filename) or
			!preg_match('#(.*\.)' . $extensions . '$#iD', $filename, $matches)
		) {
			# url absolue ou déjà une url pour thummbnail ou extension non reconnue
			return $filename;
		} else {
			return $matches[1].'tb.'.$matches[2];
		}
	}

	/**
	 * Méthode qui minifie un buffer
	 *
	 * @param	string	chaine de caractères à minifier
	 * @return	string	chaine de caractères minifiée
	 * @author	Frédéric Kaplon
	 **/
	public static function minify($buffer) {
		/* Supprime les commentaires */
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		/* Supprime les tabs, espaces, saut de ligne, etc. */
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
		return $buffer;
	}

	/**
	 * Méthode qui converti les urls contenus dans une chaine en liens cliquables.
	 *
	 * @param	string	chaîne d'entrée
	 * @param	string	Optionnel. Si spécifié, ce paramètre doit être un tableau associatif de format $arr['attribute'] = $value.
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
				# self::debugJS($extsText, 'extsText');
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
					self::_printSelectDir($root.$child.'/', $level, $prefixParent.$next);
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
	 * @param	str|bool id : si vrai génère un id à partir du nom du champ, sinon génère l'id à partir du paramètre name
	 * @return	void
	 * @author	J.P. Pourrez alias bazooka07, T. Ingles @sudwebdesign
	 * $modeDir=true	pour ne choisir que les dossiers : voir plxMedias contentFolder()
	 * $modeDir=false	pour ne choisir que les fichiers du thème
	 * */
	public static function printSelectDir($name, $currentValue, $root, $class='', $modeDir=true, $id=true) {

		if(is_bool($id))
			$id = ($id ? ' id="id_'.$name.'"' : '');
		else
			$id = ($id!='' ? ' id="'.$id.'"' : '');

		if(substr($root, -1) != '/')
			$root .= '/';
		$value = ($modeDir) ? '.' : '';
		$selected = ($value == $currentValue)? ' selected': '';
		$caption = L_PLXMEDIAS_ROOT;
		$data_files = (!$modeDir)? ' data-files': '';
		$disabled = (!$modeDir)? ' disabled': '';
		$class = ($class? $class.' ': '') . 'scan-folders fold' . $data_files;
		echo <<< EOT
		<select $id name="$name" class="$class">
			<option$disabled value="$value"$selected>$caption/</option>
EOT;
		self::_printSelectDir($root, 0, str_repeat(' ', 3), $currentValue, $modeDir);
		echo <<< EOT
		</select>
EOT;
	}

	/**
	 * Méthode qui affiche la balise <link> partir d'un nom de fichier
	 * @param	string	file	nom d'un fichier
	 * @param	boolean	admin	false == Public & urlrwrite(), true == admin
	 * @return	void
	 * @author J.P. Pourrez alias bazooka07, T. Ingles @sudwebdesign
	 */
	public static function printLinkCss($file, $admin=false) {

		if(!empty(trim($file)) and is_file(PLX_ROOT . $file)) {
			if($admin) {
				$href = PLX_ROOT . $file;
			} else {
				$plxMotor = plxMotor::getinstance();
				$href = $plxMotor->urlRewrite($file);
			}
			$href .= '?d='.base_convert(filemtime(PLX_ROOT.$file) & 4194303, 10, 36); # 4194303 === 2 puissance 22 - 1; base_convert(4194303, 10, 16) -> 3fffff; => 48,54 jours
?>
	<link rel="stylesheet" type="text/css" href="<?= $href ?>" media="screen" />
<?php
		}
	}

	/**
	 * Méthode qui affiche des boutons pour la pagination.
	 * S'il n'y a pas assez d'éléments à afficher pour 2 pages, la pagination n'est pas affichée.
	 *
	 * @param integer $itemsCount Nombre total d'éléments à afficher dans toutes les pages
	 * @param integer $itemsPerPage Nombre d'éléments à afficher par page
	 * @param integer $currentPage numéro de la page courante affichée
	 * @param string $urlTemplate modèle pour calculer la valeur de href dans les balises <a>. Doit avoir une position numérique
	 *
	 * @return    void
	 * @author    Jean-Pierre Pourrez (@bazooka07), Thomas Inglès (@sudwebdesign)
	 **/
	public static function printPagination($itemsCount, $itemsPerPage, $currentPage, $urlTemplate) {

		if ($itemsCount <= $itemsPerPage or $itemsPerPage <= 0) {
			# just one page => no pagination and prevent division by zero ($itemsPerPage)
			return;
		}

		//Pagination preparation
		$last_page = ceil($itemsCount / $itemsPerPage);
		$showFirst = ($currentPage > 1);
		$showLast = ($currentPage < $last_page);

		// Display pagination links
		// Notice : $urlTemplate may contains % char. So don't use printf !
?>
				<span class="sml-hide"><?= ucfirst(L_PAGE) ?></span>
				<ul class="inline-list">
					<li><a href="<?= preg_replace(self::PATTERN_PAGINATION, 'page=1', $urlTemplate) ?>" title="<?= L_PAGINATION_FIRST_TITLE ?>"<?= $showFirst ? '' : ' disabled' ?> class="button"><i class="icon-angle-double-left"></i></a></li>
					<li><a href="<?= preg_replace(self::PATTERN_PAGINATION, 'page=' . ($showFirst ? $currentPage - 1 : 1), $urlTemplate) ?>" title="<?= L_PAGINATION_PREVIOUS_TITLE ?>"<?= $showFirst ? '' : ' disabled' ?> class="button"><i class="icon-angle-left"></i></a></li>
<?php
		# On boucle sur les pages
		if($last_page <= 2 * self::DELTA_PAGINATION  + 1) {
			$iMin = 1; $iMax = $last_page;
		} else {
			if($currentPage > self::DELTA_PAGINATION + 1) {
				$iMin = ($last_page - $currentPage > self::DELTA_PAGINATION) ? $currentPage - self::DELTA_PAGINATION : $last_page - 2 * self::DELTA_PAGINATION;
			} else {
				$iMin = 1;
			}
			$iMax =  $iMin + 2 * self::DELTA_PAGINATION;
		}
		for ($i = $iMin; $i <= $iMax; $i++) {
			if($i != $currentPage) {
?>
					<li><a href="<?= preg_replace(self::PATTERN_PAGINATION, 'page=' . $i, $urlTemplate); ?>" class="button"><?= $i ?></a></li>
<?php
			} else {
?>
					<li><span class="current btn--info"><?= $i ?></span></li>
<?php
			}
		}
?>
					<li><a href="<?= preg_replace(self::PATTERN_PAGINATION, 'page=' . ($showLast ? $currentPage + 1 : $last_page), $urlTemplate) ?>" title="<?= L_PAGINATION_NEXT_TITLE ?>"<?= $showLast ? '' : ' disabled' ?> class="button"><i class="icon-angle-right"></i></a></li>
					<li><a href="<?= preg_replace(self::PATTERN_PAGINATION, 'page=' . $last_page, $urlTemplate) ?>" title="<?= L_PAGINATION_LAST_TITLE ?>" class="button"<?= $showLast ? '' : ' disabled' ?>><i class="icon-angle-double-right"></i></a></li>
				</ul>
<?php
	}

	/**
	 * Remove Php opening and closing tags
	 *
	 * Deprecated !
	 * @param String $content
	 * @return array|string|string[]
	 * @author Pedro "P3ter" CADETE, Moritz Huppert, Jean-Pierre Pourrez "bazooka07"
	 */
	public static function sanitizePhpTags(String $content) {
		return preg_replace(
			['#<\?(php|=)\b#i', '#\?>#'],
			['<!-- ', ' -->'],
			$content
		);
	}

	/**
	 * Remove critical functions from PHP
	 * @param String $content
	 * @return String
	 * @author Jean-Pierre Pourrez aka bazooka07
	 **/
	public static function sanitizePhp(String $content) {
		return preg_replace('#\b(fsockopen|proc_open|system|exec|chroot|shell_exec|socket\w*)\b\([^)]*?\)\s*;#', '/* $1() not allowed here */;' . PHP_EOL, $content);
	}
}

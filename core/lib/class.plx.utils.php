<?php

/**
 * Classe plxUtils rassemblant les fonctions utiles à PluXml
 *
 * @package PLX
 * @author	Florent MONTHEL et Stephane F
 **/
class plxUtils {

	/**
	 * Méthode qui vérifie si une variable est définie.
	 * Renvoie la valeur de la variable ou la valeur par défaut passée en paramètre
	 *
	 * @param	var			string	variable à tester
	 * @param	default		string	valeur par défaut
	 * @return	valeur de la variable ou valeur par défaut passée en paramètre
	*/
	public static function getValue(&$var, $default='') {
		return (isset($var) ? (!empty($var) ? $var : $default) : $default) ;
	}

	/**
	 * Méthode qui retourne un tableau contenu les paramètres passés dans l'url de la page courante
	 *
	 * @return	array	tableau avec les paramètres passés dans l'url de la page courante
	 **/
	public static function getGets() {

		if(!empty($_SERVER['QUERY_STRING']))
			return strip_tags($_SERVER['QUERY_STRING']);
		else
			return false;
	}

	/**
	 * Méthode qui supprime les antislashs
	 *
	 * @param	content				variable ou tableau
	 * @return	array ou string		tableau ou variable avec les antislashs supprimés
	 **/
	public static function unSlash($content) {

		if(get_magic_quotes_gpc() == 1) {
			if(is_array($content)) { # On traite un tableau
				foreach($content as $k=>$v) { # On parcourt le tableau
					if(is_array($v)) {
						foreach($v as $key=>$val)
							$new_content[$k][$key] = stripslashes($val);
					} else {
						$new_content[ $k ] = stripslashes($v);
					}
				}
			} else { # On traite une chaine
				$new_content = stripslashes($content);
			}
			# On retourne le tableau modifie
			return $new_content;
		} else {
			return $content;
		}
	}

	/**
	 * Méthode qui vérifie le bon formatage d'une adresse email
	 *
	 * @param	mail		adresse email à vérifier
	 * @return	boolean		vrai si adresse email bien formatée
	 **/
	public static function checkMail($mail) {

		if (strlen($mail) > 80)
			return false;
		return preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $mail);
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
		if(preg_match('@(https?|ftp)://(-\.)?([^\s/?\.#]+\.?)+([/?][^\s]*)?$@iS', $site))
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

		return plxUtils::isValidIp($ip) ? $ip : $localIP;
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
	 * @return	stdout
	 **/
	public static function printSelect($name, $array, $selected='', $readonly=false, $class='', $id=true) {

		if(!is_array($array)) $array=array();

		if(is_bool($id))
			$id = ($id ? ' id="id_'.$name.'"' : '');
		else
			$id = ($id!='' ? ' id="'.$id.'"' : '');

		if($readonly)
			echo '<select'.$id.' name="'.$name.'" disabled="disabled" class="readonly">'."\n";
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
				if($a == $selected)
					echo "\t".'<option value="'.$a.'" selected="selected">'.$b.'</option>'."\n";
				else
					echo "\t".'<option value="'.$a.'">'.$b.'</option>'."\n";
			}
		}
		echo '</select>'."\n";
	}

	/**
	 * Méthode qui affiche un zone de saisie
	 *
	 * @param	name		nom de la zone de saisie
	 * @param	value		valeur contenue dans la zone de saisie
	 * @param	type		type du champ (text, password, hidden)
	 * @param	size		longueur du champ - nombre maximal de caractères pouvant être saisis (par défaut 50-255)
	 * @param	readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @param	placeholder valeur du placeholder du champ (html5)
	 * @param   extra		extra paramètre pour du javascript par exemple (onclick)
	 * @return	stdout
	 **/
	public static function printInput($name, $value='', $type='text', $sizes='50-255', $readonly=false, $className='', $placeholder='', $extra='') {

		 $params = array(
			'id="id_'.$name.'"',
			'name="'.$name.'"',
			'type="'.$type.'"'
		 );
		 if(!empty($value))
			 $params[] = 'value="'.$value.'"';
		 if(!empty($extra))
			 $params[] = $extra;
		 if($type != 'hidden') {
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
		 echo '<input '.implode(' ', $params).' />';
	}

	/**
	 * Méthode qui affiche une zone de texte
	 *
	 * @param	name		nom de la zone de texte
	 * @param	value		valeur contenue dans la zone de texte
	 * @param	cols		nombre de caractères affichés par colonne
	 * @params	rows		nombre de caractères affichés par ligne
	 * @param	readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @return	stdout
	 **/
	public static function printArea($name, $value='', $cols='', $rows='', $readonly=false, $class='') {

		if($readonly)
			echo '<textarea id="id_'.$name.'" name="'.$name.'" class="readonly" cols="'.$cols.'" rows="'.$rows.'" readonly="readonly">'.$value.'</textarea>'."\n";
		else
			echo '<textarea id="id_'.$name.'" name="'.$name.'"'.($class!=''?' class="'.$class.'"':'').' cols="'.$cols.'" rows="'.$rows.'">'.$value.'</textarea>'."\n";
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
	 * @param	io			affiche à l'écran le résultat du test si à VRAI
	 * @param	format		format d'affichage
	 * @return	boolean		retourne vrai si le module apache mod_rewrite est disponible
	 * @author	Stephane F
	 **/
	public static function testModRewrite($io=true, $format="<li><span style=\"color:#color\">#symbol #message</span></li>\n") {

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
	 * @param	io			affiche à l'écran le résultat du test si à VRAI
	 * @param	format		format d'affichage
	 * @return	boolean		retourne vrai si la fonction php mail est disponible
	 * @author	Stephane F
	 **/
	public static function testMail($io=true, $format="<li><span style=\"color:#color\">#symbol #message</span></li>\n") {

		if($return=function_exists('mail')) {
			if($io==true) {
				$output = str_replace('#color', 'green', $format);
				$output = str_replace('#symbol', '&#10004;', $output);
				$output = str_replace('#message', L_MAIL_AVAILABLE, $output);
				echo $output;
			}
		} else {
			if($io==true) {
				$output = str_replace('#color', 'red', $format);
				$output = str_replace('#symbol', '&#10007;', $output);
				$output = str_replace('#message', L_MAIL_NOT_AVAILABLE, $output);
				echo $output;
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
	public static function removeAccents($str,$charset='utf-8') {

		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$str = str_replace($a, $b, $str);
		$str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml|uro)\;#', '\1', $str);
		$str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); # pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#\&[^;]+\;#', '', $str); # supprime les autres caractères
		return $str;
	}

	/**
	 * Méthode qui convertit une chaine de caractères au format valide pour une url
	 *
	 * @param	str			chaine de caractères à formater
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
	 * @param	str			chaine de caractères à formater
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
	public static function write($xml, $filename) {

		if(file_exists($filename)) {
			$f = fopen($filename.'.tmp', 'w'); # On ouvre le fichier temporaire
			fwrite($f, trim($xml)); # On écrit
			fclose($f); # On ferme
			unlink($filename);
			rename($filename.'.tmp', $filename); # On renomme le fichier temporaire avec le nom de l'ancien
		} else {
			$f = fopen($filename, 'w'); # On ouvre le fichier
			fwrite($f, trim($xml)); # On écrit
			fclose($f); # On ferme
		}
		# On place les bons droits
		chmod($filename,0644);
		# On vérifie le résultat
		if(file_exists($filename) AND !file_exists($filename.'.tmp'))
			return true;
		else
			return false;
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
	 **/
	public static function makeThumb($src_image, $dest_image, $thumb_width = 48, $thumb_height = 48, $jpg_quality = 90) {

		if(!function_exists('imagecreatetruecolor')) return false;

		// Get dimensions of existing image
		$image = getimagesize($src_image);

		// Check for valid dimensions
		if($image[0] <= 0 || $image[1] <= 0) return false;

		// Determine format from MIME-Type
		$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

		// calcul du ration si nécessaire
		if($thumb_width!=$thumb_height) {
			# Calcul du ratio
			$x_offset = $y_offset = 0;
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
		}

		$canvas = imagecreatetruecolor($thumb_width, $thumb_height);

		// Import image
		switch( $image['format'] ) {
			case 'jpg':
			case 'jpeg':
				$image_data = imagecreatefromjpeg($src_image);
				break;
			case 'png':
				$image_data = imagecreatefrompng($src_image);
				$color = imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
				imagefill($canvas, 0, 0, $color);
				imagesavealpha($canvas, true);
				break;
			case 'gif':
				$image_data = imagecreatefromgif($src_image);
				$color = imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
				imagefill($canvas, 0, 0, $color);
				imagesavealpha($canvas, true);
				break;
			default:
				return false; // Unsupported format
			break;
		}

		// Verify import
		if($image_data == false) return false;

		// Calculate measurements (square crop)
		if($thumb_width==$thumb_height) {
			if($image[0] > $image[1]) {
				// For landscape images
				$x_offset = ($image[0] - $image[1]) / 2;
				$y_offset = 0;
				$square_size_w = $square_size_h = $image[0] - ($x_offset * 2);
			} else {
				// For portrait and square images
				$x_offset = 0;
				$y_offset = ($image[1] - $image[0]) / 2;
				$square_size_w = $square_size_h = $image[1] - ($y_offset * 2);
			}
		}

		// Resize and crop
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

			// Create thumbnail
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
				default:
					return false; // Unsupported format
				break;
			}

		} else {
			return false;
		}

	}

	/**
	 * Méthode qui affiche un message
	 *
	 * @param	string message à afficher
	 * @param	string classe css à utiliser pour formater l'affichage du message
	 * @param       string format des balises avant le message
	 * @param	string format des balises après le message
	 * @return      stdout
	 **/
	public static function showMsg($msg, $class='',$format_start='<p class="#CLASS">',$format_end='</p>') {
		$format_start = str_replace('#CLASS',($class != '' ? $class : 'msg'),$format_start);
		echo $format_start.$msg.$format_end;
	}

	/**
	 * Méthode qui retourne l'url de base du site
	 *
	 * @return	string		url de base du site
	 **/
	public static function getRacine() {

		$protocol = (!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) AND strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' )?        'https://' : "http://";
		$servername = $_SERVER['HTTP_HOST'];
		$serverport = (preg_match('/:[0-9]+/', $servername) OR $_SERVER['SERVER_PORT'])=='80' ? '' : ':'.$_SERVER['SERVER_PORT'];
		$dirname = preg_replace('/\/(core|plugins)\/(.*)/', '', dirname($_SERVER['SCRIPT_NAME']));
		$racine = rtrim($protocol.$servername.$serverport.$dirname, '/\\').'/';
		if(!plxUtils::checkSite($racine, false))
			die('Error: wrong or invalid url');
		return $racine;
	}

	/**
	 * Méthode qui retourne une chaine de caractères au hasard
	 *
	 * @param	taille		nombre de caractère de la chaine à retourner (par défaut sur 10 caractères)
	 * @return	string		chaine de caractères au hasard
	 **/
	public static function charAleatoire($taille='10') {

		$string = '';
		$chaine = 'abcdefghijklmnpqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		mt_srand((float)microtime()*1000000);
		for($i=0; $i<$taille; $i++)
			$string .= $chaine[ mt_rand()%strlen($chaine) ];
		return $string;
	}

	/**
	 * Méthode qui coupe une chaine de caractères sur n caractères ou sur n mots
	 *
	 * @param	str			chaine de caractères à couper
	 * @param	length		nombre de caractères ou nombre de mots à garder (par défaut 25)
	 * @param   type		à renseigner avec la valeur 'word' pour couper en nombre de mots. Par défaut la césure se fait en nombre de caractères
	 * @param	add_text	texte à ajouter après la chaine coupée (par défaut '...' est ajouté)
	 * @return	string		chaine de caractères coupée
	 **/
	public static function strCut($str='', $length=25, $type='', $add_text='...') {
		if($type == 'word') { # On coupe la chaine en comptant le nombre de mots
			$content = explode(' ',$str);
			$length = sizeof($content) < $length ? sizeof($content) : $length;
			return implode(' ',array_slice($content,0,$length)).$add_text;
		} else { # On coupe la chaine en comptant le nombre de caractères
			return strlen($str) > $length ? utf8_decode(substr(utf8_encode($str), 0, $length)).$add_text : $str;
		}
	}

	/**
	 * Méthode qui retourne une chaine de caractères formatée en fonction du charset
	 *
	 * @param	str			chaine de caractères
	 * @return	string		chaine de caractères tenant compte du charset
	 **/
	public static function strCheck($str) {

		return htmlspecialchars($str,ENT_QUOTES,PLX_CHARSET);
	}

	/**
	 * Méthode qui retourne une chaine de caractères nettoyée des cdata
	 *
	 * @param	str			chaine de caractères à nettoyer
	 * @return	string		chaine de caractères nettoyée
	 * @author	Stephane F
	 **/
	public static function cdataCheck($str) {
		$str = str_ireplace('!CDATA', '&#33;CDATA', $str);
		return str_replace(']]>', ']]&gt;', $str);
	}

	/**
	 * Méthode qui retourne une chaine de caractères HTML en fonction du charset
	 *
	 * @param	str			chaine de caractères
	 * @return	string		chaine de caractères tenant compte du charset
	 **/
	public static function strRevCheck($str) {

		return html_entity_decode($str,ENT_QUOTES,PLX_CHARSET);
	}

	/**
	 * Méthode qui retourne le type de compression disponible
	 *
	 * @return	stout
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
	 * @param	base		url du site qui sera rajoutée devant les liens relatifs
	 * @param	html		chaine de caractères à convertir
	 * @return	string		chaine de caractères modifiée
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
	 * @return	string		chaine de caractères modifiée
	 * @author	Stephane F.
	 **/
	public static function getLangs() {
		$array = array();
		$glob = plxGlob::getInstance(PLX_CORE.'lang', true);
		if($aFolders = $glob->query("/[a-z]+/i")) {
			foreach($aFolders as $folder) {
				$array[$folder] = $folder;
			}
		}
		ksort($array);
		return $array;
	}

	/**
	 * Méthode qui empeche de mettre en cache une page
	 *
	 * @return	stdio
	 * @author	Stephane F.
	 **/
	public static function cleanHeaders() {
		@header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
		@header('Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT');
		@header('Cache-Control: no-cache, must-revalidate, max-age=0');
		@header('Cache: no-cache');
		@header('Pragma: no-cache');
		@header('Content-Type: text/html; charset='.PLX_CHARSET);
	}

	/**
	* Méthode d'envoi de mail
	*
	* @param	name	string 			Nom de l'expéditeur
	* @param	from	string 			Email de l'expéditeur
	* @param	to		array/string	Adresse(s) du(des) destinataires(s)
	* @param	subject	string			Objet du mail
	* @param	body	string			contenu du mail
	* @return			boolean			renvoie FAUX en cas d'erreur d'envoi
	* @author	Amaury Graillat
	**/
	public static function sendMail($name, $from, $to, $subject, $body, $contentType="text", $cc=false, $bcc=false) {

		if(is_array($to))
			$to = implode(', ', $to);
		if(is_array($cc))
			$cc = implode(', ', $cc);
		if(is_array($bcc))
			$bcc = implode(', ', $bcc);

		$headers  = "From: ".$name." <".$from.">\r\n";
		$headers .= "Reply-To: ".$from."\r\n";
		$headers .= 'MIME-Version: 1.0'."\r\n";
		// Content-Type
		if($contentType == 'html')
			$headers .= 'Content-type: text/html; charset="'.PLX_CHARSET.'"'."\r\n";
		else
			$headers .= 'Content-type: text/plain; charset="'.PLX_CHARSET.'"'."\r\n";

		$headers .= 'Content-transfer-encoding: 8bit'."\r\n";
		$headers .= 'Date: '.date("D, j M Y G:i:s O")."\r\n"; // Sat, 7 Jun 2001 12:35:58 -0700

		if($cc != "")
			$headers .= 'Cc: '.$cc."\r\n";
		if($bcc != "")
			$headers .= 'Bcc: '.$bcc."\r\n";

		return mail($to, $subject, $body, $headers);
	}

	/**
	* Méthode qui formate un lien pour la barre des menus
	*
	* @param	name	string 			titre du menu
	* @param	href	string 			lien du menu
	* @param	title	string			contenu de la balise title
	* @param	class	string			contenu de la balise class
	* @param	onclick	string			contenu de la balise onclick
	* @param	extra	string			extra texte à afficher
	* @return			string			balise <a> formatée
	* @author	Stephane F.
	**/
	public static function formatMenu($name, $href, $title=false, $class=false, $onclick=false, $extra='', $highlight=true) {
		$menu = '';
		$basename = explode('?', basename($href));
		$active = ($highlight AND ($basename[0] == basename($_SERVER['SCRIPT_NAME']))) ? ' active':'';
		if($basename[0]=='plugin.php' AND isset($_GET['p']) AND $basename[1]!='p='.$_GET['p']) $active='';
		$title = $title ? ' title="'.$title.'"':'';
		$class = $class ? ' '.$class:'';
		$onclick = $onclick ? ' onclick="'.$onclick.'"':'';
		$menu = '<li id="mnu_'.plxUtils::title2url($name).'" class="menu'.$active.$class.'"><a href="'.$href.'"'.$onclick.$title.'>'.$name.$extra.'</a></li>';
		return $menu;
	}

	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ending if the text is longer than length.
	 *
	 * @param string  $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param string  $ending Ending to be appended to the trimmed string.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 * @return string Trimmed string.
	*/
	public static function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}

			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';

			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
					// if tag is a closing tag (f.e. </b>)
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
					// if tag is an opening tag (f.e. <b>)
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}

				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}

				// if the maximum length is reached, get off the loop
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

		// if the words shouldn't be cut in the middle...
		if (!$exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}

		// add the defined ending to the text
		$truncate .= $ending;
		/*
		if($considerHtml) {
			// close all unclosed html-tags
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
	 * @param	string chaine à nettoyer
	 * @return	string chaine nettoyée
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
	 * @param	string  nom d'un fichier
	 * @return	string	nom de la miniature au format fichier.tb.ext
	*/
	public static function thumbName($filename) {
		if(preg_match('/^(.*\.)(jpe?g|png|gif)$/iD', $filename, $matches)) {
			return $matches[1].'tb.'.$matches[2];
		} else {
			return $filename;
		}
	}

	/**
	 * Méthode qui minifie un buffer
	 *
	 * @param	string		chaine de caractères à minifier
	 * @return	string		chaine de caractères minifiée
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
	 * @param	string		chaîne d'entrée
	 * @param	string		Optionnel. Si spécifié, ce paramètre doit être un tableau associatif de format $arr['attribute'] = $value.
	 * @return	string		Retourne une copie de la chaîne str dont les urls ont été encapsulées dans des balises <a>.
	 * @author	http://code.seebz.net/p/autolink-php/
	 *	Exemple 1:
	 *		$str = 'A link : http://example.com/?param=value#anchor.';
	 *		$str = autolink($str);
	 *		echo $str; // A link : <a href="http://example.com/?param=value#anchor">http://example.com/?param=value#anchor</a>.
	 *  Exemple 2:
	 *		$str = 'http://example.com/';
	 *		$str = autolink($str, array("target"=>"_blank","rel"=>"nofollow"));
	 *		echo $str; // <a href="http://example.com/" target="_blank" rel="nofollow">http://example.com/</a>
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

/*
	function arrayRemoveDuplicate($array, $field) {
		foreach ($array as $element)
			$cmp[] = $element[$field];
		$unique = array_unique($cmp);
		foreach ($unique as $k => $v)
			$new[] = $array[$k];
		return $new;
	}
*/

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
	 * @author		J.P. Pourrez alias bazooka07
	 * @version		2017-06-09
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

}
?>

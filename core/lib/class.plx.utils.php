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
	 * @param	default		string	valeur par defaut
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

		if(!empty($_GET)) {
			$a = array_keys($_GET);
			return strip_tags($a[0]);
		}
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
		# On vérifie le site via une expression régulière
		# Méthode Jeffrey Friedl - http://mathiasbynens.be/demo/url-regex
		# modifiée par Amaury Graillat pour prendre en compte la valeur localhost dans l'url
		if(preg_match('@\b((ftp|https?)://([-\w]+(\.\w[-\w]*)+|localhost)|(?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)+(?: com\b|edu\b|biz\b|gov\b|in(?:t|fo)\b|mil\b|net\b|org\b|[a-z][a-z]\b))(\:\d+)?(/[^.!,?;"\'<>()\[\]{}\s\x7F-\xFF]*(?:[.!,?]+[^.!,?;"\'<>()\[\]{}\s\x7F-\xFF]+)*)?@iS', $site))
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

        return plxUtils::isValidIp($ip) ? $ip : '';
	}

	/**
	 * Méthode qui affiche une liste de sélection
	 *
	 * @param	name		nom de la liste
	 * @param	array		valeurs de la liste sous forme de tableau (nom, valeur)
	 * @param	selected	valeur par défaut
	 * @param	readonly	vrai si la liste est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @param	id			si à vrai génère un id
	 * @return	stdout
	 **/
	public static function printSelect($name, $array, $selected='', $readonly=false, $class='', $id=true) {

		if(!is_array($array)) $array=array();

		$id = ($id?' id="id_'.$name.'"':'');
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
	 * àparam	placeholder valeur du placeholder du champ (html5)
	 * @return	stdout
	 **/
	public static function printInput($name, $value='', $type='text', $size='50-255', $readonly=false, $class='', $placeholder='') {

		$size = explode('-',$size);
		$placeholder = $placeholder!='' ? ' placeholder="'.$placeholder.'"' : '';
		if($readonly)
			echo '<input id="id_'.$name.'" name="'.$name.'" type="'.$type.'" class="readonly" value="'.$value.'" size="'.$size[0].'" maxlength="'.$size[1].'" readonly="readonly"'.$placeholder.' />'."\n";
		else
			echo '<input id="id_'.$name.'" name="'.$name.'" type="'.$type.'"'.($class!=''?' class="'.$class.'"':'').' value="'.$value.'" size="'.$size[0].'" maxlength="'.$size[1].'"'.$placeholder.' />'."\n";

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
	 * @param	io			affiche à l'écran le resultat du test si à VRAI
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
	 * @param	io			affiche à l'écran le resultat du test si à VRAI
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
	 * Méthode qui formate une chaine de caractères en supprimant des caractères non valides
	 *
	 * @param	str			chaine de caracères à formater
	 * @param	charset		charset à utiliser dans le formatage de la chaine (par défaut utf-8)
	 * @return	string		chaine formatée
	 **/
	public static function removeAccents($str,$charset='utf-8') {

		$str = htmlentities($str, ENT_NOQUOTES, $charset);
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
	 * @param	num					chiffre à convertire
	 * @param	length				longeur de la chaine à retourner
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
	 * @param	filename			emplacement et nom du fichier source
	 * @param	filename_out		emplacement et nom de la miniature créée
	 * @param	width				largeur de la miniature
	 * @param	height				hauteur de la miniature
	 * @param	quality				qualité de l'image
	 * @param	ratio				si vrai conserve le ratio largeur x hauteur
	 * @return	boolean				vrai si image créée
	 **/
	public static function makeThumb($filename, $filename_out, $width, $height, $quality) {

		if(!function_exists('imagecreatetruecolor')) return false;

		# Informations sur l'image
		list($width_orig,$height_orig,$type) = getimagesize($filename);

		# Calcul du ratio
		$ratio_w = $width / $width_orig;
		$ratio_h = $height / $height_orig;
		if($width == 0)
            $width = $width_orig * $ratio_h;
		elseif($height == 0)
            $height = $height_orig * $ratio_w;
		elseif($ratio_w < $ratio_h AND $ratio_w < 1) {
			$width = $ratio_w * $width_orig;
			$height = $ratio_w * $height_orig;
		} elseif($ratio_h < 1) {
			$width = $ratio_h * $width_orig;
			$height = $ratio_h * $height_orig;
		} else {
			$width = $width_orig;
			$height = $height_orig;
		}

		# Création de l'image
		$image_p = imagecreatetruecolor($width,$height);

		if($type == 1) {
			$image = imagecreatefromgif($filename);
			$color = imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
			imagefill($image_p, 0, 0, $color);
			imagesavealpha($image_p, true);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagegif($image_p, $filename_out);
		}
		elseif($type == 2) {
			$image = imagecreatefromjpeg($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagejpeg($image_p, $filename_out, $quality);
		}
		elseif($type == 3) {
			$image = imagecreatefrompng($filename);
			$color = imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
			imagefill($image_p, 0, 0, $color);
			imagesavealpha($image_p, true);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagepng($image_p, $filename_out);
		}

		return is_file($filename_out);
	}

	/**
	 * Méthode qui affiche un message
	 *
	 * @param	msg			message à afficher
	 * @param	class		class css à utiliser pour formater l'affichage du message
	 * @return	stdout
	 **/
	public static function showMsg($msg, $class='') {

		if($class=='') echo '<p class="msg">'.$msg.'</p>';
		else echo '<p class="'.$class.'">'.$msg.'</p>';
	}

	/**
	 * Méthode qui retourne l'url de base du site
	 *
	 * @return	string		url de base du site
	 **/
	public static function getRacine() {

		$protocol = (!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on')?	'https://' : "http://";
		$servername = $_SERVER['HTTP_HOST'];
		$serverport = (preg_match('/:[0-9]+/', $servername) OR $_SERVER['SERVER_PORT'])=='80' ? '' : ':'.$_SERVER['SERVER_PORT'];
		$dirname = preg_replace('/\/(core|plugins)\/(.*)/', '', dirname($_SERVER['SCRIPT_NAME']));
		$racine = rtrim($protocol.$servername.$serverport.$dirname, '/').'/';
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
			return strlen($str) > $length ? utf8_encode(substr(utf8_decode($str), 0, $length)).$add_text : $str;
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
		}elseif(strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false){
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
	 * @author	Stephane F., Amaury Graillat
	 **/
	public static function rel2abs($base, $html) {

		// url des plugins
		$html = preg_replace('@\<([^>]*) (href|src)=(["\'])[\.]/plugins@i', '<$1 $2=$3'.$base.'plugins', $html);
		// generate server-only replacement for root-relative URLs
		$server = preg_replace('@^([^:]+)://([^/]+)(/|$).*@', '\1://\2/', $base);
		// on repare les liens ne commençant que part #
		$get = plxUtils::getGets();
		$html = preg_replace('@\<([^>]*) (href|src)="#@i', '<\1 \2="' . $get . '#', $html);
		// replace root-relative URLs
		$html = preg_replace('@\<([^>]*) (href|src)=".?/@i', '<\1 \2="' . $server, $html);
		// replace base-relative URLs
		$html = preg_replace('@\<([^>]*) (href|src)="([^:"]*|[^:"]*:[^/"][^"]*)"@i', '<\1 \2="' . $base . '\3"', $html);
		// unreplace fully qualified URLs with proto: that were wrongly added $base
		$html = preg_replace('@\<([^>]*) (href|src)="'. $base . '(mailto|javascript):@i', '<\1 \2="\3:', $html);
		return $html;

	}

	/**
	 * Méthode qui retourn la liste des langues disponibles dans un tableau
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
		$headers .= 'MIME-Version: 1.0'."\r\n";
		// Content-Type
		if($contentType == 'html')
			$headers .= 'Content-type: text/html; charset="'.PLX_CHARSET.'"'."\r\n";
		else
			$headers .= 'Content-type: text/plain; charset="'.PLX_CHARSET.'"'."\r\n";

		$headers .= 'Content-transfer-encoding: 8bit'."\r\n";

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
		$menu = '<li class="menu'.$active.$class.'"><a href="'.$href.'"'.$onclick.$title.'>'.$name.'</a>'.$extra.'</li>';
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
	 * @param	string  $text chaine à nettoyer
	 * @return	string chaine nettoyée
	*/
	public static function nullbyteRemove($string) {
		return str_replace("\0", '', $string);
	}

	/**
	 * Controle le nom d'un fichier ou d'un dossier
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
	 * @return	string	nmo de la miniature au format fichier.tb.ext
	*/
	public static function thumbName($filename) {
		if(preg_match('/^(.*\.)([^.]+)$/D', $filename, $matches)) {
			return $matches[1].'tb.'.$matches[2];
		} else {
			return $filename;
		}
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
}
?>
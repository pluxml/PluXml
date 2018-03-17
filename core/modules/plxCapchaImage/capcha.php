<?php

session_start();

# Chemin absolu vers le dossier
if (!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__).'/');

# tableau contenant les fontes disponibles
$fonts=array();
if ($dh = opendir(ABSPATH.'fonts')) {
	while (($file = readdir($dh)) !== false) {
		if(strtolower(strrchr($file,'.'))=='.ttf')
			$fonts[] = ABSPATH.'fonts/'.$file;
	}
	closedir($dh);
}

# tableau contenant les fonds d'images pour le capcha
$images=array();
if ($dh = opendir(ABSPATH.'images')) {
	while (($file = readdir($dh)) !== false) {
		if(strtolower(strrchr($file,'.'))=='.png')
			$images[] = ABSPATH.'images/'.$file;
	}
	closedir($dh);
}

# Création de l'image de fond du capcha
$image = imagecreatefrompng($images[array_rand($images)]);

# tableau des couleurs pour les lettres. imagecolorallocate() retourne un identifiant de couleur.
$colors=array(
	imagecolorallocate($image, 131,154,255),
	imagecolorallocate($image, 89,186,255),
	imagecolorallocate($image, 155,190,214),
	imagecolorallocate($image, 255,128,234),
	imagecolorallocate($image, 255,123,123)
);

# Retourne de façon aléatoire une donnée d'un tableau
function random($tab) {
	return $tab[array_rand($tab)];
}

function _getCode($length) {
	$chars = '23456789abcdefghjklmnpqrstuvwxyz'; // Certains caractères ont été enlevés car ils prêtent à confusion
	$rand_str = '';
	for ($i=0; $i<$length; $i++) {
		$rand_str .= $chars{ mt_rand( 0, strlen($chars)-1 ) };
	}
	return strtolower($rand_str);
}

# récupération du code du capcha en variable de session
$theCode = $_SESSION['capcha'] = _getCode(5);

# imagettftext(image, taille police, angle inclinaison, coordonnée X, coordonnée Y, couleur, police, texte) écrit le texte sur l'image.
imagettftext($image, 28, rand(-10, 10),  0,  37, random($colors), random($fonts), substr($theCode,0,1));
imagettftext($image, 28, rand(-10, 10), 37,  37, random($colors), random($fonts), substr($theCode,1,1));
imagettftext($image, 28, rand(-10, 10), 60,  37, random($colors), random($fonts), substr($theCode,2,1));
imagettftext($image, 28, rand(-10, 10), 100, 37, random($colors), random($fonts), substr($theCode,3,1));
imagettftext($image, 28, rand(-10, 10), 120, 37, random($colors), random($fonts), substr($theCode,4,1));

# Envoi de l'image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
exit;
?>
<?php

const PLX_ROOT = './';
include PLX_ROOT . 'core/lib/config.php'; # Autochargement des classes

# On démarre la session
const SESSION_LIFETIME = 7200;

# use session_set_cookie_params() before session_start() - See https://www.php.net
$path1 = preg_replace('@/(core|plugins)/(.*)$@', '/', dirname($_SERVER['SCRIPT_NAME']));
if(version_compare(phpversion(), '7.3.1', '>=')) {
	session_set_cookie_params(array(
		'lifetime'	=> SESSION_LIFETIME,
		'path'		=> $path1,
		'domain'	=> $_SERVER['SERVER_NAME'],
		'secure'	=> isset($_SERVER["HTTPS"]),
		'httponly'	=> true,
		'samesite'	=> 'Strict',
	));
} else {
	# No support for samesite option
	session_set_cookie_params(SESSION_LIFETIME, $path1, $_SERVER['SERVER_NAME'], isset($_SERVER["HTTPS"]), true);
}
session_start();

# Creation de l'objet principal et lancement du traitement
$plxMotor = plxMotor::getInstance();

# Détermination de la langue à utiliser (modifiable par le hook : Index)
$lang = $plxMotor->aConf['default_lang'];

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('Index'));

# chargement du fichier de langue
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# On vérifie que PHP installé est une version récente
if(version_compare(PHP_VERSION, PHP_VERSION_MIN, '<')){
	header('Content-Type: text/plain charset=UTF-8');
	printf(L_WRONG_PHP_VERSION, PHP_VERSION_MIN);
	exit;
}

$plxMotor->prechauffage();
$plxMotor->demarrage();

# Creation de l'objet d'affichage
$plxShow = plxShow::getInstance();

eval($plxMotor->plxPlugins->callHook('IndexBegin')); # Hook Plugins

# Traitements du thème
if(empty($plxMotor->style) or !is_dir(PLX_ROOT . $plxMotor->aConf['racine_themes'] . $plxMotor->style)) {
	if(!is_dir(PLX_ROOT . $plxMotor->aConf['racine_themes'] . 'defaut')) {
		header('Content-Type: text/plain; charset='.PLX_CHARSET);
		echo L_ERR_THEME_NOTFOUND.' ('.PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.') !';
		exit;
	}

	# fallback si thème perso pas trouvé
	$plxMotor->style = 'defaut';
}

# On teste si le template existe
if(!file_exists(PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.'/'.$plxMotor->template)) {
	header('Content-Type: text/plain; charset='.PLX_CHARSET);
	echo L_ERR_FILE_NOTFOUND.' ('.PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.'/'.$plxMotor->template.') !';
	exit;
}

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

# On impose le charset
header('Content-Type: text/html; charset='.PLX_CHARSET);

# Insertion du template
include(PLX_ROOT.$plxMotor->aConf['racine_themes'].$plxMotor->style.'/'.$plxMotor->template);

# Récupération de la bufférisation
$output = ob_get_clean();

# Hooks spécifiques au thème
ob_start();
eval($plxMotor->plxPlugins->callHook('ThemeEndHead')); # Hook Plugins
$output = str_replace('</head>', ob_get_clean().'</head>', $output);

ob_start();
eval($plxMotor->plxPlugins->callHook('ThemeEndBody')); # Hook Plugins
$output = str_replace('</body>', ob_get_clean().'</body>', $output);

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('IndexEnd'));

# On applique la réécriture d'url si nécessaire
if($plxMotor->aConf['urlrewriting']) {
	$output = plxUtils::rel2abs($plxMotor->aConf['racine'], $output);
}

# On applique la compression gzip si nécessaire et disponible
if($plxMotor->aConf['gzip']) {
	if($encoding=plxUtils::httpEncoding()) {
		header('Content-Encoding: '.$encoding);
		$output = gzencode($output,-1,FORCE_GZIP);
	}
}

# Restitution écran
echo $output;

exit;
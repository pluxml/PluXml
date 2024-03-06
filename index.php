<?php
const PLX_ROOT = './';
const PLX_CORE = PLX_ROOT.'core/';

include PLX_CORE . 'lib/config.php';

# On verifie que PluXml est installé
if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	header('Location: ' . PLX_ROOT . 'install.php');
	exit;
}

# On démarre la session
plx_session_start();

# Creation de l'objet principal et lancement du traitement
$plxMotor = plxMotor::getInstance();

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('Index'));

# chargement du fichier de langue
loadLang(PLX_CORE.'lang/' . PLX_SITE_LANG . '/core.php');

$plxMotor->prechauffage();
$plxMotor->demarrage();

# Creation de l'objet d'affichage
$plxShow = plxShow::getInstance();

eval($plxMotor->plxPlugins->callHook('IndexBegin')); # Hook Plugins

# Traitements du thème
$style = PLX_ROOT . $plxShow->plxMotor->aConf['racine_themes'] . $plxShow->plxMotor->style;
if (empty(trim($plxShow->plxMotor->style)) or !is_dir($style)) {
	header('Content-Type: text/plain; charset=' . PLX_CHARSET);
	echo L_ERR_THEME_NOTFOUND.' (' . $style . ') !';
	exit;
}

$template = $style . '/' . $plxShow->plxMotor->template;
if (!file_exists($template)) {
	header('Content-Type: text/plain; charset=' . PLX_CHARSET);
	echo L_ERR_FILE_NOTFOUND.' (' . $template . ') !';
	exit;
}

# On impose le charset
header('Content-Type: text/html; charset=' . PLX_CHARSET);

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

# Insertion du template
include $template;
# Récuperation de la bufférisation
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

<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author    Florent MONTHEL, Stephane F, Philippe-M, Pedro "P3ter" CADETE"
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if (!empty($_POST)) {
    $plxAdmin->editConfiguration($_POST);
    header('Location: parametres_base.php');
    exit;
}

# On inclut le header
include 'top.php';
?>

<form method="post" id="form_base_settings" class="first-level">
    <?= plxToken::getTokenPostMethod() ?>
    <div class="adminheader">
        <div>
            <h2 class="h3-like"><?= L_CONFIG_BASE ?></h2>
        </div>
        <div>
			<div>
				<input class="btn--primary" type="submit" name="config-base" role="button" value="<?= L_SAVE ?>" />
			</div>
        </div>
    </div>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop'));
?>
    <fieldset>
<?php
foreach(array(
	'title'				=> L_CONFIG_BASE_SITE_TITLE,
	'description'		=> L_CONFIG_BASE_SITE_SLOGAN,
	'meta_description'	=> L_CONFIG_META_DESCRIPTION,
	'meta_keywords'		=> L_CONFIG_META_KEYWORDS,
	'default_lang'		=> array(L_CONFIG_BASE_DEFAULT_LANG, plxUtils::getLangs()),
	'timezone'			=> array(L_TIMEZONE, plxTimezones::timezones()),
	'allow_com'			=> array(L_ALLOW_COMMENTS),
	'mod_com'			=> array(L_CONFIG_BASE_MODERATE_COMMENTS),
	'mod_art'			=> array(L_CONFIG_BASE_MODERATE_ARTICLES),
	'enable_rss'		=> array(L_CONFIG_BASE_ENABLE_RSS),
) as $k=>$infos) {
?>
		<div <?= is_string($infos) ? '' : 'class="label-expanded"' ?>>
<?php
	if(is_string($infos)) {
?>
			<label for="id_<?= $k ?>"><?= $infos ?></label>
			<input type="text" name="<?= $k ?>" value="<?= plxUtils::strCheck($plxAdmin->aConf[$k]) ?>" id="id_<?= $k ?>" />
<?php
	} else {
?>
			<label for="id_<?= $k ?>"><?= $infos[0] ?></label>
<?php
		if(isset($infos[1])) {
			plxUtils::printSelect($k, $infos[1], $plxAdmin->aConf[$k]);
		} else {
?>
			<input type="checkbox" name="<?= $k ?>" value="1" id="id_<?= $k ?>" class="switch" <?= !empty($plxAdmin->aConf[$k]) ? 'checked' : '' ?> />
<?php
		}
	}
?>
		</div>
<?php
}
?>

    </fieldset>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase'))
?>
</form>

<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));

# On inclut le footer
include 'foot.php';

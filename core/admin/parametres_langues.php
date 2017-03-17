<?php

/**
 * Edition des paramètres de langue
 *
 * @package PLX
 * @author  Cyril MAGUIRE
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

$Lang = plxUtils::strCheck(plxUtils::getValue($_POST['langToTranslate'], (isset($_GET['lang']) && in_array($_GET['lang'], plxUtils::getLangs()) ? $_GET['lang'] : $lang) ));
$fileToTranslate = plxUtils::getValue($_GET['file'], 'admin');

if (isset($_GET['lang']) && in_array($_GET['lang'], plxUtils::getLangs())) {
    $_POST['readFile'] = true;
}

$LANG = $plxAdmin->getItemsToTranslate($fileToTranslate, $Lang);

# On édite le fichier de langue
if(isset($_POST['recordFile'])) {
    $plxAdmin->editLang($fileToTranslate,$Lang,$LANG,$_POST);
    $_POST = array();
    header('Location: parametres_langues.php');
    exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<form action="parametres_langues.php?file=<?php echo $fileToTranslate ?><?php echo isset($_POST['readFile']) ? '&lang='.$Lang : '';?>" method="post" id="form_settings">

    <div class="inline-form action-bar">
        <h2><?php echo L_MENU_CONFIG_LANG ?></h2>
        <input type="submit" value="<?php echo (isset($_POST['readFile']) ? L_CONFIG_LANG_RECORD.' '.$Lang : L_CONFIG_LANG_SEE_FILE_TO_TRANSLATE );?>" />
        <?php if (isset($_POST['readFile'])) :?>

        <ul class="menu">
            <li><?php echo L_CONFIG_FILES_TO_TRANSLATE ?> :</li>
            <?php foreach ($plxAdmin->aTrad as $k => $file) {
                if ($file == $fileToTranslate) {
                    echo '<li>'.$file.'</li>';
                } else {
                    echo '<li><a href="parametres_langues.php?file='.$file.(isset($_POST['readFile']) ? '&lang='.$Lang : '').'">'.$file.'</a></li>';
                }
            } ?>
        </ul>
        <?php endif; ?>

    </div>

    <?php eval($plxAdmin->plxPlugins->callHook('AdminTranslateLangsTop')) # Hook Plugins ?>

    <fieldset>
    <?php if(!isset($_POST['readFile'])) :?>
        <div class="grid">
            <div class="col sml-12 med-5 label-centered">
                <label for="id_default_lang"><?php echo L_CONFIG_LANG_TO_TRANSLATE ?>&nbsp;:</label>
            </div>
            <div class="col sml-12 med-7">
                <?php plxUtils::printSelect('langToTranslate', plxUtils::getLangs(), $Lang) ?>
            </div> 
        </div>

        <input type="hidden" name="readFile" value="true">

    <?php else : ?>
        
        <?php foreach ($LANG as $key => $value) :?>
           
        <div class="grid">
            <div class="col sml-12 med-5 label-centered">
                <label for="id_bypage"><?php echo $key ?>&nbsp;:</label>
            </div>
            <div class="col sml-12 med-7">
                <?php plxUtils::printInput($key, $value, 'text', '50-250',false,'fieldnum'); ?>
            </div>
        </div>
        <?php endforeach; ?>

        <input type="hidden" name="recordFile" value="true">

    <?php endif; ?>

    </fieldset>

    <?php echo plxToken::getTokenPostMethod() ?>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminTranslateLangsFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>

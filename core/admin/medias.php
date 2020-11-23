<?php

/**
 * Backoffice - Medias manager
 *
 * @package PLX
 * @author  Stephane F, Pedro "P3ter" CADETE
 **/

include __DIR__ . '/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

$path = filter_input(INPUT_GET, 'path');
//TODO vérifier que $path est bien un répertoire de $plxAdmin->aConf['medias'] sinon retour à la racine, pour éviter de naviguer sur le serveur

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasPrepend'));

# Recherche du type de medias à afficher via la session
if (empty($_SESSION['medias'])) {
    $_SESSION['medias'] = $plxAdmin->aConf['medias'];
    $_SESSION['folder'] = '';
} elseif (!empty($path)) {
    $_SESSION['currentfolder'] = (isset($_SESSION['folder']) ? $_SESSION['folder'] : '');
    $_SESSION['folder'] = ($path);
}
# Nouvel objet de type plxMedias
$plxMediasRoot = PLX_ROOT . $_SESSION['medias'];
if ($plxAdmin->aConf['userfolders'] and $_SESSION['profil'] == PROFIL_WRITER)
    $plxMediasRoot .= $_SESSION['user'] . '/';
$plxMedias = new plxMedias($plxMediasRoot, $_SESSION['folder'], $plxAdmin->aConf['default_lang']);

#----

if (!empty($_POST['btn_newfolder']) and !empty($_POST['newfolder'])) {
    $newdir = plxUtils::title2filename(trim($_POST['newfolder']));
    if ($plxMedias->newDir($newdir)) {
        $_SESSION['folder'] = $_SESSION['folder'] . $newdir . '/';
    }
    header('Location: medias.php');
    exit;
}
if (!empty($_POST['btn_renamefile']) and !empty($_POST['newname'])) {
    $plxMedias->renameFile($_POST['oldname'], $_POST['newname']);
    header('Location: medias.php');
    exit;
} elseif (!empty($path) and !empty($_POST['btn_delete'])) {
    if ($plxMedias->deleteDir($path)) {
        $_SESSION['folder'] = '';
    }
    header('Location: medias.php');
    exit;
} elseif (!empty($_POST['btn_upload'])) {
    $plxMedias->uploadFiles($_FILES, $_POST);
    header('Location: medias.php');
    exit;
} elseif (isset($_POST['selection']) and ((!empty($_POST['btn_ok']) and $_POST['selection'] == 'delete')) and isset($_POST['idFile'])) {
    $plxMedias->deleteFiles($_POST['idFile']);
    header('Location: medias.php');
    exit;
} elseif (isset($_POST['selection']) and ((!empty($_POST['btn_ok']) and $_POST['selection'] == 'move')) and isset($_POST['idFile'])) {
    $plxMedias->moveFiles($_POST['idFile'], $_SESSION['currentfolder'], $path);
    header('Location: medias.php');
    exit;
} elseif (isset($_POST['selection']) and ((!empty($_POST['btn_ok']) and $_POST['selection'] == 'thumbs')) and isset($_POST['idFile'])) {
    $plxMedias->makeThumbs($_POST['idFile'], $plxAdmin->aConf['miniatures_l'], $plxAdmin->aConf['miniatures_h']);
    header('Location: medias.php');
    exit;
}

# Tri de l'affichage des fichiers
if (isset($_POST['sort']) and !empty($_POST['sort'])) {
    $sort = $_POST['sort'];
} else {
    $sort = isset($_SESSION['sort_medias']) ? $_SESSION['sort_medias'] : 'date_desc';
}

$sort_title = 'title_desc';
$sort_date = 'date_desc';
switch ($sort) {
    case 'title_asc':
        $sort_title = 'title_desc';
        usort($plxMedias->aFiles, function ($b, $a) {
            return strcmp($a["name"], $b["name"]);
        });
        break;
    case 'title_desc':
        $sort_title = 'title_asc';
        usort($plxMedias->aFiles, function ($a, $b) {
            return strcmp($a["name"], $b["name"]);
        });
        break;
    case 'date_asc':
        $sort_date = 'date_desc';
        usort($plxMedias->aFiles, function ($b, $a) {
            return strcmp($a["date"], $b["date"]);
        });
        break;
    case 'date_desc':
        $sort_date = 'date_asc';
        usort($plxMedias->aFiles, function ($b, $a) {
            return strcmp($a["date"], $b["date"]);
        });
        break;
}
$_SESSION['sort_medias'] = $sort;

# Contenu des 2 listes déroulantes
$selectionList = array('' => L_FOR_SELECTION, 'move' => L_PLXMEDIAS_MOVE_FOLDER, 'thumbs' => L_MEDIAS_RECREATE_THUMB, '-' => '-----', 'delete' => L_DELETE_FILE);

# On inclut le header
include __DIR__ . '/top.php';

$curFolder = '/' . plxUtils::strCheck(basename($_SESSION['medias']) . '/' . $_SESSION['folder']);
$curFolders = explode('/', $curFolder);

?>

<div class="adminheader">
    <h2 class="h3-like"><?= L_MEDIAS_TITLE ?></h2>
    <p id="medias-breadcrumb">
        <?php
        echo L_MEDIAS_DIRECTORY.' : <a href="javascript:void(0)" onclick="document.forms[1].folder.value=\'.\';document.forms[1].submit();return true;" title="'.L_PLXMEDIAS_ROOT.'">('.L_PLXMEDIAS_ROOT.')</a> / ';
        if($curFolders) {
            $path='';
            foreach($curFolders as $id => $folder) {
                if(!empty($folder) AND $id>1) {
                    $path .= $folder.'/';
                    echo '<a href="javascript:void(0)" onclick="document.forms[1].folder.value=\''.$path.'\';document.forms[1].submit();return true;" title="'.$folder.'">'.$folder.'</a> / ';
                }
            }
        }
        ?>
    </p>
</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasTop')) # Hook Plugins ?>

<form method="post">
    <!-- Rename File Dialog -->
    <div id="dlgRenameFile" class="dialog">
        <div class="dialog-content">
            <?= L_MEDIAS_NEW_NAME ?>&nbsp;:&nbsp;
            <input id="id_newname" type="text" name="newname" value="" maxlength="50" size="15"/>
            <input id="id_oldname" type="hidden" name="oldname"/>
            <?= plxToken::getTokenPostMethod() ?>
            <input type="submit" name="btn_renamefile" value="<?= L_MEDIAS_RENAME ?>"/>
            <span class="dialog-close">&times;</span>
        </div>
    </div>
</form>

<div class="admin">
    <form method="post" id="form_medias" data-chk="idFile[]">

        <?= plxToken::getTokenPostMethod() ?>

        <!-- New Folder Dialog -->
        <div id="dlgNewFolder" class="dialog">
            <div class="dialog-content">
                <?= L_MEDIAS_NEW_FOLDER ?>&nbsp;:&nbsp;
                <input id="id_newfolder" type="text" name="newfolder" value="" maxlength="50" size="15"/>
                <input type="submit" name="btn_newfolder" value="<?= L_MEDIAS_CREATE_FOLDER ?>"/>
                <span class="dialog-close">&times;</span>
            </div>
        </div>

        <div id="files_manager" class="grid-6-small-1 mtm">
            <div class="col-1">
                <button onclick="dialogBox('dlgNewFolder');return false;"
                        id="btnNewFolder"><?= L_MEDIAS_NEW_FOLDER ?></button>
                <p><a href="?path=/"><?= L_PLXMEDIAS_ROOT ?></a></p>
                <?= $plxMedias->displayTreeView(); ?>
            </div>

            <div class="col-5">
                <div class="pas tableheader">
                    <button class="btn--primary" type="submit"
                            onclick="toggle_divs();return false"><i class="icon-plus"></i><?= L_MEDIAS_ADD_FILE ?>
                    </button>
                    <?php plxUtils::printSelect('selection', $selectionList, '', false, 'no-margin', 'id_selection') ?>
                    <input type="submit" name="btn_ok" value="<?= L_OK ?>"
                           onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idFile[]', '<?= L_CONFIRM_DELETE ?>')"/>
                    <?php if (!empty($_SESSION['folder'])) { ?>
                        <input type="submit" name="btn_delete"
                               class="red"
                               value="<?= L_DELETE_FOLDER ?>"
                               onclick="return confirm('<?php printf(L_MEDIAS_DELETE_FOLDER_CONFIRM, $curFolder) ?>')"/>
                    <?php } ?>
                    <input type="hidden" name="sort" value=""/>
                    <input type="text" id="medias-search" onkeyup="plugFilter()" placeholder="<?= L_SEARCH ?>..."
                           title="<?= L_SEARCH ?>"/>
                </div>

                <div class="table-scrollable">
	                <table id="medias-table" class="table">
	                    <thead>
		                    <tr>
		                        <th class="checkbox"><input type="checkbox" /></th>
		                        <th class="w10"></th>
		                        <th class="w100"><a href="javascript:void(0)" class="hcolumn"
		                                            onclick="document.forms[1].sort.value='<?= $sort_title ?>';document.forms[1].submit();return true;"><?= L_MEDIAS_FILENAME ?></a>
		                        </th>
		                        <th><?= L_MEDIAS_EXTENSION ?></th>
		                        <th><?= L_MEDIAS_FILESIZE ?></th>
		                        <th><?= L_MEDIAS_DIMENSIONS ?></th>
		                        <th><a href="javascript:void(0)" class="hcolumn"
		                               onclick="document.forms[1].sort.value='<?= $sort_date ?>';document.forms[1].submit();return true;"><?= L_DATE ?></a>
		                        </th>
		                    </tr>
	                    </thead>
	                    <tbody id="medias-table-tbody">
<?php
# Si on a des fichiers
if ($plxMedias->aFiles) {
	foreach ($plxMedias->aFiles as $v) { # Pour chaque fichier
		$isImage = in_array(strtolower($v['extension']), $plxMedias->img_supported);
		$title = pathinfo($v['name'], PATHINFO_FILENAME);
?>
                            <tr>
                                <td><input type="checkbox" name="idFile[]" value="<?= $v['name'] ?>"/></td>
                                <td class="icon">
<?php
		if (is_file($v['path']) and $isImage):
			$attrs = 'width="' . plxUtils::THUMB_WIDTH . '" height="' . plxUtils::THUMB_HEIGHT . '"';
?>
                                        <a class="overlay" title="<?= $title ?>" href="<?= $v['path'] ?>"><img
                                                    src="<?= $v['.thumb'] ?>" <?= $attrs ?> alt="<?= $title ?>"
                                                    class="thumb"/></a>
<?php
		else: $attrs = getimagesize($v['.thumb'])[3];
?>
                                        <img src="<?= $v['.thumb'] ?>" <?= $attrs ?>
                                             alt="<?= substr($v['extension'], 1) ?> " class="thumb"/>
<?php
		endif;
?>
                                </td>
                                <td data-sort="<?= $title . $v['extension'] ?>">
                                    <a class="imglink" target="_blank" title="<?= $title ?>"
                                       href="<?= $v['path'] ?>"><?= $title . $v['extension'] ?></a>
                                    <div data-copy="<?= str_replace(PLX_ROOT, '', $v['path']) ?>" title="<?= L_MEDIAS_LINK_COPYCLP ?>" class="ico">
										&#128203;<div><?= L_MEDIAS_LINK_COPYCLP_DONE ?></div>
									</div>
                                    <div data-rename="<?= $v['path'] ?>" title="<?= L_RENAME_FILE ?>" class="ico">
										&#9998;
									</div>
                                    <br/>
<?php
		$href = plxUtils::thumbName($v['path']);
		if ($isImage and is_file($href)) {
?>
                                        <?= L_MEDIAS_THUMB ?> : <a target="_blank" title="<?= $title ?>" href="<?= $href ?>"><?= plxUtils::strCheck(basename($href)) ?></a>
                                        <div data-copy="<?= str_replace(PLX_ROOT, '', $href) ?>" title="<?= L_MEDIAS_LINK_COPYCLP ?>" class="ico">
											&#128203;<div><?= L_MEDIAS_LINK_COPYCLP_DONE ?></div>
                                        </div>
<?php
		}
?>
                                </td>
                                <td data-sort="<?= strtoupper($v['extension']) ?>"><?= strtoupper($v['extension']) ?></td>
                                <td data-sort="<?= $v['filesize'] ?>">
                                    <?= plxUtils::formatFilesize($v['filesize']); ?><br/>
<?php
		if ($isImage and is_file($href)) {
			echo plxUtils::formatFilesize($v['thumb']['filesize']);
		}
?>
                                </td>
<?php
		$dimensions = '&nbsp;';
		if ($isImage and (isset($v['infos']) and isset($v['infos'][0]) and isset($v['infos'][1]))) {
			$dimensions = $v['infos'][0] . ' x ' . $v['infos'][1];
		}
		if ($isImage and is_file($href)) {
			$dimensions .= '<br />' . $v['thumb']['infos'][0] . ' x ' . $v['thumb']['infos'][1];
		}
?>
                                <td data-sort="<?= $v['infos'][0] * $v['infos'][1] ?>"><?= $dimensions ?></td>
                                <td data-sort="<?= $v['date'] ?>"><?= plxDate::formatDate(plxDate::timestamp2Date($v['date'])) ?></td>
                            </tr>
<?php
	}
} else {
?>
	                        <tr>
	                            <td colspan="7" class="center"><?= L_MEDIAS_NO_FILE ?></td>
	                        </tr>
<?php
}
?>
	                    </tbody>
	                </table>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="admin">
    <form action="medias.php" method="post" id="form_uploader" class="form_uploader" enctype="multipart/form-data">

        <div id="files_uploader" style="display:none">

            <div class="inline-form action-bar">
                <h2 class="h4"><?= L_MEDIAS_TITLE ?></h2>
                <p>
                    <?= L_MEDIAS_DIRECTORY ?> : (<?= L_PLXMEDIAS_ROOT ?>) /
                    <?php
                    if ($curFolders) {
                        $path = '';
                        foreach ($curFolders as $id => $folder) {
                            if (!empty($folder) and $id > 1) {
                                $path .= $folder . '/';
                                echo $folder . ' / ';
                            }
                        }
                    }
                    ?>
                </p>
                <p><a class="back" href="javascript:void(0)"
                      onclick="toggle_divs();return false"><?= L_MEDIAS_BACK ?></a></p>
                <input type="submit" class="button--primary" name="btn_upload" id="btn_upload"
                       value="<?= L_MEDIAS_SUBMIT_FILE ?>"/>
                <?= plxToken::getTokenPostMethod() ?>
            </div>

            <p><?= L_MEDIAS_MAX_UPLOAD_NBFILE ?> : <?= ini_get('max_file_uploads') ?></p>
            <p><?= L_MEDIAS_MAX_UPLOAD_FILE ?>
                : <?= $plxMedias->maxUpload['display'] ?><?php if ($plxMedias->maxPost['value'] > 0) echo " / " . L_MEDIAS_MAX_POST_SIZE . " : " . $plxMedias->maxPost['display']; ?></p>

            <div>
                <input id="selector_0" type="file" multiple="multiple" name="selector_0[]"/>
                <div class="files_list" id="files_list" style="margin: 1rem 0 1rem 0;"></div>
            </div>

            <div class="autogrid">
                <div>
                    <ul class="unstyled">
                        <li><?= L_MEDIAS_RESIZE ?>&nbsp;:</li>
                        <li><input type="radio" checked="checked" name="resize"
                                   value=""/>&nbsp;<?= L_MEDIAS_RESIZE_NO ?>
                        </li>
                        <?php
                        foreach (IMG_REDIM as $redim) {
                            ?>
                            <li><input type="radio" name="resize" value="<?= $redim ?>"/>&nbsp;<?= $redim ?></li>
                            <?php
                        }
                        ?>
                        <li>
                            <input type="radio" name="resize"
                                   value="<?= intval($plxAdmin->aConf['images_l']) ?>x<?= intval($plxAdmin->aConf['images_h']) ?>"/>&nbsp;<?= intval($plxAdmin->aConf['images_l']) ?>
                            x<?= intval($plxAdmin->aConf['images_h']) ?>&nbsp;&nbsp;(<a
                                    href="parametres_affichage.php"><?= L_MEDIAS_MODIFY ?>)</a>
                        </li>
                        <li>
                            <input type="radio" name="resize" value="user"/>&nbsp;<input type="text" size="2"
                                                                                         maxlength="4"
                                                                                         name="user_w"/>&nbsp;x&nbsp;<input
                                    type="text" size="2" maxlength="4" name="user_h"/>
                        </li>
                    </ul>
                </div>
                <div>
                    <ul class="unstyled">
                        <li><?= L_MEDIAS_THUMBS ?>&nbsp;</li>
                        <li>
                            <?php $sel = (!$plxAdmin->aConf['thumbs'] ? ' checked="checked"' : '') ?>
                            <input <?= $sel ?> type="radio" name="thumb" value=""/>&nbsp;<?= L_MEDIAS_THUMBS_NONE ?>
                        </li>
                        <?php
                        foreach (IMG_THUMB as $thumb) {
                            ?>
                            <li><input type="radio" name="thumb" value="<?= $thumb ?>"/>&nbsp;<?= $thumb ?></li>
                            <?php
                        }
                        ?>
                        <li>
                            <?php $sel = ($plxAdmin->aConf['thumbs'] ? ' checked="checked"' : '') ?>
                            <input <?= $sel ?> type="radio" name="thumb"
                                               value="<?= intval($plxAdmin->aConf['miniatures_l']) . 'x' . intval($plxAdmin->aConf['miniatures_h']) ?>"/>&nbsp;<?= intval($plxAdmin->aConf['miniatures_l']) . 'x' . intval($plxAdmin->aConf['miniatures_h']) ?>
                            &nbsp;&nbsp;(<a href="parametres_affichage.php"><?= L_MEDIAS_MODIFY ?>)</a>
                        </li>
                        <li>
                            <input type="radio" name="thumb" value="user"/>&nbsp;<input type="text" size="2"
                                                                                        maxlength="4"
                                                                                        name="thumb_w"/>&nbsp;x&nbsp;<input
                                    type="text" size="2" maxlength="4" name="thumb_h"/>
                        </li>
                    </ul>
                </div>
            </div>
            <?php eval($plxAdmin->plxPlugins->callHook('AdminMediasUpload')) # Hook Plugins ?>
        </div>
    </form>
</div>

<div class="modal">
    <input id="modal" type="checkbox" name="modal" tabindex="1">
    <div id="modal__overlay" class="modal__overlay">
        <div id="modal__box" class="modal__box">
            <img id="zoombox-img"/>
            <label for="modal">&#10006;</label>
        </div>
    </div>
</div>

<input id="clipboard" type="text" value="" style="display: none;"/>

<script type="text/javascript" src="<?= PLX_CORE ?>lib/medias.js"></script>

<?php

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasFoot'));

# On inclut le footer
include __DIR__ . '/foot.php';
?>

<?php

/**
 * Edition des pages statiques
 *
 * @package PLX
 * @author    Stephane F, Florent MONTHEL, Jean-Pierre Pourrez 'bazooka07'
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MANAGER);

# On édite les pages statiques
if (!empty($_POST)) {
    $plxAdmin->editConfiguration(!empty($_POST['homeStatic']) ? array('homestatic' => $_POST['homeStatic'][0]) : array('homestatic' => ''));
    $plxAdmin->editStatiques($_POST);
    header('Location: statiques.php');
    exit;
}

# On inclut le header
include 'top.php';
?>

<div class="adminheader">
    <h2 class="h3-like"><?= L_STATICS_PAGE_TITLE ?></h2>
</div>

<div class="admin">
    <form method="post" id="form_statics"  data-chk="idStatic[]">
        <?php eval($plxAdmin->plxPlugins->callHook('AdminStaticsTop')) # Hook Plugins ?>
        <div class="tableheader has-spacer">
            <?= PlxToken::getTokenPostMethod() ?>
            <input class="btn--primary" type="submit" name="update" value="<?= L_SAVE ?>"/>
<?php
if ($_SESSION['profil'] <= PROFIL_MODERATOR) {
?>
			<span class="spacer">&nbsp;</span>
			<button class="submit btn--warning" name="delete" disabled data-lang="<?= L_CONFIRM_DELETE ?>"><i class="icon-trash"></i><?= L_DELETE ?></button>
<?php
}
?>
        </div>
        <div class="scrollable-table">
            <table class="table mb0" data-rows-num='name^="order"'>
                <thead>
	                <tr>
	                    <th class="checkbox"><input type="checkbox" /></th>
	                    <th>#</th>
	                    <th><i class="icon-home-1" title="<?= L_HOMEPAGE ?>"></i></th>
	                    <th><?= L_STATICS_GROUP ?></th>
	                    <th><?= L_TITLE ?></th>
	                    <th><?= L_STATICS_URL ?></th>
	                    <th><?= L_ACTIVE ?></th>
	                    <th><?= L_ORDER ?></th>
	                    <th><?= L_MENU ?></th>
	                    <th><?= L_ACTION ?></th>
	                </tr>
                </thead>
                <tbody>
<?php
# Si on a des pages statiques
if ($plxAdmin->aStats) {
	# Initialisation de l'ordre
	$order = 1;

	# On boucle sur les pages statiques
	foreach ($plxAdmin->aStats as $staticId => $v) {
		# on teste si page d'accueil
		$selected = $plxAdmin->aConf['homestatic'] == $staticId ? ' checked="checked"' : '';

		$id = 'id_' . $staticId;
?>
                    <tr>
                        <td><input type="checkbox" name="idStatic[]" value="<?= $staticId ?>" id="<?= $id ?>" /></td>
                        <td><label for="<?= $id ?>"><?= $staticId ?></label></td>
                        <td><input title="<?= L_STATICS_PAGE_HOME ?>" type="checkbox" name="homeStatic[]" value="<?= $staticId ?>"<?= $selected ?> /></td>
                        <td><input type="text" name="group[<?= $staticId ?>]" value="<?= PlxUtils::strCheck($v['group']) ?>" maxlength="64" /></td>
                        <td><input type="text" name="name[<?= $staticId ?>]" value="<?= PlxUtils::strCheck($v['name']) ?>" maxlength="128" required /></td>
                        <td><input type="text" name="url[<?= $staticId ?>]" value="<?= PlxUtils::strCheck($v['url']) ?>" maxlength="128" /></td>
                        <td><input type="checkbox" name="active[<?= $staticId ?>]" class="switch" value="1" <?= !empty($v['active']) ? 'checked' : '' ?> /></td>
                        <td><input type="number" name="order[<?= $staticId ?>]" value="<?= $order ?>" maxlength="3" /></td>
                        <td><input type="checkbox" name="menu[<?= $staticId ?>]" class="switch" value="1" <?= !empty($v['menu']) ? 'checked' : '' ?> /></td>
                        <td>
<?php
		# boutons pour éditer et visualiser la page statique
		$url = $v['url'];
		if (!PlxUtils::checkSite($url)) {
?>
							<button><a href="statique.php?p=<?= $staticId ?>" title="<?= L_STATICS_SRC_TITLE ?>"><i class="icon-pencil"></i></a></button>
<?php
			if ($v['active']) {
?>
							<button><a href="<?= $plxAdmin->urlRewrite('?static' . intval($staticId) . '/' . $v['url']) ?>" title="<?= L_STATIC_VIEW_PAGE ?> <?= PlxUtils::strCheck($v['name']) ?> <?= L_STATIC_ON_SITE ?>" target="_blank"><i class="icon-eye"></i></a></button>
<?php
			}
		} elseif ($v['url'][0] == '?') {
?>
							<button><a href="<?= $plxAdmin->urlRewrite($v['url']) ?>" title="<?= PlxUtils::strCheck($v['name']) ?>" target="_blank"><i class="icon-eye"></i></a></button>
<?php
		} else {
?>
							<button><a href="<?= $v['url'] ?>" title="<?= PlxUtils::strCheck($v['name']) ?>" target="_blank"><i class="icon-eye"></i></a></button>
<?php
		}
?>
						</td>
					</tr>
<?php
		$order++;
	}

	# On récupère le dernier identifiant et on fait un tri inversé
	$a = array_keys($plxAdmin->aStats);
	rsort($a);
} else {
	$a['0'] = 0;
}
$newStaticId = str_pad($a['0'] + 1, 3, '0', STR_PAD_LEFT);
?>
	                <tr class="new">
	                    <td colspan="3"><?= L_STATICS_NEW_PAGE ?></td>
                        <td><input type="text" name="group[<?= $newStaticId ?>]" value="" maxlength="64" /></td>
                        <td><input type="text" name="name[<?= $newStaticId ?>]" value="" maxlength="128" /></td><?php /* not required for a new item */ ?>
                        <td><input type="text" name="url[<?= $newStaticId ?>]" value="" maxlength="128" /></td>
                        <td><input type="checkbox" name="active[<?= $newStaticId ?>]" class="switch" $value="1" /></td>
                        <td><input type="number" name="order[<?= $newStaticId ?>]" value="<?= $order ?>" maxlength="3" /></td>
                        <td><input type="checkbox" name="menu[<?= $newStaticId ?>]" class="switch" $value="1" /></td>
	                    <td>&nbsp;</td>
	                </tr>
				</tbody>
            </table>
        </div>
    </form>
</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsFoot'));

# On inclut le footer
include 'foot.php';

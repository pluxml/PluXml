<?php

/**
 * Edition des pages statiques
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include 'prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER);

# On édite les pages statiques
if(!empty($_POST)) {
	if(isset($_POST['homeStatic']))
		$plxAdmin->editConfiguration($plxAdmin->aConf, array('homestatic'=>$_POST['homeStatic'][0]));
	else
		$plxAdmin->editConfiguration($plxAdmin->aConf, array('homestatic'=>''));
	$plxAdmin->editStatiques($_POST);
	header('Location: statiques.php');
	exit;
}

$aTemplates = $plxAdmin->getTemplatesTheme();

# On inclut le header
include 'top.php';
?>
<script>
function checkBox(cb) {
	cbs=document.getElementsByName('homeStatic[]');
	for (var i = 0; i < cbs.length; i++) {
		if(cbs[i].checked==true) {
			cbs[i].checked = ((i+1) == cb) ? true: false;
		}
	}
}
</script>

<form action="statiques.php" method="post" id="form_statics">

	<div class="inline-form action-bar">
		<h2><?= L_STATICS_PAGE_TITLE ?></h2>
		<p>&nbsp;</p>
		<?php plxUtils::printSelect('selection', array( '' =>L_FOR_SELECTION, 'delete' =>L_DELETE), '', false, 'no-margin', 'id_selection'); ?>
		<input type="submit" name="submit" value="<?= L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idStatic[]', '<?= L_CONFIRM_DELETE ?>')" />
		<?= plxToken::getTokenPostMethod() ?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input type="submit" name="update" value="<?= L_STATICS_UPDATE ?>" />
	</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminStaticsTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="statics-table" class="full-width"  data-rows-num='name$="_ordre"'>
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idStatic[]')" /></th>
					<th><?= L_ID ?></th>
					<th><?= L_STATICS_HOME_PAGE ?></th>
					<th><?= L_STATICS_GROUP ?></th>
					<th class="required"><?= L_STATICS_TITLE ?></th>
					<th><?= L_STATICS_URL ?></th>
					<th><?= L_STATICS_TEMPLATE_FIELD ?></th>
					<th><?= L_STATICS_ACTIVE ?></th>
					<th data-id="order"><?= L_STATICS_ORDER ?></th>
					<th><?= L_STATICS_MENU ?></th>
					<th><?= L_STATICS_ACTION ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			# Initialisation de l'ordre
			$ordre = 1;
			# Si on a des pages statiques
			if($plxAdmin->aStats) {
				foreach($plxAdmin->aStats as $k=>$v) { # Pour chaque page statique
					$selected = ($plxAdmin->aConf['homestatic'] == $k) ? ' checked="checked"' : '';
?>
				<tr>
					<td>
						<input type="checkbox" name="idStatic[]" value="<?= $k ?>" />
						<input type="hidden" name="staticNum[]" value="<?= $k ?>" />
					</td>
					<td><?= $k ?></td>
					<td>
						<input title="<?= L_STATICS_PAGE_HOME ?>" type="checkbox" name="homeStatic[]" value="<?= $k ?>"<?= $selected ?> onclick="checkBox('<?= $ordre ?>')" />
					</td><td>
						<?php plxUtils::printInput($k.'_group', plxUtils::strCheck($v['group']), 'text', '-100'); ?>
					</td><td>
						<?php plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '-255', false, '', '', '', true); ?>
					</td><td>
						<?php plxUtils::printInput($k.'_url', $v['url'], 'text', '-255'); ?>
					</td><td>
						<?php plxUtils::printSelect($k.'_template', $aTemplates, $v['template']); ?>
					</td><td>
						<?php plxUtils::printSelect($k.'_active', array('1'=>L_YES,'0'=>L_NO), $v['active']); ?>
					</td><td>
						<?php plxUtils::printInput($k.'_ordre', $ordre, 'text', '2-3') ?>
					</td><td>
						<?php plxUtils::printSelect($k.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), $v['menu']); ?>
					</td><td>
<?php
					if(!plxUtils::checkSite($v['url'], false)) {
						$filename = PLX_ROOT . $plxAdmin->aConf['racine_statiques'] . $k . '.' . $v['url'] . '.php';
?>
						<a href="statique.php?p=<?= $k ?>" class="<?= empty($v['readable']) ? 'text-red' : '' ?>" title="<?= L_STATICS_SRC_TITLE ?>"><?= L_STATICS_SRC ?></a>
<?php
						if($v['active'] and file_exists($filename)) {
?>
							&nbsp;&nbsp;<a href="<?= $plxAdmin->urlRewrite('?' . L_STATIC_URL . intval($k) . '/' . $v['url']) ?>" title="<?= L_STATIC_VIEW_PAGE ?> '<?= plxUtils::strCheck($v['name']); ?>' <?= L_STATIC_ON_SITE ?>" target="_blank"><?= L_VIEW ?></a>
<?php
						}
					} else {
						$href = ($v['url'][0] == '?') ? $plxAdmin->urlRewrite($v['url']) : $v['url'];
?>
						<a href="<?= $href ?>" title="<?= plxUtils::strCheck($v['name']) ?>"><?= L_VIEW ?></a>
<?php
					}
?>
					</td>
				</tr>
<?php
					$ordre++;
				}

				# On récupère le dernier identifiant
				$a = array_keys($plxAdmin->aStats);
				rsort($a);
				$newId = $a[0] + 1;
			} else {
				$newId = 1;
			}

			# Pour une nouvelle page statique
			$new_staticid = str_pad($newId, 3, '0', STR_PAD_LEFT);
?>
				<tr class="new">
					<td colspan="3"><?= L_STATICS_NEW_PAGE ?></td>
					<td>
						<input type="hidden" name="staticNum[]" value="<?= $new_staticid ?>" />
						<?php plxUtils::printInput($new_staticid.'_group', '', 'text', '-100'); ?>
					</td><td>
						<?php plxUtils::printInput($new_staticid.'_name', '', 'text', '-255'); ?>
					</td><td>
						<?php plxUtils::printInput($new_staticid.'_url', '', 'text', '-255'); ?>
					</td><td>
						<?php plxUtils::printSelect($new_staticid.'_template', $aTemplates); ?>
					</td><td>
						<?php plxUtils::printSelect($new_staticid.'_active', array('1'=>L_YES,'0'=>L_NO), '0'); ?>
					</td><td>
						<?php plxUtils::printInput($new_staticid.'_ordre', $ordre, 'text', '2-3'); ?>
					</td><td>
						<?php plxUtils::printSelect($new_staticid.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), '1'); ?>
					</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsFoot'));

# On inclut le footer
include 'foot.php';

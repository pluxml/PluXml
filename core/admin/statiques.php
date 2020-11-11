<?php

/**
 * Edition des pages statiques
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL, Jean-Pierre Pourrez "bazooka07"
 **/

include __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_MANAGER);

# On édite les pages statiques
if(!empty($_POST)) {
	$plxAdmin->editConfiguration(!empty($_POST['homeStatic']) ? array('homestatic'=>$_POST['homeStatic'][0]) : array('homestatic'=>''));
	$plxAdmin->editStatiques($_POST);
	header('Location: statiques.php');
	exit;
}

# On inclut le header
include __DIR__ .'/top.php';

?>
<form method="post" id="form_statics">

	<div class="inline-form action-bar">
		<h2><?= L_STATICS_PAGE_TITLE ?></h2>
		<p>
			<a class="back" href="index.php"><?= L_BACK_TO_ARTICLES ?></a>
		</p>
<?php plxUtils::printSelect('selection', array( '' =>L_FOR_SELECTION, 'delete' =>L_DELETE), '', false, 'no-margin', 'id_selection') ?>
		<input type="submit" name="delete" value="<?= L_OK ?>" disabled onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idStatic[]', '<?= L_CONFIRM_DELETE ?>')" />
		<?= plxToken::getTokenPostMethod() ?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input type="submit" name="update" value="<?= L_STATICS_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminStaticsTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="statics-table" class="full-width"  data-rows-num='name^="ordre["'>
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idStatic[]')" /></th>
					<th>#</th>
					<th>
						<label for="id_clear_homepage" style="cursor: pointer; padding: 0;"><?= L_HOMEPAGE ?></label>
						<input type="radio" name="homeStatic" id="id_clear_homepage" value="" style="display: none;" />
					</th>
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
			# Initialisation de l'ordre
			$ordre = 1;
			# Si on a des pages statiques
			if($plxAdmin->aStats) {
				foreach($plxAdmin->aStats as $k=>$v) { # Pour chaque page statique
					$checked = ($plxAdmin->aConf['homestatic'] == $k) ? ' checked="checked"' : '';
?>
				<tr>
					<td>
						<input type="checkbox" name="idStatic[]" id="id_static_<?= $k ?>" value="<?= $k ?>" />
						<?php plxUtils::printInput('template[' . $k . ']', plxUtils::strCheck($v['template']), 'hidden'); echo PHP_EOL; ?>
					</td>
					<td><label for="id_static_<?= $k ?>"><?= $k ?></label></td>
					<td>
						<input title="<?= L_STATICS_PAGE_HOME ?>" type="radio" name="homeStatic" value="<?= $k ?>"<?= $checked ?> />
					</td><td>
						<?php plxUtils::printInput('group[' . $k . ']', plxUtils::strCheck($v['group']), 'text', '-100'); echo PHP_EOL; ?>
					</td><td>
						<?php plxUtils::printInput('name[' . $k . ']', plxUtils::strCheck($v['name']), 'text', '-255'); echo PHP_EOL; ?>
					</td><td>
						<?php plxUtils::printInput('url[' . $k . ']', $v['url'], 'text', '-255'); echo PHP_EOL; ?>
					</td><td>
<?php plxUtils::printSelect('active[' . $k . ']', array(1=>L_YES, 0=>L_NO), $v['active']); ?>
					</td><td>
						<?php plxUtils::printInput('ordre[' . $k . ']', $ordre, 'text', '2-3'); echo PHP_EOL; ?>
					</td><td>
<?php plxUtils::printSelect('menu[' . $k . ']', array(1=>L_DISPLAY, 0=>L_HIDE), $v['menu']); ?>
					</td><td>
<?php
					$url = $v['url'];
					if(!plxUtils::checkSite($url)) {
?>
						<a href="statique.php?p=<?= $k ?>" title="<?= L_STATICS_SRC_TITLE ?>"><?= L_EDIT ?></a>
<?php
						if($v['active']) {
?>
						<a href="<?= $plxAdmin->urlRewrite('?static'.intval($k).'/'.$v['url']) ?>" title="<?= L_STATIC_VIEW_PAGE . ' ' . plxUtils::strCheck($v['name']) . ' ' . L_STATIC_ON_SITE ?>"><?= L_VIEW ?></a>
<?php
						}
					} elseif($v['url'][0] == '?' ) {
?>
						<a href="'.$plxAdmin->urlRewrite($v['url']).'" title="'.plxUtils::strCheck($v['name']).'"><?= L_VIEW ?></a>
<?php
					} else {
?>
						<a href="<?= $v['url'] ?>" title="<?= plxUtils::strCheck($v['name']) ?>"><?= L_VIEW ?></a>
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
			} else {
				$a['0'] = 0;
			}
			$new_staticid = str_pad($a['0'] + 1, 3, '0', STR_PAD_LEFT);
?>
				<tr class="new">
					<td colspan="3"><?= L_STATICS_NEW_PAGE ?></td>
					<td>
						<?php plxUtils::printInput('group[' . $new_staticid . ']', '', 'text', '-100'); echo PHP_EOL; ?>
					</td><td>
						<?php plxUtils::printInput('name[' . $new_staticid . ']', '', 'text', '-255'); echo PHP_EOL; ?>
					</td><td>
						<?php plxUtils::printInput('url[' . $new_staticid . ']', '', 'text', '-255'); echo PHP_EOL; ?>
					</td><td>
<?php plxUtils::printSelect('active[' . $new_staticid . ']', array('1'=>L_YES,'0'=>L_NO), '0'); ?>
					</td><td>
						<?php plxUtils::printInput('ordre[' . $new_staticid . ']', $ordre, 'text', '2-3'); echo PHP_EOL; ?>
					</td><td>
<?php plxUtils::printSelect('menu[' . $new_staticid . ']', array(1=>L_DISPLAY, 0=>L_HIDE), 1); ?>
					</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>
</form>
<script>
	(function() {
		'use strict';
		document.getElementById('form_statics').addEventListener('change', function(event) {
			if(
				(event.target.tagName == 'INPUT' && event.target.name == 'idStatic[]') ||
				(event.target.tagName == 'SELECT' && event.target.name == 'selection')
			) {
				var cnt = 0;
				const myForm = event.target.form;
				if(myForm.elements['selection'].value == 'delete') {
					const chks = myForm.elements['idStatic[]'];
					for(var i=0, iMax=chks.length; i<iMax; i++) {
						if(chks[i].checked) {
							cnt++;
						}
					}
				}
				myForm.elements['delete'].disabled = (cnt == 0);
				myForm.elements['update'].disabled = (cnt > 0);
			}
		});
	})();
</script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsFoot'));

# On inclut le footer
include __DIR__ .'/foot.php';

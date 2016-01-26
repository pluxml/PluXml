<?php

/**
 * Edition des pages statiques
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

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

# On inclut le header
include(dirname(__FILE__).'/top.php');
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
		<h2><?php echo L_STATICS_PAGE_TITLE ?></h2>
		<p><a class="back" href="index.php"><?php echo L_BACK_TO_ARTICLES ?></a></p>
		<?php plxUtils::printSelect('selection', array( '' =>L_FOR_SELECTION, 'delete' =>L_DELETE), '', false, 'no-margin', 'id_selection') ?>
		<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idStatic[]', '<?php echo L_CONFIRM_DELETE ?>')" />
		<?php echo plxToken::getTokenPostMethod() ?>
		&nbsp;&nbsp;&nbsp;
		<input type="submit" name="update" value="<?php echo L_STATICS_UPDATE ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminStaticsTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="statics-table" class="full-width">
			<thead>
				<tr>
					<th><input type="checkbox" onclick="checkAll(this.form, 'idStatic[]')" /></th>
					<th><?php echo L_STATICS_ID.' '.L_PAGE ?></th>
					<th><?php echo L_STATICS_HOME_PAGE ?></th>
					<th><?php echo L_STATICS_GROUP ?></th>
					<th><?php echo L_STATICS_TITLE ?></th>
					<th><?php echo L_STATICS_URL ?></th>
					<th><?php echo L_STATICS_ACTIVE ?></th>
					<th><?php echo L_STATICS_ORDER ?></th>
					<th><?php echo L_STATICS_MENU ?></th>
					<th><?php echo L_STATICS_ACTION ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			# Initialisation de l'ordre
			$num = 0;
			# Si on a des pages statiques
			if($plxAdmin->aStats) {
				foreach($plxAdmin->aStats as $k=>$v) { # Pour chaque page statique
					$ordre = ++$num;
					echo '<tr>';
					echo '<td><input type="checkbox" name="idStatic[]" value="'.$k.'" /><input type="hidden" name="staticNum[]" value="'.$k.'" /></td>';
					echo '<td>'.$k.'</td><td>';
					$selected = $plxAdmin->aConf['homestatic']==$k ? ' checked="checked"' : '';
					echo '<input title="'.L_STATICS_PAGE_HOME.'" type="checkbox" name="homeStatic[]" value="'.$k.'"'.$selected.' onclick="checkBox(\''.$num.'\')" />';
					echo '</td><td>';
					plxUtils::printInput($k.'_group', plxUtils::strCheck($v['group']), 'text', '10-100');
					echo '</td><td>';
					plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '10-255');
					echo '</td><td>';
					plxUtils::printInput($k.'_url', $v['url'], 'text', '10-255');
					echo '</td><td>';
					plxUtils::printSelect($k.'_active', array('1'=>L_YES,'0'=>L_NO), $v['active']);
					echo '</td><td>';
					plxUtils::printInput($k.'_ordre', $ordre, 'text', '1-3');
					echo '</td><td>';
					plxUtils::printSelect($k.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), $v['menu']);

					if(!plxUtils::checkSite($v['url'])) {
						echo '</td><td>';
						echo '<a href="statique.php?p='.$k.'" title="'.L_STATICS_SRC_TITLE.'">'.L_STATICS_SRC.'</a>';
						if($v['active']) {
							echo '&nbsp;&nbsp;<a href="'.PLX_ROOT.'?static'.intval($k).'/'.$v['url'].'" title="'.L_STATIC_VIEW_PAGE.' '.plxUtils::strCheck($v['name']).' '.L_STATIC_ON_SITE.'">'.L_VIEW.'</a>';
						}
						echo '</td></tr>';
					}
					elseif($v['url'][0]=='?')
						echo '</td><td><a href="'.$plxAdmin->urlRewrite($v['url']).'" title="'.plxUtils::strCheck($v['name']).'">'.L_VIEW.'</a></td></tr>';
					else
						echo '</td><td><a href="'.$v['url'].'" title="'.plxUtils::strCheck($v['name']).'">'.L_VIEW.'</a></td></tr>';
				}
				# On récupère le dernier identifiant
				$a = array_keys($plxAdmin->aStats);
				rsort($a);
			} else {
				$a['0'] = 0;
			}
			$new_staticid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
			?>
				<tr>
					<td>&nbsp;</td>
					<td><?php echo L_STATICS_NEW_PAGE ?></td>
					<td>
					<?php
						echo '<input type="hidden" name="staticNum[]" value="'.$new_staticid.'" />';
						echo '</td><td>';
						plxUtils::printInput($new_staticid.'_group', '', 'text', '10-100');
						echo '</td><td>';
						plxUtils::printInput($new_staticid.'_name', '', 'text', '10-255');
						plxUtils::printInput($new_staticid.'_template', 'static.php', 'hidden');
						echo '</td><td>';
						plxUtils::printInput($new_staticid.'_url', '', 'text', '10-255');
						echo '</td><td>';
						plxUtils::printSelect($new_staticid.'_active', array('1'=>L_YES,'0'=>L_NO), '0');
						echo '</td><td>';
						plxUtils::printInput($new_staticid.'_ordre', ++$num, 'text', '1-3');
						echo '</td><td>';
						plxUtils::printSelect($new_staticid.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), '1');
					?>
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
include(dirname(__FILE__).'/foot.php');
?>
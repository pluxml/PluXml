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

$yes_no = array('1'=>L_YES,'0'=>L_NO);
?>

<form action="statiques.php" method="post" id="form_statics">

	<div class="inline-form action-bar">
		<h2><?php echo L_STATICS_PAGE_TITLE ?></h2>
		<p><a class="back" href="index.php"><?php echo L_BACK_TO_ARTICLES ?></a></p>
		<div class="flex-line">
			<?php plxUtils::printSelect('selection', array( '' =>L_FOR_SELECTION, 'delete' =>L_DELETE), '', false, 'no-margin', 'id_selection') ?>
			<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idStatic[]', '<?php echo L_CONFIRM_DELETE ?>')" />
			<?php echo plxToken::getTokenPostMethod() ?>
			<span class="spacer">&nbsp;</span>
			<input type="submit" name="update" value="<?php echo L_STATICS_UPDATE ?>" />
		</div>
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminStaticsTop')) # Hook Plugins ?>

	<div class="scrollable-table">
		<table id="statics-table" class="full-width">
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idStatic[]')" /></th>
					<th><?php echo L_ID ?></th>
					<th>
						<input id="homepage" name="homeStatic" type="radio" name="" value="" style="display: none;" />
						<label for="homepage"><img src="<?php echo PLX_CORE; ?>admin/theme/images/homepage.svg" alt="<?php echo L_HOMEPAGE ?>" title="<?php echo L_STATICS_HOME_PAGE; ?>"></label>
					</th>
					<th><?php echo L_STATICS_GROUP ?></th>
					<th><?php echo L_STATICS_TITLE ?></th>
					<th><?php echo L_STATICS_URL ?></th>
					<th><?php echo L_STATICS_ACTIVE ?></th>
					<th data-id="order"><?php echo L_STATICS_ORDER ?></th>
					<th><?php echo L_STATICS_MENU ?></th>
					<th><?php echo L_STATICS_ACTION ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			# Initialisation de l'ordre
			$ordre = 0;
			# Si on a des pages statiques
			if($plxAdmin->aStats) {
				foreach($plxAdmin->aStats as $k=>$v) { # Pour chaque page statique
					$ordre++;
					$selected = $plxAdmin->aConf['homestatic']==$k ? ' checked="checked"' : '';
					$url = $v['url'];
					if(!plxUtils::checkSite($url)) {
						$href1 = 'statique.php?p='.$k;
						$title1 = L_STATICS_SRC_TITLE;
						$caption1 = L_STATICS_SRC;
					} elseif($v['url'][0]=='?') {
						$href1 = $plxAdmin->urlRewrite($v['url']);
						$title1 = plxUtils::strCheck($v['name']);
						$caption1 = L_VIEW;
					} else {
						$href1 = $v['url'];
						$title1 = plxUtils::strCheck($v['name']);
						$caption1 = L_VIEW;
					}
?>
				<tr>
					<td>
						<input type="checkbox" name="idStatic[]" value="<?php echo $k; ?>" />
						<input type="hidden" name="staticNum[]" value="<?php echo $k; ?>" /></td>
					<td><?php echo $k; ?></td>
					<td>
						<input title="<?php echo L_STATICS_PAGE_HOME; ?>" type="radio" name="homeStatic" value="<?php echo $k; ?>"<?php echo $selected; ?> />
					</td>
					<td><?php plxUtils::printInput($k.'_group', plxUtils::strCheck($v['group']), 'text', '-100'); ?></td>
					<td><?php plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '-255'); ?></td>
					<td><?php plxUtils::printInput($k.'_url', $v['url'], 'text', '-255'); ?></td>
					<td><?php plxUtils::printSelect($k.'_active', $yes_no, $v['active']); ?></td>
					<td><?php plxUtils::printInput($k.'_ordre', $ordre, 'text', '2-3'); ?></td>
					<td><?php plxUtils::printSelect($k.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), $v['menu']); ?></td>
					<td>
						<a href="<?php echo $href1; ?>" title="<?php echo $title1; ?>"><?php echo $caption1; ?></a>
<?php
					if($v['active']) {
						$href2 = $plxAdmin->urlRewrite('?static'.intval($k).'/'.$v['url']);
						$title2 = L_STATIC_VIEW_PAGE;
						$caption2 = L_VIEW;
?>
						<a href="<?php echo $href2; ?>" title="<?php echo $title2; ?>"><?php echo $caption2; ?></a>
<?php
					}
?>
					</td>
				</tr>
<?php
				}
				# On récupère le dernier identifiant
				$a = array_keys($plxAdmin->aStats);
				rsort($a);
			} else {
				$a['0'] = 0;
			}
			$new_staticid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
			?>
				<tr class="new"><?php /* nouvelle page statique */ ?>
					<td colspan="3"><?php echo L_STATICS_NEW_PAGE ?></td>
					<td>
						<input type="hidden" name="staticNum[]" value="<?php echo $new_staticid; ?>" />
						<?php plxUtils::printInput($k.'_group', '', 'text', '-100'); ?>
					</td>
					<td>
						<?php plxUtils::printInput($k.'_name', '', 'text', '-255'); ?>
						<?php plxUtils::printInput($new_staticid.'_template', 'static.php', 'hidden'); ?>
					</td>
					<td><?php plxUtils::printInput($k.'_url', '', 'text', '-255'); ?></td>
					<td><?php plxUtils::printSelect($k.'_active', $yes_no, 0); ?></td>
					<td><?php plxUtils::printInput($k.'_ordre', $ordre, 'text', '2-3'); ?></td>
					<td><?php plxUtils::printSelect($k.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), $v['menu']); ?></td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>

</form>

<script type="text/javascript">
	dragAndDrop('#statics-table tbody tr:not(.new)', '#statics-table tbody tr:not(.new) input[name$="_ordre"]');
</script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminStaticsFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
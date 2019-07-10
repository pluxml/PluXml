<?php 
$adminTitle = L_PLUGINS_TITLE;
$inputChecked = true;
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsTop')) # Hook Plugins ?>

<?php ob_start(); ?>

<form action="parametres_plugins.php" method="post" id="form_plugins">

	<div class="inline-form admin-title">
		<span data-scope="admin">Admin</span>
		<span data-scope="site">Site</span>
		
		<ul class="menu">
			<?php echo implode($breadcrumbs); ?>
		</ul>
		<?php echo plxToken::getTokenPostMethod() ?>
		<?php plxUtils::printSelect('selection', $aSelList,'', false,'','id_selection'); ?>
		<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'chkAction[]', '<?php echo L_CONFIRM_DELETE ?>')" />
		&nbsp;&nbsp;&nbsp;
		<?php if($sel==1) { ?>
		<input type="submit" name="update" value="<?php echo L_PLUGINS_APPLY_BUTTON ?>" />
		<?php } ?>
	</div>

	<div class="scrollable-table">
		<table id="plugins-table" class="full-width" <?php if(!empty($data_rows_num)) echo $data_rows_num; ?>>
			<thead>
				<tr>
					<th><input type="checkbox" onclick="checkAll(this.form, 'chkAction[]')" /></th>
					<th>&nbsp;</th>
					<th><input type="text" id="plugins-search" onkeyup="plugFilter()" placeholder="<?php echo L_SEARCH ?>..." title="<?php echo L_SEARCH ?>" /></th>
					<?php if($_SESSION['selPlugins']=='1') : ?>
					<th><?php echo L_PLUGINS_LOADING_SORT ?></th>
					<?php endif; ?>
					<th><?php echo L_PLUGINS_ACTION ?></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $plugins ?>
			</tbody>
		</table>
	</div>

</form>

<script>
function plugFilter() {
	var input, filter, table, tr, td, i;
	filter = document.getElementById("plugins-search").value;
	table = document.getElementById("plugins-table");
	tr = table.getElementsByTagName("tr");
	for (i = 0; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td")[2];
		if (td != undefined) {
			if (td.innerHTML.toLowerCase().indexOf(filter.toLowerCase()) > -1) {
				tr[i].style.display = "";
			} else {
				tr[i].style.display = "none";
			}
		}
	}
	if (typeof(Storage) !== "undefined" && filter !== "undefined") {
		localStorage.setItem("plugins_search", filter);
	}
}
if (typeof(Storage) !== "undefined" && localStorage.getItem("plugins_search") !== "undefined") {
	input = document.getElementById("plugins-search");
	input.value = localStorage.getItem("plugins_search");
	plugFilter();
}
</script>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot'));
?>

<?php $mainContent = ob_get_clean(); ?>
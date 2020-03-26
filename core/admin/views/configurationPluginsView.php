<?php 
/**
 * Plugins administration view
 * @author	Stephane F, Pedro "P3ter" CADETE
 **/

use Pluxml\PlxToken;
use Pluxml\PlxUtils;

// Header
include __DIR__ .'/../tags/top.php';
?>

<div class="adminheader">
	<h2 class="h3-like"><?= L_MENU_CONFIG ?></h2>
</div>

<div class="admin mtm grid-6">
	<div class="col-1 mtl">
		<?php include __DIR__ .'/../tags/configurationMenu.php'; ?>
	</div>
	<div class="panel col-5">
		<form action="configurationPlugins.php" method="post" id="form_plugins">
		
			<div class="panel-header">
				<h3 class="h4-like">
					<?php echo L_PLUGINS_TITLE ?>
					<span data-scope="admin">Admin</span>
					<span data-scope="site">Site</span>
				</h3>
				<ul class="menu">
					<?php echo implode($breadcrumbs); ?>
				</ul>
			</div>
		
			<div class="panel-content">
				<?php echo PlxToken::getTokenPostMethod() ?>
				<?php PlxUtils::printSelect('selection', $aSelList,'', false,'','id_selection'); ?>
				<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'chkAction[]', '<?php echo L_CONFIRM_DELETE ?>')" />
				<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
				<?php if($sel==1) { ?>
				<input type="submit" name="update" value="<?php echo L_PLUGINS_APPLY_BUTTON ?>" />
				<?php } ?>
		
				<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsTop')) # Hook Plugins ?>
			
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
			</div>
		</form>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsPluginsFoot')); ?>

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

	</div>
</div>

<?php
// Footer
include __DIR__ .'/../tags/foot.php';
?>
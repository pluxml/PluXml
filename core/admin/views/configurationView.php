<?php 
/**
 * Configuration view
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

// Header
include __DIR__ .'/../tags/top.php';
?>

<div class="adminheader">
	<h2 class="h3-like"><?= L_MENU_CONFIG ?></h2>
</div>

<div class="admin mtm grid-6">
	<div class="col-1 mtl">
		<ul class="mlm configMenu unstyled">
			<li><?= L_MENU_CONFIG_BASE ?></li>
			<li><?= L_MENU_CONFIG_VIEW ?></li>
			<li><?= L_MENU_CONFIG_ADVANCED ?></li>
			<li><?= L_MENU_CONFIG_USERS ?></li>
			<li><?= L_MENU_CONFIG_PLUGINS ?></li>
			<li><?= L_THEMES ?></li>
			<li><?= L_MENU_CONFIG_INFOS ?></li>
		</ul>
	</div>
	<div class="panel col-5">
		<?= $content ?>
	</div>
</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot')); ?>

<?php
// Footer
include __DIR__ .'/../tags/foot.php';
?>
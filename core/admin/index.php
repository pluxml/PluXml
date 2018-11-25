<?php

/**
 * Page d'accueil de l'administration
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

include __DIR__ .'/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# On inclut le header
include __DIR__ .'/top.php';
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<div class="inline-form action-bar">
	<i class="ico icon-th-large"></i>
	<h2><?php echo L_DASHBOARD_TITLE ?></h2>
</div>

<p><strong><?php echo L_PLUXML_CHECK_VERSION ?></strong></p>
<?php echo $plxAdmin->checkMaj(); ?>

<div class="grid">
	<div class="col sml-6">


	</div>
	<div class="col sml-6 text-right">

	</div>
</div>


<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>
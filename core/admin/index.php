<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include __DIR__ .'/prepend.php';
use Pluxml\PlxMsg;

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

# On inclut le header
include __DIR__ .'/top.php';
?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<div class="adminheader">
	<h2 class="h3-like">Tableau de bord (lang)</h2>
</div>

<div class="admin">
<?php
if(is_file(PLX_ROOT.'install.php'))
	echo '<p class="alert red">'.L_WARNING_INSTALLATION_FILE.'</p>'."\n";
PlxMsg::Display();
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminTopBottom'));
?>

<div class="autogrid mts mbs">

</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include __DIR__ .'/foot.php';
?>

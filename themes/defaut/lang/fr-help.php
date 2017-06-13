<?php
if (!defined('PLX_ROOT')) exit;

$path = (!empty($plxShow)) ? $plxShow->template() : PLX_ROOT.$plxAdmin->aConf['racine_themes'].$page;
?>
<p><img src="<?php echo $path; ?>/img/pluxml.jpg" /></p>
<p>Ce thème a été publié avec la version 5.6 de <a href="http://pluxml.org">PluXml</a>.</p>
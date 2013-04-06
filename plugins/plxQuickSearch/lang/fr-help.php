<?php if(!defined('PLX_ROOT')) exit; ?>

<h2>Aide</h2>
<p>
Editer pluxml/core/admin/medias.php et ajouter ligne 232 :
<br />
<pre>&lt;?php eval($plxAdmin->plxPlugins->callHook('AdminMediasQuickSearch')) # Hook Plugins ?&gt;</pre>
</p>

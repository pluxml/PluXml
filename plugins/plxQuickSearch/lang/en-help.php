<?php if(!defined('PLX_ROOT')) exit; ?>

<h2>Help</h2>
<p>
Edit pluxml/core/admin/medias.php and add line 232 :
<br />
<pre>&lt;?php eval($plxAdmin->plxPlugins->callHook('AdminMediasQuickSearch')) # Hook Plugins ?&gt;</pre>
</p>

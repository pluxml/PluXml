<?php if(!defined('PLX_ROOT')) exit; ?>
<div id="sidebar">
	<div id="categories">
		<h2>Navigation</h2>
		<ul>
			<?php $plxShow->staticList('Accueil'); ?>
			<?php $plxShow->catList(); ?>
		</ul>
		<h2>Derniers articles</h2>
		<ul>
			<?php $plxShow->lastArtList('<li><a href="#art_url" title="#art_title">#art_title</a></li>'); ?>
		</ul>
		<h2>Derniers commentaires</h2>
		<ul>
			<?php $plxShow->lastComList('<li><a href="#com_url">#com_author a dit :</a><br/><p style="padding-left:18px">#com_content(70)</p></li>'); ?>
		</ul>
	</div>
</div>
<div class="clearer"></div>
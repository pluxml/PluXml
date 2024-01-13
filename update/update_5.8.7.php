<?php

/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author Pedro "P3ter" CADETE
 **/
class update_5_8_7 extends plxUpdate
{

	# Reconstruction des fichiers admin.css et site.css pour les plugins actifs
	# dans le dossier data en remplacement du dossiers plguins
	public function step1()
	{
?>
	<li><?= L_BUILD_CSS_PLUGINS_CACHE ?></li>
<?php
		foreach(array('admin', 'site') as $context) {
			$this->plxAdmin->plxPlugins->cssCache($context);
			$oldFilename = PLX_PLUGINS . $context . '.css';
			if(file_exists($oldFilename)) {
				unlink($oldFilename);
			}
		}
		return true;
	}
}

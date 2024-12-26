<?php

/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author Pedro "P3ter" CADETE
 **/
class update_5_8_7 extends plxUpdate {

	# Reconstruction des fichiers admin.css et site.css pour les plugins actifs
	# dans le dossier data en remplacement du dossiers plugins
	public function step1()
	{
?>
	<li><?= L_BUILD_CSS_PLUGINS_CACHE ?></li>
<?php
		if(empty($this->plxMotor->plxPlugins)) {
			$this->plxMotor->plxPlugins = new plxPlugins(USER_LANG);
		}
		$this->plxMotor->plxPlugins->loadPlugins();
		foreach(array('admin', 'site') as $context) {
			$this->plxMotor->plxPlugins->cssCache($context);
			$oldFilename = PLX_PLUGINS . $context . '.css';
			if(is_writable($oldFilename)) {
				unlink($oldFilename);
			}
		}

		# nouveaux paramètres
		return $this->updateParameters();
	}
}

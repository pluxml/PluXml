<?php
/**
 * Classe de mise a jour pour PluXml version 5.4
 *
 * Release on 13 Jul 2015
 *
 * @package PLX
 * @author	Stephane F, J.P. Pourrez
 **/
class update_5_4 extends plxUpdate {

	# Création du dossier pour les médias
	# Suppression des dossiers images et documents
	public function step1() {
?>
		<p><?= L_UPDATE_UPDATE_PLUGINS_FILE ?></p>
<?php
		# vérification de l'existence des dossiers médias
		$folder =  PLX_ROOT . $this->plxAdmin->aConf['images'];
		if(is_dir($folder)) {
			$this->plxAdmin->aConf['medias'] = $this->plxAdmin->aConf['images'];
		} else {
			$folder = PLX_ROOT . $this->plxAdmin->aConf['medias'];
			@mkdir($folder, 0755, true);
?>
		<p><?php printf(L_UPDATE_NEW_FOLDER, $folder); ?></p>
<?php
		}

		# on supprime les paramètres obsolètes
		foreach(array('images', 'documents') as $k) {
			if(array_key_exists($k, $this->plxAdmin->aConf)) {
				unset($this->plxAdmin->aConf[$k]);
?>
		<p><?php printf(L_UPDATE_DEPRECATED_PARAMETER, $k); ?></p>
<?php
			}
		}

		return true; # pas d'erreurs
	}

}

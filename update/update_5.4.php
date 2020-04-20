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

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# vérification de l'existence des dossiers médias
		if(!is_dir(PLX_ROOT.'data/medias')) {
			@mkdir(PLX_ROOT.'data/medias',0755,true);
		}

		# nouveaux dossier pour les médias
		$medias = empty($this->plxAdmin->aConf['images']) ? dirname(PLX_CONFIG_PATH) . '/medias/' : $this->plxAdmin->aConf['images'];

		# on supprime les paramètres obsolètes
		unset($this->plxAdmin->aConf['images']);
		unset($this->plxAdmin->aConf['documents']);

		echo $this->updateParameters(array(
			'custom_admincss_file'	=> '',
			'medias'				=> $medias;
		));
		return true; # pas d'erreurs
	}

}

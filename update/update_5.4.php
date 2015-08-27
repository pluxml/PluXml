<?php
/**
 * Classe de mise a jour pour PluXml version 5.4
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_4 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# vérification de l'existence des dossiers médias
		if(!is_dir(PLX_ROOT.'data/medias')) {
			@mkdir(PLX_ROOT.'data/medias',0755,true);
		}
		# nouveaux paramètres
		$new_parameters = array();
		$new_parameters['custom_admincss_file'] = '';
		if(!isset($this->plxAdmin->aConf['images']) OR empty($this->plxAdmin->aConf['images']))
			$new_parameters['medias'] = 'data/medias/';
		else
			$new_parameters['medias'] = $this->plxAdmin->aConf['images']; 
		# on supprime les paramètres obsolètes
		unset($this->plxAdmin->aConf['images']);
		unset($this->plxAdmin->aConf['documents']);
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

}
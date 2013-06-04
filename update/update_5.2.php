<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.8
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_2 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		# nouveaux parametres
		$new_parameters = array();
		$new_parameters['hometemplate'] = 'home.php';
		# on supprime les parametres obsoletes
		unset($this->plxAdmin->aConf['racine']);
		# mise à jour du fichier des parametres
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}
}
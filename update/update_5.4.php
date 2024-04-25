<?php
/**
 * Classe de mise a jour pour PluXml version 5.4
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_4 extends plxUpdate {

	/*
	 * mise à jour fichier parametres.xml
	 * */
	public function step1() {
		if(empty($this->aConf['images'])) {
			$dest = preg_replace('#^([\w-]+/).*#', '$1medias', PLX_CONFIG_PATH);
			# vérification de l'existence des dossiers médias
			if(!is_dir(PLX_ROOT . $dest)) {
				@mkdir(PLX_ROOT . $dest, 0755, true);
			}
		} else {
			$dest = $this->aConf['images'];
		}

		$new_params = array(
			'custom_admincss_file' => '',
			'medias'	=> $dest,
		);
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys($new_params)) ?></em></li>
<?php
		# on supprime les paramètres obsolètes
		unset($this->aConf['images']);
		unset($this->aConf['documents']);

		# nouveaux paramètres
		return $this->updateParameters($new_params);
	}

}

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
?>
			<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php
		# vérification de l'existence des dossiers médias
		$folder = PLX_ROOT . 'data/medias';
		if(!is_dir($folder)) {
			@mkdir($folder, 0755, true);
		}
		# on supprime les paramètres obsolètes
		unset($this->plxAdmin->aConf['images']);
		unset($this->plxAdmin->aConf['documents']);

		# nouveaux paramètres
		return $this->updateParameters(array(
			'custom_admincss_file' => '',
			'medias' => !empty($this->plxAdmin->aConf['images']) ? $this->plxAdmin->aConf['images'] : 'data/medias/',
		));
	}

}


<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.3
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_3 extends plxUpdate {
	const NEW_PARAMS = array(
		'images_l'	=> 800,
		'images_h'	=> 600,
	);

	/*
	 * mise à jour fichier parametres.xml
	 * */
	public function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		# mise à jour du fichier des parametres
		return $this->updateParameters(self::NEW_PARAMS);
	}
}

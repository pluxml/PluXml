<?php

/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author Pedro "P3ter" CADETE
 **/
class update_5_8_6 extends plxUpdate {
	const NEW_PARAMS = array(
		'enable_rss_comment' => 1,
	);

	/*
	 * mise à jour fichier parametres.xml (récupération du mot de passe)
	 * */
	public function step1()
	{
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		$this->updateParameters();

		return true;
	}
}

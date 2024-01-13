<?php

/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author Pedro "P3ter" CADETE
 **/
class update_5_8_6 extends plxUpdate
{

	# mise à jour fichier parametres.xml (récupération du mot de passe)
	public function step1()
	{
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php

		$new_parameters = array();
		$new_parameters['enable_rss_comment'] = '1';
		$this->updateParameters($new_parameters);

		return true;
	}
}

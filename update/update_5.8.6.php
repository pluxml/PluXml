<?php

/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author Pedro "P3ter" CADETE
 **/
class update_5_8_6 extends plxUpdate
{

	# Flux RSS pour les commentaires
	public function step1() {
?>
			<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php

		return $this->updateParameters(array(
            'enable_rss_comment' => '1',
        ));
	}
}


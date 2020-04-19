<?php

/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
class update_5_8_3 extends plxUpdate
{

    // mise à jour fichier parametres.xml
    public function step1()
    {
        echo L_UPDATE_UPDATE_PARAMETERS_FILE . "<br />";

        $new_parameters['cleanurl'] = '0';
        $this->updateParameters($new_parameters);

        return true;
    }
}
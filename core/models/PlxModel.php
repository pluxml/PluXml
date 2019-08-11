<?php
/**
 * Common models functions
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace models;

class PlxModel {

    private $_plxConfig = array(); # Objet PlxConfigModel

    public function __construct() {
        $this->setPlxConfig();
    }

    /**
     * Get $_plxConfig an array with the user configuration
     * @return array|\models\PlxConfigModel
     */
    public function getPlxConfig() {
        return $this->_plxConfig;
    }

    /**
     * Set $_plxConfig with PlxConfigModel class
     * @return \models\PlxConfigModel
     */
    private function setPlxConfig() {
        return $this->_plxConfig = new PlxConfigModel();
    }
    
    
}
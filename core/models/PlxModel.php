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
     * @author Pedro "P3ter" CADETE
     */
    public function getPlxConfig() {
        return $this->_plxConfig;
    }

    /**
     * Set $_plxConfig with PlxConfigModel class
     * @return \models\PlxConfigModel
     * @author Pedro "P3ter" CADETE
     */
    private function setPlxConfig() {
        return $this->_plxConfig = PlxConfigModel::getInstance();
    }

    /**
     * load lang file and create globals constants
     * @return \models\PlxConfigModel
     */
    public function loadLang($filename) {
	if(file_exists($filename)) {
		$LANG = array();
		include_once $filename;
		foreach($LANG as $key => $value) {
			if(!defined($key)) define($key,$value);
		}
	}
    }
}

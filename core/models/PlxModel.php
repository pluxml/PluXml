<?php
/**
 * Common models functions
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace models;

class PlxModel {

    private $_plxConfig = array(); # Objet PlxConfigModel
    private $_plxMicroTime = ''; # UNIX timestamp with microsecondes

    public function __construct() {
        $this->setPlxConfig();
        $this->setPlxMicrotime();
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
     * Get $_plxMicroTime 
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function getPlxMicrotime() {
        return $this->_plxMicroTime;
    }

    /**
     * Set $_plxConfig with PlxConfigModel class
     * @return \models\PlxConfigModel
     * @author Pedro "P3ter" CADETE
     */
    private function setPlxConfig() {
        $this->_plxConfig = PlxConfigModel::getInstance();
        return;
    }

    /**
     * Set $_plxMicroTime a UNIX timestamp with microsecondes
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function setPlxMicrotime() {
        $t = explode(' ',microtime());
        $this->_plxMicroTime = $t[0]+$t[1];
        return;
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

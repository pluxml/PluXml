<?php
/**
 * Authentification to admin panel
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/
namespace models;

class PlxConfigModel {
    
    const PLX_CONFIG_INI_FILE = 'config.ini';
    
    private $_configIniFile = self::PLX_CONFIG_INI_FILE;
    private $_configIniArray = array(); //from PLX_CONFIG_INI_FILE parsing
    
    public function __construct() {
        $this->setConfigIniArray();
    }

    public function getConfigIniFile() {
        return $this->_configIniFile;
    }

    public function getConfigIniArray() {
        return $this->_configIniArray;
    }
    
    public function getConfig($key){
        //TODO utiliser $this->_configIniArray pour rechercher le clé de config passée en paramètre
    }

    private function setConfigIniArray() {
        return $this->_configIniArray = parse_ini_file($this->_configIniFile);
    }

}

?>
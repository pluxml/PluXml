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
    private $_configIni = array(); //from PLX_CONFIG_INI_FILE parsing
    
    public function __construct() {
        $this->setConfigIni();
    }

    public function getConfigIniFile() {
        return $this->_configIniFile;
    }

    public function getConfigIni(string $key) {
        return $this->_configIni[$key];
    }
    
    private function setConfigIni() {
        return $this->_configIni = parse_ini_file($this->getConfigIniFile());
    }

}

?>
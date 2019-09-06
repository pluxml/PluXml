<?php
/**
 * Common models functions
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace models;

class PlxModel {
    
    const PLX_CORE_DIR = 'core/';
    const PLX_CORE_LANG_DIR = 'core/lang/';
    const PLX_VIEWS_COMMON_DIR = 'views/common/';
    const PLX_VIEWS_LAYOUTS_DIR = 'views/layouts/';
    const PLX_VIEWS_SCRIPTS_DIR = 'views/scripts/';
    const PLX_DEFAULT_CORE_LANG = 'en';

    private $_coreDir = self::PLX_CORE_DIR;
    private $_coreLangDir = self::PLX_CORE_LANG_DIR;
    private $_viewsCommonDir = self::PLX_CORE_DIR . self::PLX_VIEWS_COMMON_DIR;
    private $_viewsLayoutDir = self::PLX_CORE_DIR . self::PLX_VIEWS_LAYOUTS_DIR;
    private $_viewsScriptsDir = self::PLX_CORE_DIR . self::PLX_VIEWS_SCRIPTS_DIR;
    private $_coreLang = self::PLX_DEFAULT_CORE_LANG;
    
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
     * Get $_coreDir
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function getCoreDir(){
        return $this->_coreDir;
    }

    /**
     * Get $_viewsCommonDir
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function getViewsCommonDir(){
        return $this->_viewsCommonDir;
    }

    /**
     * Get $_viewsLayoutDir
     * @return string
     */
    public function getViewsLayoutDir(){
        return $this->_viewsLayoutDir;
    }

    /**
     * Get $_viewsScriptsDir
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function getViewsScriptsDir(){
        return $this->_viewsScriptsDir;
    }
    
    /**
     * Get $_coreLangDir
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function getCoreLangDir() {
        return $this->_coreLangDir;
    }
    
    /**
     * Get $_coreLang
     * @return string        // debug mode activation
     * @author Pedro "P3ter" CADETE
     */
    public function getCoreLang() {
        return $this->_coreLang;
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
    private function setPlxMicrotime() {
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

	/**
	 * set$_coreLang
	 * @return string
	 * @author Pedro "P3ter" CADETE
	 */
    public function setCoreLang($lang) {
        if (!empty($lang))
            $this->_coreLang = $lang;
            return;
    }
}

<?php

/**
 * IndexController requirements to all controllers
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace controllers;

use models\PlxConfigModel;
use models\PlxMotorModel;
use models\PlxShowModel;

class IndexController {

    const PLX_CORE_DIR = 'core/';
    const PLX_CORE_LANG_DIR = 'core/lang/';
    const PLX_VIEWS_COMMON_DIR = 'views/common/';
    const PLX_VIEWS_LAYOUTS_DIR = 'views/layouts/';
    const PLX_VIEWS_SCRIPTS_DIR = 'views/scripts/';
    const PLX_DEFAULT_THEME_DIR = 'themes/default/';

    private $_coreDir = self::PLX_CORE_DIR;
    private $_coreLangDir = self::PLX_CORE_LANG_DIR;
    private $_viewsCommonDir = self::PLX_CORE_DIR . self::PLX_VIEWS_COMMON_DIR;
    private $_viewsLayoutDir = self::PLX_CORE_DIR . self::PLX_VIEWS_LAYOUTS_DIR;
    private $_viewsScriptsDir = self::PLX_CORE_DIR . self::PLX_VIEWS_SCRIPTS_DIR;
    private $_authPage = false;
    private $_themeDir = 'themes/default/'; # "default" theme by default
    private $_coreLang = 'en'; # english by default

    private $_config; # new PlxConfigModel
    private $_plxMotor; # new PlxMotorModel
    private $_plxShow; # new PlxShowModel

    public function __construct(){
        $this->setConfig();
        $this->setCoreLang();
        $this->setThemeDir();
        $this->setPlxMotor();
        $this->setPlxShow();

        // debug mode activation
        if($this->getConfig()->getConfigIni('PLX_DEBUG'))
            error_reporting(E_ERROR | E_WARNING | E_PARSE);

        // Loading langs files
        $this->_plxMotor->loadLang($this->getCoreLangDir() . $this->getCoreLang() . '/admin.php');
        $this->_plxMotor->loadLang($this->getCoreLangDir() . $this->getCoreLang() . '/core.php');

        // Checking PluXml installation before continue
        if(!is_file($this->getConfig()->getConfigIni('XMLFILE_CONFIGURATION'))) {
            header('Location: install');
    	    exit;
    	}

    	// Checking PluXml version in core/models/config.ini and data/configuration.xml
    	if($this->getConfig()->getConfigIni('PLX_VERSION') != $this->getConfig()->getConfiguration('version')) {
    	    header('Location: update/index.php');
    	    exit;
    	}
    	
    	if($this->_authPage !== true){ # si on est pas sur la page de login
    	    // Test sur le domaine et sur l'identification
    	    if((isset($_SESSION['domain']) AND $_SESSION['domain']!=$session_domain) OR (!isset($_SESSION['user']) OR $_SESSION['user']=='')){
    	        header('Location: index.php?p='.htmlentities($_SERVER['REQUEST_URI']));
    	        exit;
    	    }
    	}
    	
    	// actions requirements
    	$this->getPlxMotor()->prechauffage();
    	$this->getPlxMotor()->demarrage();
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
     * Get $_authPage
     * @return boolean
     * @author Pedro "P3ter" CADETE
     */
    public function getAuthPage() {
        return $this->_authPage;
    }

    /**
     * Get $_config
     * @return array
     * @author Pedro "P3ter" CADETE
     */
    
    public function getConfig() {
        return $this->_config;
    }
    
    /**
     * Get $_themeDir
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function getThemeDir() {
        return $this->_themeDir;
    }
    
    /**
     * Get $_plxMotor
     * @return \models\PlxMotorModel
     * @author Pedro "P3ter" CADETE
     */
    public function getPlxMotor() {
        return $this->_plxMotor;
    }
    
    public function getPlxShow() {
        return $this->_plxShow;
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
     * Set $_authPage
     * Used for identify the authentification page to block PluXml backoffice access
     * @param $value boolean    true if the current page is the authentification page
     * @return boolean
     * @author Pedro "P3ter" CADETE
     */
    public function setAuthPage($value) {
        $this->_authPage = $value;
        return;
    }

    /**
     * config.ini parsing
     * @return \models\PlxConfigModel
     * @author Pedro "P3ter" CADETE
     */
    private function setConfig() {
        $this->_config = PlxConfigModel::getInstance();
        return;
    }

    /**
     * Set $_plxMotor
     * @return \models\PlxMotorModel
     * @author Pedro "P3ter" CADETE
     */
    private function setPlxMotor() {
        $this->_plxMotor = PlxMotorModel::getInstance();
        return;
    }

    /**
     * Set $_plxShow
     * @return \models\PlxShowModel
     * @author Pedro "P3ter" CADETE
     */
    private function setPlxShow() {
        $this->_plxShow = PlxShowModel::getInstance();
        return;
    }

    /**
     * set $_themeDir
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    private function setThemeDir(){
        if (!empty($this->_config->getConfiguration('racine_themes') && $this->_config->getConfiguration('style'))) 
            $themeDir = $this->_config->getConfiguration('racine_themes') . $this->_config->getConfiguration('style');
        else
            $themeDir = self::PLX_DEFAULT_THEME_DIR;
        $this->_themeDir = $themeDir . '/';
        return;
    }

    /**
     * set$_coreLang
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    private function setCoreLang() {
        $default_lang = $this->getConfig()->getConfiguration('default_lang');
        if (!empty($default_lang))
            $this->_coreLang = $this->getConfig()->getConfiguration('default_lang');
        return;
    }
}
?>

<?php

/**
 * Authentification to admin panel
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

use models\PlxConfigModel;

class IndexController {

    const PLX_CORE_DIR = 'core/';
    const PLX_VIEWS_COMMON_DIR = 'views/common/';
    const PLX_VIEWS_LAYOUTS_DIR = 'views/layouts/';
    const PLX_VIEWS_SCRIPTS_DIR = 'views/scripts/';
    const PLX_DEFAULT_THEME_DIR = 'themes/default/';
    
    private $_coreDir = self::PLX_CORE_DIR;
    private $_viewsCommonDir = self::PLX_CORE_DIR . self::PLX_VIEWS_COMMON_DIR;
    private $_viewsLayoutDir = self::PLX_CORE_DIR . self::PLX_VIEWS_LAYOUTS_DIR;
    private $_viewsScriptsDir = self::PLX_CORE_DIR . self::PLX_VIEWS_SCRIPTS_DIR;
    private $_authPage = false;
    private $_themeDir = '';

    private $_config; # new PlxConfigModel
    
    public function __construct(){
        $this->setConfig();

        // Checking PluXml installation before continue
        if(!is_file($this->getConfig()->getConfigIni('XMLFILE_PARAMETERS'))) {
            printf(' <br>true <br>');
            printf('config : '.$this->_coreDir . $this->getConfig()->getConfigIni('XMLFILE_PARAMETERS'));
            header('Location: ' . $this->_coreDir . 'install');
    	    exit;
    	}
    	
    	if($this->_authPage !== true){ # si on est pas sur la page de login
    	    # Test sur le domaine et sur l'identification
    	    if((isset($_SESSION['domain']) AND $_SESSION['domain']!=$session_domain) OR (!isset($_SESSION['user']) OR $_SESSION['user']=='')){
    	        header('Location: index.php?p='.htmlentities($_SERVER['REQUEST_URI']));
    	        exit;
    	    }
    	}
    	
    	$this->setThemeDir();
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
    
    public function getThemeDir() {
        return $this->_themeDir;
    }
    
    /**
     * Set $_authPage
     * Used for identify the authentification page to block PluXml backoffice access
     * @param $value boolean    true if the current page is the authentification page
     * @return boolean
     * @author Pedro "P3ter" CADETE
     */
    public function setAuthPage($value) {
        return $this->_authPage = $value;
    }

    /**
     * config.ini parsing
     * @return \models\PlxConfigModel
     * @author Pedro "P3ter" CADETE
     */
    private function setConfig() {
        return $this->_config = new PlxConfigModel();
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
        return $this->_themeDir = $themeDir;
    }
}
?>

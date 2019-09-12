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
    
    const PLX_DEFAULT_THEME_DIR = 'themes/default/';

    private $_userThemePath = self::PLX_DEFAULT_THEME_DIR;
    private $_authPage = false;

    private $_config; # new PlxConfigModel
    private $_plxMotor; # new PlxMotorModel
    private $_plxShow; # new PlxShowModel

    public function __construct(){
        $this->setConfig();
        $this->setUserThemePath();
        $this->setPlxMotor();
        $this->setPlxShow();

        // debug mode activation
        if($this->getConfig()->getConfigIni('PLX_DEBUG'))
            error_reporting(E_ERROR | E_WARNING | E_PARSE);

        // Checking PluXml installation before continue
        $configurationFile = $this->getConfig()->getConfigIni('XMLFILE_CONFIGURATION');
        $configurationFileOldPluxmlVersion = 'data/configuration/parametres.xml';
        if(!is_file($configurationFile) AND !is_file($configurationFileOldPluxmlVersion)) {
            if (strpos('/installation', $_SERVER['REQUEST_URI']) != 0) { # prevent from infinite redirections
                header('Location: /installation');
                exit;
            }
    	}
    	// If installation already done, checking PluXml version in core/models/config.ini and data/configuration.xml
    	else if($this->getConfig()->getConfigIni('PLX_VERSION') != $this->getConfig()->getConfiguration('version')) {
    	    if ($_SERVER['REQUEST_URI'] != '/update') { # prevent from infinite redirections
    	       header('Location: /update');
    	       exit;
    	    }
    	}

    	if($this->getAuthPage() !== true){ # si on est pas sur la page de login
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
     * Get $_userThemePath
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    public function getUserThemePath() {
        return $this->_userThemePath;
    }

    /**
     * Get $_plxMotor
     * @return \models\PlxMotorModel
     * @author Pedro "P3ter" CADETE
     */
    public function getPlxMotor() {
        return $this->_plxMotor;
    }

    /**
     * Get $_plxShow
     * @return \models\PlxShowModel
     */
    public function getPlxShow() {
        return $this->_plxShow;
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
     * set $_userThemePath
     * @return string
     * @author Pedro "P3ter" CADETE
     */
    private function setUserThemePath(){
        $themesDirectory = $this->getConfig()->getConfiguration('racine_theme');
        $themeName = $this->getConfig()->getConfiguration('style'); 
        if (!empty($themesDirectory && $themeName))
            $this->_userThemePath = $themesDirectory . $themeName . '/';
        return;
    }
}
?>

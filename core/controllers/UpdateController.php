<?php

/**
 * UpdateController PluXml update version
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace controllers;

use models\PlxUpdaterModel;

class UpdateController extends AdminController {
    
    private $_plxUpdater; # new PlxUpdaterModel

    public function __construct() {
        $plxVersions = $this->getPlxUpdater()->getVersionsIniValues();
        $this->setPlxUpdater($plxVersions);
        parent::__construct();
    }

    /**
     * Get $_plxUpdater
     * @return \models\PlxUpdaterModel
     */
    public function getPlxUpdater() {
        return $this->_plxUpdater;
    }

    /**
     * Set $_plxUpdater
     * @param string $versions
     */
    private function setPlxUpdater($versions) {
        $this->_plxUpdater = new PlxUpdaterModel($versions);
        return;
    }

    /**
     * Index action default view call
     */
    public function indexAction() {
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxUpdater = $this->getPlxUpdater();
        $lang = $this->getCoreLang();
        
        // Checking PluXml installation
        if(!file_exists($this->getConfig()->getConfigIni('XMLFILE_PARAMETERS'))) {
            header('Location: install');
            exit;
        }
        
        // Checking PHP version (7.3 is required)
        if(version_compare(PHP_VERSION, '7.3.0', '<')){
            header('Content-Type: text/plain charset=UTF-8');
            echo utf8_decode(L_WRONG_PHP_VERSION);
            exit;
        }
        
        # Echappement des caractères
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $plxUtils->unSlash($_POST);
        }
        
        $plxToken->cleanHeaders();
        $plxToken->validateFormToken($_POST);
        require_once $this->getViewsScriptsDir() . 'updateView.php';
    }
}
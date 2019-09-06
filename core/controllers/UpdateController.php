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
        parent::__construct();
        $plxVersions = $this->getConfig()->getVersionsIniArray();
        $this->setPlxUpdater($plxVersions);
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

        $plxUtils->cleanHeaders();
        $plxToken->validateFormToken($_POST);
        require_once $this->getViewsScriptsDir() . 'updateView.php';
    }
}
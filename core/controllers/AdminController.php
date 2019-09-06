<?php

/**
 * Authentification to admin panel
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

use models\PlxAdminModel;
use models\PlxUtilsModel;
use models\PlxTokenModel;

class AdminController extends IndexController {

    private $_plxAdmin; # new PlxAdminModel
    private $_plxUtils; # new PlxAdminModel
    private $_plxToken; # new PlxAdminModel

    public function __construct(){
        $this->setPlxAdmin();
        $this->setPlxUtils();
        $this->setPlxToken();

        // This page don't need user authentification
        $this->setAuthPage(true);

        parent::__construct();
    }
    
    /**
     * Get $_plxAdmin
     * @return \models\PlxAdminModel
     * @author Pedro "P3ter" CADETE
     */
    public function getPlxAdmin() {
        return $this->_plxAdmin;
    }

    /**
     * Set $_plxAdmin
     * @author Pedro "P3ter" CADETE
     */
    private function setPlxAdmin() {
        $this->_plxAdmin = PlxAdminModel::getInstance();
        return;
    }
    
    /**
     * Get $_plxUtils
     * @return \models\PlxUtilsModel
     * @author Pedro "P3ter" CADETE
     */
    public function getPlxUtils() {
        return $this->_plxUtils;
    }

    /**
     * Set $_plxUtils
     * @author Pedro "P3ter" CADETE
     */
    private function setPlxUtils() {
        $this->_plxUtils = new PlxUtilsModel;
        return;
    }

    /**
     * Get $_plxToken
     * @return \models\PlxTokenModel
     * @author Pedro "P3ter" CADETE
     */
    public function getPlxToken() {
        return $this->_plxToken;
    }

    /**
     * Set $_plxToken
     * @author Pedro "P3ter" CADETE
     */
    private function setPlxToken() {
        $this->_plxToken = new PlxTokenModel;
        return;
    }

    /**
     * Index action redirect to AuthController
     * @author Pedro "P3ter" CADETE
     */
    public function indexAction() {
        header('Location: auth');
    }

    /**
     * Display admin dasboard action
     * @author Pedro "P3ter" CADETE
     */
    public function dashboardAction() {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxMotor()->getViewsLayoutDir();

        # Display the view
        PlxUtilsModel::cleanHeaders();
        require_once $this->getPlxMotor()->getViewsScriptsDir() . 'dashboardView.php';
    }
}
<?php

/**
 * Authentification to admin panel
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

use models\PlxUtilsModel;
use models\PlxViewAdminDataModel;
use models\PlxAdminModel;
use models\PlxTokenModel;

class AdminController extends IndexController {

    private $_plxAdmin; # new PlxAdminModel
    private $_plxUtils; # new PlxUtilsModel
    private $_plxToken; # new PlxTokenModel
    private $_plxViewData; # new PlxViewAdminDataModel

    public function __construct(){
        $this->setPlxAdmin();
        $this->setPlxUtils();
        $this->setPlxToken();
        
        // Set object data for views
        $this->setViewAdminData();

        // This page don't need user authentification
        $this->setAuthPage(true);

        parent::__construct();
    }

    public function getViewData() {
        return $this->_plxViewData;
    }
    
    public function getPlxAdmin() {
        return $this->_plxAdmin;
    }
    
    public function getPlxUtils() {
        return $this->_plxUtils;
    }
    
    public function getPlxToken() {
        return $this->_plxToken;
    }
    
    private function setPlxAdmin() {
        $this->_plxAdmin = PlxAdminModel::getInstance();
    }
    
    private function setPlxUtils() {
        $this->_plxUtils = new PlxUtilsModel;
    }
    
    private function setPlxToken() {
        $this->_plxToken = new PlxTokenModel();
    }

    private function setViewAdminData() {
        $this->_plxViewData = new PlxViewAdminDataModel();
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
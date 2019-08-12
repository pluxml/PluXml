<?php

/**
 * Homepage blog or static page
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

use models\PlxMotorModel;
use models\PlxShowModel;

class HomepageController extends IndexController {

    public function __construct(){
        // This page don't need user authentification
        $this->setAuthPage(true);
        parent::__construct();
    }

    /**
     * Index action call the view from the selected theme
     * @author Pedro "P3ter" CADETE
     */
    public function indexAction() {
        $plxMotor = PlxMotorModel::getInstance();
        $plxMotor->prechauffage();
        $plxMotor->demarrage();
        
        //TODO need a class PlxLangModel
        $lang = $this->getConfig()->getConfiguration('default_lang');
        
        $plxShow = PlxShowModel::getInstance();
        
        require_once $this->getThemeDir() . 'home.php';
    }
}
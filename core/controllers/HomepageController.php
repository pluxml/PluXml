<?php

/**
 * Homepage blog or static page
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

use models\PlxShowModel;
use models\PlxMotorModel;

class HomepageController extends IndexController {

    public function __construct(){
        // This page don't need user authentification
        $this->setAuthPage(true);
        parent::__construct();

        $plxMotor = PlxMotorModel::getInstance();
        $plxMotor->prechauffage();
        $plxMotor->demarrage();
        
        $lang = $this->getConfig()->getConfiguration('default_lang');

        $plxShow = PlxShowModel::getInstance();
    }

    /**
     * Index action call the view from the selected theme
     * @author Pedro "P3ter" CADETE
     */
    public function indexAction() {
        require_once $this->getThemeDir() . 'home.php';
    }
}
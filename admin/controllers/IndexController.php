<?php

/**
 * Authentification to admin panel
 *
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

class AdminController extends IndexController {

    const PLX_ROOT_DIR = '../';
    const PLX_ADMIN_DIR = PLX_ROOT_DIR . 'admin/';
    const PLX_VIEWS_COMMON_DIR = PLX_ADMIN_DIR . 'views/common/';
    const PLX_VIEWS_LAYOUTS_DIR = PLX_ADMIN_DIR . 'views/layouts/';
    const PLX_VIEWS_SCRIPTS_DIR = PLX_ADMIN_DIR . 'views/common/';
    
    private $_rootDir = Self::PLX_ROOT_DIR;
    private $_adminDir = Self::PLX_ADMIN_DIR;
    private $_viewsCommonDir = Self::PLX_VIEWS_COMMON_DIR;
    private $_viewsLayoutDir = Self::PLX_VIEWS_LAYOUTS_DIR;
    private $_viewsScriptsDir = Self::PLX_VIEWS_SCRIPTS_DIR;
    
    public function __construct(){
	# Checking PluXml installation before continue
	if(!file_exists(path('XMLFILE_PARAMETERS'))) {
	    header('Location: ../install.php');
	    exit;
	}

	session_start();
    }
}
?>

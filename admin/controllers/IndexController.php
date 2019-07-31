<?php

/**
 * Authentification to admin panel
 *
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

class IndexController {

    const PLX_ROOT_DIR = '../';
    const PLX_ADMIN_DIR = 'admin/';
    const PLX_VIEWS_COMMON_DIR = 'admin/views/common/';
    const PLX_VIEWS_LAYOUTS_DIR = 'admin/views/layouts/';
    const PLX_VIEWS_SCRIPTS_DIR = 'admin/views/scripts/';
    
    public $_rootDir = Self::PLX_ROOT_DIR;
    public $_adminDir = Self::PLX_ROOT_DIR . Self::PLX_ADMIN_DIR;
    public $_viewsCommonDir = Self::PLX_ROOT_DIR . Self::PLX_VIEWS_COMMON_DIR;
    public $_viewsLayoutDir = Self::PLX_ROOT_DIR . Self::PLX_VIEWS_LAYOUTS_DIR;
    public $_viewsScriptsDir = Self::PLX_ROOT_DIR . Self::PLX_VIEWS_SCRIPTS_DIR;
    
    public function __construct(){
    	# Checking PluXml installation before continue
    	if(!file_exists(path('XMLFILE_PARAMETERS'))) {
    	    header('Location: ' . $this->_adminDir . 'install.php');
    	    exit;
    	}
    	session_start();
    }
}
?>

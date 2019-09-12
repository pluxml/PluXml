<?php

/**
 * PlxViewAdminDataModel array data for views
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace models;

class PlxViewAdminDataModel extends PlxAdminModel {

    private $_viewAdminData = array(); # an array containing datas for views

    public function __construct() {
        parent::__construct();
        $this->viewAdminDataPopulator(null, null);
    }

    public function getViewAdminData() {
       return $this->_viewAdminData; 
    }

    private function setViewAdminData($key, $value) {
        $this->_viewAdminData[$key] = $value;
        return;
    }

    public function viewAdminDataPopulator($key, $value) {
        $this->setViewAdminData('plxAdmin', PlxAdminModel::getInstance());
        $this->setViewAdminData('plxUtils', new PlxUtilsModel);
        $this->setViewAdminData('plxToken', new PlxTokenModel());
        $this->setViewAdminData('timezones', PlxTimezonesModel::timezones());
        $this->setViewAdminData('layoutDir', $this->getViewsLayoutDir());
        $this->setViewAdminData('lang', $this->getCoreLang());
        $this->setViewAdminData('charset', $this->getPlxConfig()->getConfigIni('PLX_CHARSET'));
        $this->setViewAdminData('version', $this->getPlxConfig()->getConfigIni('PLX_VERSION'));
    }
}
?>
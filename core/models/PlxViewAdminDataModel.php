<?php

/**
 * PlxViewAdminDataModel array data for views
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace models;

class PlxViewAdminDataModel extends PlxAdminModel {

    private $_viewDataArray = array(); # an array containing datas for views

    public function __construct() {
        parent::__construct();
        $this->viewAdminDataPopulator(null, null);
    }

    public function getViewDataArray() {
       return $this->_viewDataArray; 
    }

    public function setViewDataArray($key, $value) {
        $this->_viewDataArray[$key] = $value;
        return;
    }

    public function viewAdminDataPopulator($key, $value) {
        $this->setViewDataArray('plxAdmin', PlxAdminModel::getInstance());
        $this->setViewDataArray('plxUtils', new PlxUtilsModel);
        $this->setViewDataArray('plxToken', new PlxTokenModel());
        $this->setViewDataArray('timezones', PlxTimezonesModel::timezones());
        $this->setViewDataArray('layoutDir', $this->getViewsLayoutDir());
        $this->setViewDataArray('lang', $this->getCoreLang());
        $this->setViewDataArray('charset', $this->getPlxConfig()->getConfigIni('PLX_CHARSET'));
        $this->setViewDataArray('version', $this->getPlxConfig()->getConfigIni('PLX_VERSION'));
    }
}
?>
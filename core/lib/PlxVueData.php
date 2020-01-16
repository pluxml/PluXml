<?php

/**
 * PlxVueData class is in charge of Vue.js datas
 * @author	Pedro "P3ter" CADETE
 **/
namespace Pluxml;

class PlxVueData {

	private $_datas = [];
	private $_jsonDatas = '';

	public function __construct($datas = null) {
		if (!empty($datas)) {
			$this->setDatas($datas);
			$this->setJsonDatas();
		}
	}

	public function getDatas() {
		return $this->_datas;
	}

	private function setDatas($array) {
		$this->_datas = $array;
	}

	public function getJsonDatas() {
		return $this->_jsonDatas;
	}

	private function setJsonDatas() {
		$this->_jsonDatas = json_encode($this->getDatas());
	}
}

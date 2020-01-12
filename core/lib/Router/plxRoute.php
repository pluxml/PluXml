<?php

/**
 * Classe plxRoute URL route
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace Pluxml\Router;

class plxRoute {
	
	private $path;
	private $params = [];
	private $callable;
	private $matches = [];

	public function __construct($path, $callable) {
		$this->setPath($path);
		$this->setCallable($callable);
	}

	public function with($param, $regex) {
		$this->setParams($param, $regex);
		return $this;
	}

	public function match($url) {
		$result = false;
		$matches = '';
		$url = trim($url, '/');
		$path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->getPath());
		$regex = "#^$path$#i";
		if(preg_match($regex, $url, $matches)) {
			array_shift($matches);
			$this->setMatches($matches);
			$result = true;
		}
		return $result;
	}

	private function paramMatch($match) {
		if (isset($this->getParams($match[1]))) {
			return '('.$this->getParams($match[1]).')';
		}
		return '([^/]+)';
	}

	public function call() {
		var_dump($this->getMatches());
		return call_user_func_array($this->getCallable(), $this->getMatches());
	}

	/**
	 * @return string
	 */
	private function getPath() {
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	private function setPath($path) {
		$this->path = trim($path, '/');
	}

	/**
	 * @return array
	 */
	private function getParams($match = null) {
		$params = $this->params;
		if (!empty($match)) {
			$params = $this->params[$match];
		}
		return $params;
	}
	
	/**
	 * @param string $params
	 * @param string $regex
	 */
	private function setParams($param, $regex) {
		$this->params[$param] = $regex;
	}
	
	/**
	 * @return string
	 */
	private function getCallable() {
		return $this->callable;
	}

	/**
	 * @param string $callable
	 */
	private function setCallable($callable) {
		$this->callable = $callable;
	}

	/**
	 * @return string
	 */
	private function getMatches() {
		return $this->matches;
	}

	/**
	 * @param string $matches
	 */
	private function setMatches($matches) {
		$this->matches = $matches;
	}
}
?>
<?php

/**
 * plxRoute class define a route for the PluXml router
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace Pluxml\Router;

use Pluxml;

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
	
	public function getRouteUrl($params) {
		$path = $this->getPath();
		foreach ($params as $key => $value) {
			$path = str_replace(":$key", $value, $path);
		}
		return $path;
	}

	public function call() {
		if (is_string($this->getCallable())) {
			$params = explode("#", $this->getCallable());
			$controller = "Pluxml\\Controllers\\" . $params[0] . "Controller";
			$controller = new $controller();
			return call_user_func_array([$controller, $params[1]], $this->getMatches());
		} else {
			return call_user_func_array($this->getCallable(), $this->getMatches());
		}
	}

	private function paramMatch($match) {
		if (!empty($this->getParams($match[1]))) {
			return '('.$this->getParams($match[1]).')';
		}
		return '([^/]+)';
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
	private function getParams($param = null) {
		$params = $this->params;
		if (!empty($param) AND isset($this->params[$param])) {
			$params = $this->params[$param];
		}
		return $params;
	}
	
	/**
	 * @param string $params
	 * @param string $regex
	 */
	private function setParams($param, $regex) {
		$this->params[$param] = str_replace('(', '(?:', $regex);
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
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
	private $callable;
	private $matches;
	
	public function __construct($path, $callable) {
		$this->setPath($path);
		$this->setCallable($callable);
	}
	
	public function match($url) {
		$result = false;
		$matches = '';

		$url = trim($url, '/');
		$path = preg_replace('#:[\w]+#', '([^/]+)', $this->getPath());
		$regex = "#^$path$#i";
		if(preg_match($regex, $url, $matches)) {
			array_shift($matches);
			$this->setMatches($matches);
			$result = true;
			var_dump($matches);
		}

		return $result;
	}

	public function call() {
		return call_user_func_array($this->getCallable(), $this->getMatches());
	}
	
	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = trim($path, '/');
	}

	/**
	 * @return string
	 */
	public function getCallable() {
		return $this->callable;
	}

	/**
	 * @param string $callable
	 */
	public function setCallable($callable) {
		$this->callable = $callable;
	}
	
	/**
	 * @return string
	 */
	public function getMatches() {
		return $this->matches;
	}
	
	/**
	 * @param string $matches
	 */
	public function setMatches($matches) {
		$this->matches = $matches;
	}
	
}

?>
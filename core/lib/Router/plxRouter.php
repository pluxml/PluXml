<?php

/**
 * Classe plxRouter manage the PluXml URL router
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace Pluxml\Router;

class plxRouter {

	private $url;
	private $routes = [];

	public function __construct($url) {
		$this->setUrl($url);
	}

	public function get($path, $callable) {
		$route = new plxRoute($path, $callable);
		$this->setRoutes('GET', $route);
		return $route;
	}

	public function post($path, $callable) {
		$route = new plxRoute($path, $callable);
		$this->setRoutes('POST', $route);
	}
	
	public function run() {
		if (empty($this->getRoutes($_SERVER['REQUEST_METHOD']))) {
			throw new plxRouterException('REQUEST_METHOD does not exist or missing');
		}
		foreach($this->getRoutes($_SERVER['REQUEST_METHOD']) as $route) {
			if ($route->match($this->getUrl())) {
				return $route->call();
			}
		}
		throw new plxRouterException('No matching routes');
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}
	
	/**
	 * @param	string	$requestMethod	http request method (GET, POST, ...)
	 * @return	array	a plxRoute object list
	 */
	public function getRoutes($requestMethod = null) {
		$routes = $this->routes;
		if (!empty($requestMethod)) {
			$routes = $this->routes[$requestMethod];
		}
		return $routes;
	}
	
	/**
	 * @param string	$requestMethod	http request method (GET, POST, ...)
	 * @param plxRoute	$route			plxRoute object
	 */
	public function setRoutes($requestMethod, $route) {
		$this->routes[$requestMethod][] = $route;
	}
	
}
?>
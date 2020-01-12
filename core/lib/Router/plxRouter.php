<?php

/**
 * plxRouter class manage the PluXml URL router
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace Pluxml\Router;

class plxRouter {

	private $url;
	private $routes = [];
	private $namedRoutes = [];

	public function __construct($url) {
		$this->setUrl($url);
	}

	public function get($path, $callable, $name = null) {
		return $this->addRoute($path, $callable, $name, 'GET');
	}

	public function post($path, $callable, $name = null) {
		return $this->addRoute($path, $callable, $name, 'POST');
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

	public function url($name, $params = []) {
		if(empty($this->getNamedRoutes($name))) {
			throw new plxRouterException('Named route not found');
		}
		return $this->getNamedRoutes($name)->getRouteUrl($params);
	}

	private function addRoute($path, $callable, $name, $method) {
		$route = new plxRoute($path, $callable);
		$this->setRoutes($method, $route);
		if (is_string($callable) && $name === null) {
			$name = $callable;
		}
		if (!empty($name)) {
			$this->setNamedRoutes($name, $route);
		}
		return $route;
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

	/**
	 * @return array
	 */
	public function getNamedRoutes($name = null) {
		$namedRoutes = $this->namedRoutes;
		if (!empty($name) && isset($this->namedRoutes[$name])) {
			$namedRoutes = $this->namedRoutes[$name];
		}
		return $namedRoutes;
	}

	/**
	 * @param array $namedRoutes
	 */
	public function setNamedRoutes($name, $route) {
		$this->namedRoutes[$name] = $route;
	}
}
?>
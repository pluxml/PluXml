<?php

/**
 * Front controller
 * @package PLX
 * @author	Pedro "P3ter" CADETE based on Alejandro GERVASIO work (https://www.sitepoint.com/front-controller-pattern-1/)  
 **/
namespace controllers;

class FrontController
{
    const DEFAULT_CONTROLLER = __NAMESPACE__ . '\\' . 'HomepageController';
    const DEFAULT_ACTION     = 'indexAction';
    const DEFAULT_PARAMS     = array();

    private $_controller     = self::DEFAULT_CONTROLLER;
    private $_action         = self::DEFAULT_ACTION;
    private $_params         = self::DEFAULT_PARAMS;

    public function __construct(array $options = array()) {
        if (empty($options)) {
            $this->parseUri();
        }
        else {
            if (isset($options["controller"])) {
                $this->setController($options["controller"]);
            }
            if (isset($options["action"])) {
                $this->setAction($options["action"]);
            }
            if (isset($options["params"])) {
                $this->setParams($options["params"]);
            }
        }
    }

    public function setController($controller) {
        $controller = __NAMESPACE__ . '\\' . ucfirst(strtolower($controller)) . 'Controller';
        if (!class_exists($controller)) {
            throw new \InvalidArgumentException(
                'The controller ' . $controller . ' has not been defined.');
        }
        $this->_controller = $controller;
        return $this;
    }

    public function setAction($action) {
        $reflector = new \ReflectionClass($this->_controller);
        if (!$reflector->hasMethod($action)) {
            throw new \InvalidArgumentException(
                'The controller action ' . $action . ' has been not defined.');
        }
        $this->_action = $action;
        return $this;
    }

    public function setParams(array $params) {
        $this->_params = $params;
        return $this;
    }

    public function run() {
        call_user_func_array(array(new $this->_controller, $this->_action), $this->_params);
    }
    
    private function parseUri() {
        $path = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), '/');
        $path = preg_replace('[^a-zA-Z0-9]', "", $path);
        list($controller, $action, $params) = explode('/', $path, 3);
        if (!empty($controller) and $controller != 'index.php') {
            $this->setController($controller);
        }
        // compatibility with PluXml 5 and older
        else if (!empty($controller)) {
            $pathPlx5 = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
            switch ($pathPlx5) {
                case strpos($pathPlx5, 'static'):
                    $this->setController('Staticpage');
                    break;
                case strpos($pathPlx5, 'article'):
                    $this->setController('Articlepage');
                    break;
            }
        }
        if (!empty($action)) {
            $this->setAction($action);
        }
        if (!empty($params)) {
            $this->setParams(explode("/", $params));
        }
    }
}

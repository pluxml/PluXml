<?php

/**
 * Autoloader
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace loader;

class Autoloader{

    public function __construct() {}

    public function register(){
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    
    private static function autoload($class){
        $class = str_replace('\\', '/', $class);
        $class = str_replace(__NAMESPACE__, strtolower(__NAMESPACE__), $class);
        require $class.'.php';
    }
}
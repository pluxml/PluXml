<?php

/**
 * Autoloader
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace controllers;

class Autoloader{

    public function register(){
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    private static function autoload($class){
        $class = strtr($class, '\\', '/');
        require __DIR__ . '/core/' . $class.'.php';
    }
}

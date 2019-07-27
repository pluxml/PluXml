<?php

/**
 * Router / Dispatcher
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace admin;

use controllers\FrontController;

require_once __DIR__ . '/autoloader.php';

$autoloader = new Autoloader;
$autoloader->register();

$frontController = new FrontController();
$frontController->run();
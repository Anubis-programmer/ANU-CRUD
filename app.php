<?php

session_start();

define('BASE_PATH', dirname(realpath(__FILE__)) . '/');
define('CORE_PATH', BASE_PATH . 'core/');
define('LOGS_PATH', BASE_PATH . 'Logs/');
define('INDEX', BASE_PATH . '/index.html');

include_once(CORE_PATH . 'config.php');
include_once(CORE_PATH . 'Request.php');
include_once(CORE_PATH . 'Router.php');

$router = new Router(new Request);

$router->get('/', function($request) {
    include_once(INDEX);
});

?>
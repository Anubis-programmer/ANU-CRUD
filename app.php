<?php

session_start();

define('BASE_PATH', dirname(realpath(__FILE__)) . '/');
define('CORE_PATH', BASE_PATH . 'core/');
define('DATA_PATH', BASE_PATH . 'data/');

include_once(CORE_PATH . 'config.php');
include_once(CORE_PATH . 'Request.php');
include_once(CORE_PATH . 'Router.php');

$router = new Router(new Request);

/**
 * $router->get('/my-end-point', function($request) {
 *     http_response_code(200);
 *
 *     return json_encode(["message" => "It works!"]);
 * });
 */

?>

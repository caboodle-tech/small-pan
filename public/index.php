<?php
/**
 * Turn the lights on.
 */

require '../config.php';
require '../app/includes/constants.php';
require '../app/includes/helpers.php';
require '../app/controllers/core/autoloader.php';

$Cookie = new Controller\Core\Cookie();
$Router = new Controller\Core\Router();

$Database = new Module\Core\Database();
$Session  = Module\Core\Session::getInstance();
$User     = new Module\Core\User();

$Router->add('', 'Controller\Core\Route');
$Router->add('test/page', 'Controller\Core\Route');
$Router->add('test/*/:item', 'Controller\Core\Route');

// echo 'REQ URL: ' . $Router->getRequestUrl() . '<br><br>';
// echo 'APP URL: ' . $Router->getAppUrl() . '<br>';
// echo 'REQUEST_METHOD: ' . REQUEST_METHOD . '<br>';
// echo 'RESPONSE_TYPE: ' . RESPONSE_TYPE . '<br>';
// $Router->getRoutingTable(true);
// $Router->route();

// echo '<pre>';
// print_r($_SERVER);
// echo '</pre>';
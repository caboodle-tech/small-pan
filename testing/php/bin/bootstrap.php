<?php
/**
 * Bootstrap all the necessary files for SM/PAN to run otherwise all the
 * tests would fail.
 * 
 * YOU SHOULD NOT NEED TO EDIT THIS FILE.
 */

$sep  = DIRECTORY_SEPARATOR;
$base = dirname(__FILE__);
$root = realpath($base . '/../../../');

/**
 * Simple path fixer that swaps forward slashes (/) in routes to backslash (\)
 * if we are in a windows environment.
 *
 * @param string $path The path to fix.
 * @param string $sep  The proper separator to use on this OS.
 * 
 * @return string The OS correctly path.
 */
function _fixPath(string $path, string $sep) {
    return str_replace('/', $sep, $path);
}

// Pull in the test autoloader first.
require $root . _fixPath('/testing/php/bin/autoloader.php', $sep);

// Pull in all the necessary files. Note that order is important here!
require $root . _fixPath('/testing/php/config.php', $sep);
require $root . _fixPath('/app/includes/constants.php', $sep);
require $root . _fixPath('/app/includes/helpers.php', $sep);
require $root . _fixPath('/app/controllers/core/autoloader.php', $sep);

// Setup globals needed for the Router to work.
$_GET    = ['p' => ''];
$_SERVER = [
    'HTTP_ACCEPT'    => 'text/html',
    'HTTP_HOST'      => 'localhost',
    'PHP_SELF'       => '/public/index.php',
    'REQUEST_METHOD' => 'GET',
    'REQUEST_SCHEME' => 'http',
    'REQUEST_URI'    => '/www/sm-pan/'
];

// Create main controllers.
$Cookie = new Controller\Core\Cookie();
$Router = new Controller\Core\Router();

// Create main Modules.
$Database = new Module\Core\Database();
$Session  = Module\Core\Session::getInstance();
$User     = new Module\Core\User();

// Run setup scripts if any now.
$setup = $root . _fixPath('/testing/php/setup.php', $sep);
if (file_exists($setup)) {
    // phpcs:ignore PEAR.Files.IncludingFile.UseInclude
    require $setup;
}

// Setup basic session information for a signed-in user.
$Session->userId   = 1234;
$Session->loggedIn = true;
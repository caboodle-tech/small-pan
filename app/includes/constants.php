<?php
/**
 * Application specific constants needed by SM/PAN.
 */

define('ROOT', realpath(dirname(__FILE__) . '/../../'));
define('SEP', DIRECTORY_SEPARATOR);

if (!defined('PRODUCTION')) {
    define('PRODUCTION', true);
}
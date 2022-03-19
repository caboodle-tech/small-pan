<?php
/**
 * Testing specific configuration.
 * 
 * WARNING:
 * 
 * Never use your production database credentials here! Database tests are
 * designed to delete data and drop tables in the testing database.
 */

/**
 * Testing database credentials.
 */
define('DB_NAME', 'name');
define('DB_USER', 'user');
define('DB_PASSWORD', 'pass');
define('DB_HOST', 'localhost');

/**
 * Site specific settings.
 */
define('PEPPER', 'BH0GYRZ3Z0PL3K5PQ02C');
define('SESSION_NAME', 'test');
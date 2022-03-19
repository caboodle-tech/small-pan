<?php
/**
 * The SM/PAN test class autoloader.
 */

namespace Test;

class Autoloader {
    
    /**
     * Create the absolute file path to a test class based on its namespace.
     *
     * @param string $class The namespace and name of a class.
     * 
     * @return string The absolute file path to a class.
     */
    public static function getPath(string $class) {
        $class = str_replace('_', '-', strtolower($class));
        $parts = explode('\\', $class);
        $base  = '';
        switch($parts[0]) {
            case 'test':
            case 'tests':
            case 'testing':
                $base = 'testing/php';
                if (isset($parts[2])) {
                    switch($parts[2]) {
                        case 'controller':
                            $parts[2] = 'controllers';
                            break;
                        case 'include':
                            $parts[2] = 'includes';
                            break;
                        case 'module':
                            $parts[2] = 'modules';
                            break;
                    }
                }
                $parts = array_slice($parts, 1);
                break;
            default:
                $base = '';
        }
        return absPath($base . SEP . implode(SEP, $parts)) . '.php';
    }
    
    /**
     * Attempt to autoload a class. This is what should be registered with PHP's
     * spl_autoload_register function.
     * 
     * @param string $class The namespace and name of a class to attempt to load.
     * 
     * @return boolean True when the class was loaded and false otherwise.
     */
    public static function load($class) {
        $path = self::getPath($class);
        if (file_exists($path)) {
            // phpcs:ignore PEAR.Files.IncludingFile.UseInclude
            require $path;
            return true;
        }
        return false;
    }

}

// Go ahead and register the autoloader here. It's clean and out of the way.
spl_autoload_register("Test\Autoloader::load");
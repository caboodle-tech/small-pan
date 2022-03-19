<?php
// phpcs:disable PEAR.Commenting.FileComment.Missing

/**
 * Helper function that takes a relative path and converts it to the system
 * appropriate absolute file path.
 *
 * @param string $str The relative path to a file.
 * 
 * @return void The system appropriate absolute file path.
 */
function absPath($str = '') {
    $prefix = '';
    $root   = ROOT;
    $pos    = strpos($root, ':');
    if ($pos != false && $pos < 4) {
        $prefix = substr($root, 0, $pos + 1);
        $root   = substr($root, $pos + 1);
    }
    $str = $root . SEP . $str;
    $str = str_replace('\\\\', '/', $str);
    $str = str_replace('\\', '/', $str);
    $str = str_replace('///', '/', $str);
    $str = str_replace('//', '/', $str);
    if (strlen($prefix) > 0) {
        if ($str[0] == '/' || $str[0] == '\\') {
            $str = substr($str, 1);
        }
        $ary = explode('/', $prefix . '/' . $str);
    } else {
        $ary = explode('/', $str);
    }
    // realpath is not used here on purpose for security!
    return implode(SEP, $ary);
}
<?php
// phpcs:disable PEAR.Commenting.FileComment.Missing

/**
 * Return the value of a PHP constant or default to a set value.
 *
 * @param string  $name    The name of the constant.
 * @param boolean $default The default value to use if the constant does not exist.
 * 
 * @return void
 */
function getConstant($name, $default = false) {
    if (defined($name)) {
        return constant($name);
    }
    return $default;
}
<?php
// phpcs:disable PEAR.Commenting.FileComment.Missing

/**
 * Simple getter to retrieve the requested response type. Defaults to HTML.
 *
 * @return string The requests method: HTML, JSON, or XML.
 */
function responseType() {
    global $Router;
    return $Router->getRequestMethod();
}

/**
//  * Simple getter to retrieve the request method.
 *
 * @return string The requests method: GET, POST, DELETE, CREATE, etc.
 */
function requestMethod() {
    global $Router;
    return $Router->getResponseType();
}


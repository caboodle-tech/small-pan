<?php
/**
 * The SM/PAN's applications Router.
 */

namespace Controller\Core;

class Router {

    private $appUrl      = '';    
    private $handle404   = null;
    private $initialized = false;
    private $reqUrl      = '';
    private $requestMethod;
    private $responseType;
    private $routes;

    /**
     * Create a new instance of the Router, record the requested page, and other
     * important request headers the application may need.
     */
    public function __construct() {
        $this->rebootSettings(true);
        $this->initialized = true;
    }

    /**
     * Register a new route with the routing table.
     *
     * @param string $route      A URI to treat as a valid route.
     * @param string $controller The controller (including namespace) that will handle this route.
     * 
     * @return void
     */
    public function add($route, $controller) {
        $parts = explode('/', $route);
        // Routing tables are organized by how many parts the route contains.
        $index = 'R' . count($parts);
        if (!isset($this->routes->$index)) {
            $this->routes->$index = [];
        }
        // Push this routes data into the correct routing table.
        array_push(
            $this->routes->$index,
            (object) [
                'regex'      => $this->getRouteRegexp($parts),
                'route'      => $route,
                'parts'      => $parts,
                'controller' => $controller
            ]
        );
    }

    /**
     * Transform an associative array into a simple XML document.
     *
     * @param array   $ary   An associative (key value pair) array to transform into an XML document.
     * @param boolean $start Should the starting tag be included; defaults to TRUE.
     * 
     * @return void
     */
    public function arrayToXml($ary, bool $start =  true) {
        $xml = '';
        if ($start) {
            $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        }
        foreach ($ary as $key => $val) {
            if (is_numeric($key)) {
                $xml .= $this->arrayToXml($ary[$key], false);
                continue;
            }
            if (is_array($val) || is_object($val)) {
                $xml .= '<' . $key . '>';
                $xml .= $this->arrayToXml($val, false);
                $xml .= '</' . $key . '>';
            } else if (is_numeric($val)) {
                $xml .= '<' . $key . ' number="true">' . $val . '</' . $key . '>';
            } else if (is_bool($val)) {
                $xml .= '<' . $key . ' boolean="true">' . $val . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            }
        }
        return $xml;
    }

    /**
     * Getter to retrieve the applications URL; everything from ?p=...
     *
     * @return string The application specific URL.
     */
    public function getAppUrl() {
        return $this->appUrl;
    }

    /**
     * Get the Routers response type: HTML, JSON, or XML.
     *
     * @return string The type requested and supported by SM/PAN: HTML, JSON, or XML.
     */
    public function getResponseType() {
        return $this->responseType;
    }


    /**
     * Get the Routers request method; any valid HTTP request method.
     * 
     * @return string The request method; GET, POST, DELETE, CREATE, ect.
     */
    public function getRequestMethod() {
        return $this->requestMethod;
    }

    /**
     * Getter to retrieve the originally requested URL; full original request.
     *
     * @return string The full originally requested URL.
     */
    public function getRequestUrl() {
        return $this->reqUrl;
    }

    /**
     * Build a object with important route information:
     * 
     * Object->appUrl  string   The app specific URI not accounting for the fully requested URI.
     * Object->reqUrl  string   The original full requested URI.
     * Object->trimUrl string   A shortened `appUrl` that removes any parameter parts from the URI.
     * Object->params  stdClass Key value pairs created when routes contain parameters.
     *
     * @param array $routeParts The original URI parts registered with the router if any.
     * @param array $uriParts   The URI parts from the route the user just requested if any.
     * 
     * @return object An object with all the information any route may need to
     *                properly process and handle a request.
     */
    public function getRouteInfo(array $routeParts = [], array $uriParts = []) {
        $parsed = $this->parseUrl($routeParts, $uriParts);
        if (empty($parsed->trimUrl)) {
            $parsed->trimUrl = $this->appUrl;
        }
        return (object) [
            'appUrl'  => $this->appUrl,
            'reqUrl'  => $this->reqUrl,
            'trimUrl' => $parsed->trimUrl,
            'params'  => $parsed->params
        ];
    }

    /**
     * Creates a regular expression that matches this specific URL parts.
     *
     * @param array $parts An array of URL parts to transform into a matching RegExp.
     * 
     * @return string The RegExp string for this URL.
     */
    private function getRouteRegexp($parts) {
        $regex = '/^';
        foreach ($parts as $part) {
            if (strpos($part, '*') !== false) {
                $regex .= '.*';
            } else if (isset($part[0]) && $part[0] === ':') {
                $regex .= '[\w\d\s\-\_]{1,}?';
            } else {
                $regex .= $part;
            }
            $regex .= '\/';
        }
        $regex = substr($regex, 0, strlen($regex) - 2) . '$/';
        return $regex;
    }

    /**
     * Getter to retrieve the applications current routing table.
     *
     * @return object The current routing table; sorted into routing lengths.
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Getter to retrieve or immediately print the current routing table.
     * 
     * This differs from getRoutes because it converts the routing table into a
     * string and places it inside pre tags for outputting.
     *
     * @param boolean $print Should the result be printed instead of returned; default false.
     * 
     * @return string|void
     */
    public function getRoutingTable($print = false) {
        ob_start();
        echo '<pre>';
        print_r($this->routes);
        echo '</pre>';
        $table = ob_get_clean();
        if ($print === true) {
            echo $table;
        } else {
            return $table;
        }
    }

    /**
     * If the SITE_ROOT constant is missing determine the applications absolute
     * path based on the root directory and not the public directory.
     *
     * @return string The absolute root path to the application.
     */
    public function getSiteRoot() {
        if (defined('SITE_ROOT')) {
            return SITE_ROOT;
        }
        // Determine protocol.
        $protocol = 'https';
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $protocol = $_SERVER['REQUEST_SCHEME'];
        }
        $protocol .= '://';
        // Determine the host.
        $host = 'localhost';
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }
        // Determine any additional path.
        $path = $_SERVER['PHP_SELF'];
        $path = htmlspecialchars(substr($path, 0, stripos($path, 'public/')));
        // Always end with a forward slash.
        if (strlen($path) > 0) {
            if ($path[strlen($path) - 1] !== '/') {
                $path .= '/';
            }
        } else {
            if ($host[strlen($host) - 1] !== '/') {
                $host .= '/';
            }
        }
        // Sites root URL not including the public directory.
        return $protocol . $host . $path;
    }

    /**
     * Some routes are Express like where parameters are part of the URL. Create a new
     * trimmed URL that contains only the URL parts with parameters separated out.
     *
     * @param array $routeParts The original URI parts registered with the router.
     * @param array $uriParts   The URI parts from the route the user just requested.
     * 
     * @return object An object where `params` is an object with all the key value
     *                pairs created from the URL (if any), and a trimmed down URL
     *                with parameter parts removed (if needed).
     */
    protected function parseUrl(array $routeParts, array $uriParts) {
        $trimmedUrl = '';
        $params     = (object) [];
        foreach ($routeParts as $index => $value) {
            if (isset($value[0]) && $value[0] === ':') {
                // This route part should be considered a parameter.
                $name          = substr($value, 1);
                $params->$name = $uriParts[$index];
            } else {
                $trimmedUrl .= $uriParts[$index] . '/';
            }
        }
        return (object) [
            'params'  => $params,
            'trimUrl' => substr($trimmedUrl, 0, strlen($trimmedUrl) - 1)
        ];
    }

    /**
     * Setup the Routers settings based on the servers request properties. By default
     * this method can only be run once when the site is in PRODUCTION mode for
     * security. When PRODUCTION mode is disabled (FALSE) you can change this as 
     * many times as you need. This functionality is meant to aid in testing.
     *
     * @param boolean $hard If TRUE the routing table is flushed.
     * 
     * @return void
     */
    public function rebootSettings(bool $hard = false) {
        if (PRODUCTION == true && $this->initialized == true) {
            return;
        }

        if ($hard) {
            $this->routes = (object) [];
        }

        $this->appUrl = '';
        if (isset($_GET['p'])) {
            $this->appUrl = trim($_GET['p']);
        }
        $this->reqUrl = $_SERVER['REQUEST_URI'];

        // Remove any trailing forward slash from the app URI.
        if (!empty($this->appUrl)) {
            if ($this->appUrl[strlen($this->appUrl) - 1] === '/') {
                $this->appUrl = substr($this->appUrl, 0, strlen($this->appUrl) - 1);
            }
        }

        // Remove the initial forward slash form the request URI: /page => page
        if (!empty($this->reqUrl)) {
            if ($this->reqUrl[0] === '/') {
                $this->reqUrl = substr($this->reqUrl, 1);
            }
        }

        // Create global constants needed for routing and responding.
        if (!defined('SITE_ROOT')) {
            define('SITE_ROOT', $this->getSiteRoot());
        }
        $this->setRequestMethod();
        $this->setResponseType();
    }

    /**
     * Redirect a request to the provided URL.
     *
     * @param string  $url        The location (URL) to redirect to.
     * @param integer $statusCode The HTTP status code to return with this redirect.
     *                            Defaults to 200 (success).
     * 
     * @return void
     */
    public function redirect(string $url, int $statusCode = 200) {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit();
    }

    /**
     * Sets the HTTP responses code and if present, provides a response.
     * 
     * NOTICE: This will always close the active request. The constant `RESPONSE_TYPE` will be
     * used to determine the correct Content-Type header to set and how to convert the message
     * if needed.
     *
     * @param mixed   $message    The message to send back. Can be a string, stdClass (object),
     *                            array, or empty.
     * @param integer $statusCode The HTTP status code of this response to.
     * 
     * @return void
     */
    public function respond($message = null, int $statusCode = 200) {
        http_response_code($statusCode);
        // If no message was given close the connection now.
        if (empty($message)) {
            exit();
        }
        /**
         * If response type is requesting JSON and message is an array or object
         * JSON encode the message and close the connection now.
         */
        $type = strtoupper(gettype($message));
        if (responseType() === 'JSON' && ($type === 'ARRAY' || $type === 'OBJECT')) {
            header('Content-Type: application/json');
            if ($type === 'OBJECT') {
                $message = get_object_vars($message);
            }
            echo json_encode($message);
            exit();
        }
        /**
         * If response type is requesting XML and message is an array or object
         * convert to XML and close the connection now.
         */
        if (responseType() === 'JSON' && ($type === 'ARRAY' || $type === 'OBJECT')) {
            header('Content-Type: application/xml');
            if ($type === 'OBJECT') {
                $message = get_object_vars($message);
            }
            // XML documents must have a single root element.
            if (count($message) > 1) {
                $message = [
                    "response" => $message // Wrap in a root element called `response`.
                ];
            }
            echo $this->arrayToXml($message);
            exit();
        }
        /**
         * If we made it here this must be a traditional HTML request. If the message
         * is an array of object convert it to a string first but send the `text/html`
         * header still. The user is not requesting the correct content type back or
         * the developer has provided a different content type than the user expected.
         */ 
        header('Content-Type: text/html');
        if ($type === 'OBJECT') {
            $message = get_object_vars($message);
        }
        if ($type === 'ARRAY') {
            $message = json_encode($message);
        }
        echo $message;
        exit();
    }

    /**
     * Attempt to navigate to the requested page or resource. If a matching route
     * is found, the routes controller is instantiated and the process method is called.
     *
     * @param string $uri The application URL the user was attempting to navigate to.
     * 
     * @return void
     */
    public function route($uri = null) {
        if (is_null($uri)) {
            $uri = $this->appUrl;
        }
        $parts = explode('/', $uri);
        if (strpos($uri, '*') === false) {
            $index = 'R' . count($parts);
        }
        // Do we have any routes that match this routes length?
        if ($this->routes->$index) {
            // Yes. Check each route with the same length in the table...
            foreach ($this->routes->$index as $route) {
                // Against each routes RegExp. Do we have a match?
                if (preg_match($route->regex, $uri)) {
                    /**
                     * Yes. Instantiate its controller and call the `process` method
                     * passing in all the data the route may need to handle the request.
                     */
                    $info       = $this->getRouteInfo($route->parts, $parts);
                    $controller = new $route->controller($info);
                    $controller->process();
                    return;
                }
            }
        }
        // No match found.
        $this->show404Page();
    }

    /**
     * Register a function or class to handle 404 page not found events. If a
     * class is provided it must have one of the following methods: `process`, 
     * `callback`, or the `__invoke` method.
     *
     * @param function|class $callback A function or class to call when a 404 is triggered.
     * 
     * @return boolean TRUE if the callback was accepted FALSE if not.
     */
    public function set404Handler($callback) {
        if (is_callable($callback)) {
            $this->handle404 = $callback;
            return true;
        }
        if (strtoupper(gettype($callback)) === 'OBJECT') {
            if (method_exists($callback, 'process') || method_exists($callback, 'callback')) {
                $this->handle404 = $callback;
                return true;
            }
        }
        return false;
    }

    /**
     * Parse and record the response type of this request: HTML, JSON, or XML.
     * 
     * @return void
     */
    public function setResponseType() {
        $accept = '';
        if (isset($_SERVER['HTTP_ACCEPT']) && !empty($_SERVER['HTTP_ACCEPT'])) {
            $accept = $_SERVER['HTTP_ACCEPT'];
        } else if (isset($_SERVER['ACCEPT']) && !empty($_SERVER['ACCEPT'])) {
            $accept = $_SERVER['ACCEPT'];
        }
        $accept = strtoupper($accept);
        if (strlen($accept) > 0) {
            // Typical HTTP GET requests contain many Content-Types so check for HTML first.
            if (stripos($accept, 'HTML') !== false) {
                $this->responseType = 'HTML';
                return;
            }
            if (stripos($accept, 'JSON') !== false) {
                $this->responseType = 'JSON';
                return;
            }
            if (stripos($accept, 'XML') !== false) {
                $this->responseType = 'XML';
                return;
            }
        }
        $this->responseType = 'HTML';
    }

    /**
     * Parse and record the request method for this request.
     *
     * @return void
     */
    public function setRequestMethod() {
        $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Show the 404 page because the requested route was not found.
     * 
     * NOTICE: Will default to SM/PAN's 404 page if you do not set your own with
     * Class::set404Handler();
     *
     * @return void
     */
    public function show404Page() {
        $info = $this->getRouteInfo();
        if (!empty($this->handle404)) {
            $callback = $this->handle404;
            if (is_callable($callback)) {
                $callback($info);
                return;
            }
            if (method_exists($callback, 'process')) {
                $callback->process($info);
                return;
            }
            if (method_exists($callback, 'callback')) {
                $callback->callback($info);
                return;
            }
        }
        // Fallback to SM/PAN 404 page.
        $route = new Route((object) []);
        $route->setView('app/views/core/404.phtml');
        $route->process($info);
    }
}
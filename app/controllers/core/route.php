<?php
/**
 * A base class for SM/PAN routes.
 */

namespace Controller\Core;

class Route {

    protected $info;
    protected $vars;
    protected $view;

    /**
     * Create a new instance of the Route class.
     *
     * @param object $info A standard class object with this routes information.
     */
    public function __construct(object $info) {
        /**
         * $info->appUrl  string   The app specific URI not accounting for the fully requested URI.
         * $info->reqUrl  string   The original full requested URI.
         * $info->trimUrl string   A shortened `appUrl` that removes any parameter parts from the URI.
         * $info->params  stdClass Key value pairs created when routes contain parameters.
         */
        $this->info = $info;
        $this->vars = array();
        $this->view = absPath('app/views/core/default.phtml');
    }

    /**
     * After filter that will run after the route is done processing. You can
     * return TRUE or FALSE here but it will not affect anything but other calls
     * to Class::after() that may be waiting to run. Any additional calls will
     * have to decide to respect or ignore the returned value.
     *
     * @return boolean|void
     */
    protected function after(){
        return true;
    }

    /**
     * Before filter that will run before a route starts to process the request.
     * Return false to stop this route from processing (executing) this request.
     *
     * @return boolean|void
     */
    protected function before(){
        return true;
    }

    public function getVars() {
        return $this->vars;
    }

    public function getView() {
        return $this->view;
    }

    /**
     * The default action to run for any application route. Class::before() is
     * called first and if it succeeds (returns TRUE) then Class::render() is
     * called followed by Class::after();
     *
     * @return void
     */
    public function process() {
        if ($this->before() !== false) {
            $this->render();
            $this->after();
        }
    }

    /**
     * Render the view for this route. Use Class::setVars() before this method
     * is called to setup variables that should be available during the render
     * process.
     *
     * @return void
     */
    protected function render() {
        extract($this->vars);
        ob_start();
        /* phpcs:ignore PEAR.Files.IncludingFile.UseInclude */
        require $this->view;
        ob_end_flush();
    }

    /**
     * Setup PHP variables that should be available to the `render` method. You
     * should provide an associative array where the keys are the PHP variable
     * names.
     *
     * @param array $pairs An associative array of variables to make available
     *                     to the `render` method.
     * 
     * @return void
     */
    public function setVars($pairs) {
        if (strtoupper(gettype($pairs)) === 'OBJECT') {
            $pairs = get_object_vars($pairs);
        }
        if (strtoupper(gettype($pairs)) === 'ARRAY') {
            $this->vars = array_merge($this->vars, $pairs);
        }
    }

    /**
     * Set the path to this pages view (template) file. If the file cannot be
     * found then the default SM/PAN page is used.
     *
     * @param string $path The root relative path to the view file for this page.
     * 
     * @return void
     */
    public function setView(string $path) {
        if (!empty($path)) {
            $abspath = absPath($path);
            if (file_exists($abspath)) {
                $this->view = $abspath;
                return;
            }
        }
    }

}
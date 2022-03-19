<?php

namespace Test\Core\Controller;

class Test_Controller extends \Controller\Core\Route {

    private $catchAllRoute      = false;
    private $routeHasParameters = false;

    /**
     * Create a new instance of the Route class.
     *
     * @param object $info A standard class object with this routes information.
     */
    public function __construct(object $info) {
        parent::__construct($info);
        if (count(get_object_vars($info->params)) > 0) {
            $this->routeHasParameters = true;
        }
        if (stripos($info->trimUrl, 'all/route') !== false) {
            $this->catchAllRoute = true;
        }
    }

    protected function render() {
        if ($this->routeHasParameters === true) {
            echo 'Test with parameters successful.';
        } else if ($this->catchAllRoute === true) {
            echo 'Test witch catch all successful.';
        } else {
            echo 'Test successful.';
        }
        print_r($this->info);
    }
}
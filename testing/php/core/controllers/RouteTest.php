<?php
/**
 * Test the Controller\Core\Router class.
 */

namespace Test\Core\Controller;

use \PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase {
    
    /**
     * Make sure no errors are thrown instantiating a new Route.
     *
     * @return Route The Route instance on success.
     */
    public function testRouteClassInstantiatedSuccessfully() {
        $info  = (object) [];
        $route = new \Controller\Core\Route($info);
        $this->assertIsObject($route);
        return $route;
    }

    /**
     * Make sure we can set the view (template file) of a route.
     * 
     * @param Route $route The route instance from the previous test.
     * 
     * @return Route The Route instance on success.
     * 
     * @depends testRouteClassInstantiatedSuccessfully
     */
    public function testSetRouteView($route) {
        if (!is_object($route)) {
            $this->fail("Required variable `route` was missing.");
        }
        $view = '/testing/php/core/controllers/route-test.phtml';
        $route->setView($view);
        $result = str_replace('\\', '/', $route->getView()); // Account for windows OS.
        $this->assertStringContainsString($view, $result);
        return $route;
    }

    /**
     * Make sure we can set the variables (template variables) for a route.
     * 
     * @param Route $route The route instance from the previous test.
     * 
     * @return Route The Route instance on success.
     * 
     * @depends testSetRouteView
     */
    public function testSetRouteVariables($route) {
        if (!is_object($route)) {
            $this->fail("Required variable `route` was missing.");
        }
        $route->setVars(['success' => 'Successful']);
        $this->assertArrayHasKey('success', $route->getVars());
        return $route;
    }

    /**
     * Make sure that a route can render properly without any errors. This test
     * replies on the previous test succeeding otherwise the assertion will fail.
     * 
     * @param Route $route The route instance from the previous test.
     * 
     * @return Route The Route instance on success.
     * 
     * @depends testSetRouteVariables
     */
    public function testProcessRouteSuccessfully($route) {
        if (!is_object($route)) {
            $this->fail("Required variable `route` was missing.");
        }
        ob_start();
        $route->process();
        $result = ob_get_clean();
        $this->assertStringContainsStringIgnoringCase('test successful', $result);
    }

}

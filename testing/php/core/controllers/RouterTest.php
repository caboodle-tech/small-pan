<?php
/**
 * Test the Controller\Core\Router class
 */

namespace Test\Core\Controller;

use \PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase {

    /**
     * Make sure no errors are thrown instantiating a new Router.
     *
     * @return Router The Router instance on success.
     */
    public function testRouterClassInstantiatedSuccessfully() {
        $router = new \Controller\Core\Router();
        $this->assertIsObject($router);
        return $router;
    }

    /**
     * Make sure the Router boots successfully by checking its settings (state).
     *
     * @param Router $router The router instance from the previous test.
     * 
     * @return Router The Router instance on success.
     * 
     * @depends testRouterClassInstantiatedSuccessfully
     */
    public function testSettingsSetCorrectly($router) {
        $this->assertEqualsIgnoringCase('', $router->getAppUrl());
        $this->assertEqualsIgnoringCase('HTML', $router->getResponseType());
        $this->assertEqualsIgnoringCase('GET', $router->getRequestMethod());
        $this->assertStringContainsString('www/sm-pan', $router->getRequestUrl());
        $this->assertStringContainsString('http://localhost', $router->getSiteRoot());
        return $router;
    }

    /**
     * Make sure we can register new routes with the Router: normal route, a
     * route with a parameter, and a catch all route.
     *
     * @param Router $router The router instance from the previous test.
     * 
     * @return Router The Router instance on success.
     * 
     * @depends testSettingsSetCorrectly
     */
    public function testAddRoutesSuccessfully($router) {
        $router->add('test', 'Test\Core\Controller\Test_Controller');
        $router->add('test/:param', 'Test\Core\Controller\Test_Controller'); 
        $router->add('catch/*/*', 'Test\Core\Controller\Test_Controller'); 
        $table = $router->getRoutes();
        $this->assertObjectHasAttribute('R1', $table);
        $this->assertObjectHasAttribute('R2', $table);
        $this->assertObjectHasAttribute('R3', $table);
        $this->assertEqualsIgnoringCase('test', $table->R1[0]->route);
        $this->assertEqualsIgnoringCase('test/:param', $table->R2[0]->route);
        $this->assertEqualsIgnoringCase('catch/*/*', $table->R3[0]->route);
        $this->assertEquals(2, count($table->R2[0]->parts));
        return $router;
    }

    /**
     * Make sure we can route to all of our registered routes.
     *
     * @param Router $router The router instance from the previous test.
     * 
     * @return Router The Router instance on success.
     * 
     * @depends testAddRoutesSuccessfully
     */
    public function testRouteToRegisteredRoutesSuccessfully($router) {
        ob_start();
        $router->route('test');
        $result = ob_get_clean();
        $this->assertStringContainsStringIgnoringCase('test successful', $result);
        ob_start();
        $router->route('test/parameters');
        $result = ob_get_clean();
        $this->assertStringContainsStringIgnoringCase('with parameters', $result);
        ob_start();
        $router->route('catch/all/route');
        $result = ob_get_clean();
        $this->assertStringContainsStringIgnoringCase('catch all', $result);
        return $router;
    }
}
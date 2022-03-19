<?php
/**
 * Test the Module\Core\User class.
 */

namespace Test\Core\Module;

use \PHPUnit\Framework\TestCase;

final class UserTest extends TestCase {

    /**
     * Make sure no errors are thrown instantiating a new User.
     * 
     * @return User The instantiated user object.
     */
    public function testUserClassInstantiatedSuccessfully() {
        $user = new \Module\Core\User();
        $this->assertIsObject($user);
        return $user;
    }

    /**
     * Test that the User class correctly constructs itself using the Session class.
     *
     * @param User $user The current user object.
     * 
     * @depends testUserClassInstantiatedSuccessfully
     * 
     * @return User The instantiated user object.
     */
    public function testSetupValuesCorrectly($user) {
        $this->assertEquals('1234', $user->getUserId());
        $this->assertTrue($user->isLoggedIn());
        return $user;
    }

    /**
     * Test that protected properties (userId, loggedIn) can not be altered for
     * security reasons.
     *
     * @param User $user The current user object.
     * 
     * @depends testSetupValuesCorrectly
     * 
     * @return User The instantiated user object.
     */
    public function testProtectedPropertiesCanNotBeAlteredOrDestroyed($user) {
        // Attempt altering.
        $user->userId   = '9876543210';
        $user->loggedIn = false;
        $this->assertEquals('1234', $user->userId);
        $this->assertTrue($user->loggedIn);
        // Attempt deleting.
        unset($user->userId);
        unset($user->loggedIn);
        $this->assertEquals('1234', $user->userId);
        $this->assertTrue($user->loggedIn);
        return $user;
    }
}
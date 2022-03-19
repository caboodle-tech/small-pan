<?php
/**
 * The user controller that simplifies interactions with the current session user.
 */
namespace Module\Core;

class User {

    private $protected  = ['userId', 'loggedIn'];
    protected $loggedIn = false;
    protected $userId   = '';

    public function __construct() {
        $this->updateSessionStatus();
    }

    public function &__get($name) {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    public function __isset($name) {
        return isset($this->$name);
    }

    public function __set($name , $value) {
        if (!in_array($name, $this->protected)) {
            $this->$name = $value;
        }
    }

    public function __unset($name) {
        if (!in_array($name, $this->protected)) {
            unset($this->name);
        }
    }

    public function getUserId() {
        return $this->userId;
    }

    public function isLoggedIn() {
        return $this->loggedIn;
    }

    public function mustBeLoggedIn($redirect = true) {
        if ($this->loggedIn !== true) {
            if ($redirect === true) {
                header('Location: ' . SITE_ROOT);
                exit();
            }
            http_response_code(401);
            echo 'User must sign in first.';
        }
        exit();
    }

    public function setupUserData() {
        /**
         * You should add functionality here by overwriting this class and method.
         */
    }

    public function updateSessionStatus() {
        global $Session;
        if (isset($Session->userId) && isset($Session->loggedIn)) {
            $this->loggedIn = $Session->loggedIn;
            $this->userId   = $Session->userId;
        }
    }
}
<?php
/**
 * The Database controller for SM/PAN.
 */

namespace Module\Core;

class Database {

    private $closeObservers   = [];
    private $connectObservers = [];
    private $connected        = false;
    private $connection       = null;
    private $settings         = [];

    /**
     * Instantiate a new instance of the Database controller.
     *
     * @param array $settings An associative array of settings to use; defaults
     *                        to values set in the `config.php` file.
     */
    public function __construct($settings = null) {
        if ($settings) {
            $this->settings = (object) [];
            foreach ($settings as $key => $val) {
                $this->settings[$key] = $val;
            }
        } else {
            $this->settings = (object) [
                'database' => getConstant('DB_NAME', ''),
                'host'     => getConstant('DB_HOST', 'localhost'),
                'password' => getConstant('DB_PASSWORD', ''),
                'port'     => getConstant('DB_PORT', false),
                'socket'   => getConstant('DB_SOCKET', false),
                'username' => getConstant('DB_USER', '')
            ];
        }
    }

    /**
     * Close the database connection.
     * 
     * @return boolean Always returns TRUE unless an error was encountered while closing.
     */
    public function close() {
        if ($this->connected === true) {
            $this->connected  = false;
            $this->connection = null;
            // If there are any observers for connection closed events notify them.
            foreach ($this->closeObservers as $observer) {
                if (isset($observer)) {
                    if (is_callable($observer)) {
                        $observer();
                        continue;
                    }
                    if (method_exists($observer, 'close')) {
                        $observer->close();
                        continue;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Attempt to connect to the database.
     *
     * @return object|boolean The `mysqli` connection object or FALSE if the connection could not be opened.
     */
    public function connect() {
        // If a connection exists already use it.
        if ($this->connected && !empty($this->connection)) {
            return $this->connection;
        }

        // Setup connection variables.
        $host = $this->settings->host;
        $user = $this->settings->username;
        $pass = $this->settings->password;
        $db   = $this->settings->database;
        $port = $this->settings->port;
        $sock = $this->settings->socket;

        // Connect according to our connection variables.
        if (!empty($this->settings->port) && !empty($this->settings->socket)) {
            $connection = @new Sqli($host, $user, $pass, $db, $port, $sock);
        } else if (!empty($this->settings->port)) {
            $connection = @new Sqli($host, $user, $pass, $db, $port);
        } else {
            $connection = @new Sqli($host, $user, $pass, $db);
        }
        
        // If we can not connect exit the application.
        if ($connection->connect_errno) {
            global $Router;
            // TODO: Log this: $connection->connect_error;
            $Router->respond('Fatal error: Unable to connect to the database.', 500);
        }

        // Make sure Sqli notifies us if a connection closes.
        $connection->registerCloseObserver($this);

        // Record connection.
        $this->connected  = true;
        $this->connection = $connection;

        // If there are any observers for connection connected events notify them.
        foreach ($this->connectObservers as $observer) {
            if (isset($observer)) {
                if (is_callable($observer)) {
                    $observer();
                    continue;
                }
                if (method_exists($observer, 'connect')) {
                    $observer->connect();
                    continue;
                }
            }
        }

        // Send mysqli connection back to user.
        return $connection;
    }

    /**
     * Generate a unique time based ID for use as a unique keys in the database.
     *
     * @param integer $length How long the ID should be. Defaults to 16 and can not be less than 16.
     * 
     * @return void
     */
    public function generateId(int $length = 16) {
        if ($length < 16) {
            $length = 16;
        }
        $id  = '';
        $ary = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $now = date('n:j:Y:G:i:s');
        $now = explode(':', $now);
        $id .= $ary[intval($now[0])];
        $id .= $ary[intval($now[1])];
        $id .= $now[2];
        $id .= $ary[intval($now[3])];
        $id .= $now[4];
        $id .= $now[5];
        while (strlen($id) < $length) {
            shuffle($ary);
            $id .= $ary[rand(0, 35)];
        }
        return $id;
    }

    /**
     * Register an observer to notify when the database connection is closed.
     *
     * @param function|class $callback The function or class to call when a connection
     *                                 is closed. Classes should have a `close` method.
     * 
     * @return boolean TRUE if the callback was registered, FALSE otherwise.
     */
    public function registerCloseObserver($callback) {
        if (is_callable($callback)) {
            if (!in_array($callback, $this->closeObservers)) {
                array_push($this->closeObservers, $callback);
            }
            return true;
        }
        if (strtoupper(gettype($callback)) === 'OBJECT') {
            if (method_exists($callback, 'close')) {
                if (!in_array($callback, $this->closeObservers)) {
                    array_push($this->closeObservers, $callback);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Register an observer to notify when the database connection is connected (opened).
     *
     * @param function|class $callback The function or class to call when a connection
     *                                 is connected. Classes should have a `connect` method.
     * 
     * @return boolean TRUE if the callback was registered, FALSE otherwise.
     */
    public function registerConnectObserver($callback) {
        if (is_callable($callback)) {
            if (!in_array($callback, $this->connectObservers)) {
                array_push($this->connectObservers, $callback);
            }
            return true;
        }
        if (strtoupper(gettype($callback)) === 'OBJECT') {
            if (method_exists($callback, 'connect')) {
                if (!in_array($callback, $this->connectObservers)) {
                    array_push($this->connectObservers, $callback);
                }
                return true;
            }
        }
        return false;
    }
}
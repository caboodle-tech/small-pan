<?php
/**
 * A simple wrapper for PHP's mysqli allowing SM/PAN to register a close observer
 * on the connection. @see Sqli::registerObserver().
 */

namespace Module\Core;

class Sqli extends \mysqli {

    private $closeObservers = [];
    private $connected      = false;

    /**
     * Instantiate a new instance of this class.
     *
     * @param string $host    The host; database location.
     * @param string $user    The database user.
     * @param string $pass    The database password.
     * @param string $db      The database to connect to.
     * @param int    $port    The port to use for the connection.
     * @param string $socket  The socket to use for the connection.
     * @param string $charset The character set to use; depreciated in PHP >= 7.0?
     */
    public function __construct(
        $host = null,
        $user = null,
        $pass = null,
        $db = '',
        $port = null,
        $socket = null,
        $charset = null
    ) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if (is_null($host)) {
            $host = ini_get('mysqli.default_host');
        }

        if (is_null($user)) {
            $user = ini_get('mysqli.default_user');
        }

        if (is_null($pass)) {
            $pass = ini_get('mysqli.default_pw');
        }

        if (is_null($port)) {
            $port = ini_get('mysqli.default_port');
        }

        if (is_null($socket)) {
            $socket = ini_get('mysqli.default_socket');
        }

        parent::__construct($host, $user, $pass, $db, $port, $socket);

        if (!is_null($charset)) {
            $this->set_charset($charset);
        }

        if ($this->connect_errno === 0) {
            $this->connected = true;
        }
    }

    /**
     * When this class is destructed make sure any open database connections close.
     * 
     * @return void
     */
    public function __destruct() {
        if ($this->connected === true) {
            $this->connected = false;
            $this->close();
        }
    }

    /**
     * Close the open database connection.
     *
     * @return boolean Always returns TRUE unless an error was encountered while closing.
     */
    public function close() {
        if ($this->connected === true) {
            // Call the original mysqli close method.
            parent::close();
            // Notify close observers that the connection has been closed.
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
}
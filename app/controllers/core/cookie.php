<?php
/**
 * Cookie manger that wraps common PHP cookie functionality.
 * 
 * @link https://github.com/overclokk/cookie Inspiration for this class.
 */

namespace Controller\Core;

class Cookie {

    /**
     * Delete a cookie.
     *
     * @param string $name Cookie name.
     *
     * @return bool @see Class::set();
     */
    public function delete(string $name) {
        return $this->set($name, null, time() - 3600);
    }

    /**
     * Set a new cookie that never expires; will be set for 25 years.
     *
     * @param string $name     The cookie name.
     * @param string $value    The cookie value.
     * @param string $path     The path on the server in which the cookie will be available on.
     *                         Defaults to '/' which is the entire domain.
     * @param string $domain   The (sub)domain that the cookie is available to.
     * @param bool   $secure   Indicates that the cookie should only be transmitted over
     *                         a secure HTTPS connection from the client. Defaults to FALSE.
     * @param bool   $httpOnly When TRUE the cookie will be made accessible only through
     *                         the HTTP protocol. Defaults to FALSE.
     *
     * @return bool            Returns TRUE if no errors were encountered but this does
     *                         not indicate that the user accepted the cookie. 
     */
    public function forever(
        string $name,
        $value,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false
    ) {
        $expires = 788400000; // 25 Years.
        return $this->set($name, $value, $expires, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Get a previously set cookie.
     *
     * @param string  $name        The name of the cookie to retrieve.
     * @param boolean $asJson      Should the cookie value be parsed as JSON. Defaults to false.
     * @param boolean $associative When parsing the cookie value as JSON should an associative
     *                             array be returned instead of an stdClass (object). Defaults
     *                             to null which will use your servers JSON_OBJECT_AS_ARRAY value.
     * 
     * @return mixed The cookie value as a string, stdClass (object), associative array, or null.
     */
    public function get(string $name, bool $asJson = false, $associative = null) {
        if (isset($_COOKIE[$name])) {
            if ($asJson) {
                // $associative must be true, false, or null.
                if (!is_bool($associative)) {
                    if (!is_null($associative)) {
                        $associative = null;
                    }
                }
                return json_decode($_COOKIE[$name], $associative);
            }
            return strip_tags(stripslashes($_COOKIE[$name]));
        }
        return null;
    }

    /**
     * Convert the expiration time in seconds to an actual expires timestamp.
     *
     * @param integer $seconds How soon this cookie should expire.
     * 
     * @return integer The full expires timestamp; how many seconds after the epoch.
     */
    private function getExpirationDate(int $seconds = 0) {
        return intval($seconds > 0 ? time() + $seconds : -1);
    }

    /**
     * Alias of the delete method.
     * 
     * @param string $name @see Class::delete
     * 
     * @return bool @see Class::set();
     * 
     * @alias Class::delete();
     */
    public function remove(string $name) {
        return $this->delete($name);
    }

    /**
     * Set a new cookie.
     *
     * @param string $name     The cookie name.
     * @param string $value    The cookie value.
     * @param int    $expire   Expiration time in seconds; will be converted to a timestamp
     *                         accounting for the epoch.
     * @param string $path     The path on the server in which the cookie will be available on.
     *                         Defaults to '/' which is the entire domain.
     * @param string $domain   The (sub)domain that the cookie is available to.
     * @param bool   $secure   Indicates that the cookie should only be transmitted over
     *                         a secure HTTPS connection from the client. Defaults to FALSE.
     * @param bool   $httpOnly When TRUE the cookie will be made accessible only through
     *                         the HTTP protocol. Defaults to FALSE.
     *
     * @return bool            Returns TRUE if no errors were encountered but this does
     *                         not indicate that the user accepted the cookie. 
     */
    public function set(
        string $name,
        $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false
    ) {
        $type = strtoupper(gettype($value));
        switch($type) {
            case 'STRING':
                break;
            case 'OBJECT':
                $value = get_object_vars($value);
                $value = json_encode($value);
                break;
            case 'ARRAY':
                $value = json_encode($value);
                break;
            case 'NULL':
                $value = '';
        }

        return setcookie(
            $name,
            $value,
            $this->getExpirationDate($expire),
            $path,
            $domain,
            $secure,
            $httpOnly
        );
    }
}
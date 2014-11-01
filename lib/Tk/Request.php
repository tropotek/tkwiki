<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This object handles the all HttpRequests.
 *
 * @package Tk
 */
class Tk_Request
{
    
    /**
     * @var Tk_Request
     */
    static $instance = null;
    
    
    /**
     * Get an instance of this object
     *
     * @return Tk_Request
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    
    /**
     * Returns the referering Url.
     *
     * @return Tk_Type_Url Returns null if there was no referer.
     */
    function getReferer()
    {
        $referer = $this->getServerParameter('HTTP_REFERER');
        if ($referer != null) {
            return new Tk_Type_Url($referer);
        }
    }
    
    
    /**
     * Returns the URI which was given in order to access the page.
     *
     * For example, '/index.html'.
     *
     * @return Tk_Type_Url
     */
    function getRequestUri()
    {
        static $url = null;
        if (!$url) {
            $urlStr = $this->getServerParameter('REQUEST_URI');
            $scheme = 'http://';
            if ($this->getServerParameter('HTTPS') == 'on') {
                $scheme = 'https://';
            }
            $urlStr = $scheme . $this->getServerParameter('HTTP_HOST') . $urlStr;
            $url = new Tk_Type_Url($urlStr);
        }
        return clone $url;
    }
    
    /**
     * Binds data to this request, using the name specified.
     *
     * @param string $key A key to retrieve the data.
     * @param mixed $value
     */
    function setParameter($key, $value)
    {
        if ($value === null) {
            unset($_REQUEST[$key]);
        } else {
            $_REQUEST[$key] = $value;
        }
    }
    
    /**
     * Returns the value of a request parameter as a String,
     * or null if the parameter does not exist.
     *
     * You should only use this method when you are sure the parameter has
     * only one value. If the parameter might have more than one value, use
     * getParameterValues().
     *
     * If you use this method with a multivalued parameter, the value returned
     * is equal to the first value in the array returned by getParameterValues.
     *
     * @param string $key The parameter name.
     * @return mixed
     */
    function getParameter($key)
    {
        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
    }
    
    /**
     * Returns an array of String objects containing all of the values the
     * given request parameter has, or null if the parameter does not exist.
     *
     * If the parameter has a single value, the array has a length of 1.
     *
     * @param string $key
     * @return array
     */
    function getParameterValues($key)
    {
        if (isset($_REQUEST[$key])) {
            if (is_array($_REQUEST[$key])) {
                return $_REQUEST[$key];
            } else {
                return array($_REQUEST[$key]);
            }
        }
    }
    
    /**
     * Get the request array map
     *
     * @return array
     */
    function getAllParameters()
    {
        return $_REQUEST;
    }
    
    /**
     * Returns an array containing the names of the parameters contained in
     * this request.
     *
     * @return array
     */
    function getParameterNames()
    {
        return array_keys($_REQUEST);
    }
    
    /**
     * Check if a parameter name exists in the request
     *
     * @param string $key
     * @return boolean
     */
    function existsParameter($key)
    {
        return isset($_REQUEST[$key]);
    }
    
    /**
     * Returns the value of a request parameter.
     *
     * @return string The value or null if the parameter is not in the request.
     */
    private function getServerParameter($key)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
    }

    
    /**
     * Get the remote hostname if available
     *
     * @return string
     */
    function getRemoteHost()
    {
        return gethostbyaddr($this->getRemoteAddr());
    }
    
    /**
     * Get the IP of the clients machine.
     * Returns 0.0.0.0 if no IP found.
     *
     * @return string
     */
    function getRemoteAddr()
    {
        $ip = '0.0.0.0';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) { // check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { // to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if (!empty($_SERVER['REMOTE_ADDR'])) { // User remote address
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        return $ip;
    }
    
    /**
     * Get the IP of the clients machine.
     *
     * @return string
     * @todo: Run test to find the better function
     */
    function getRemoteAddrAlt()
    {
        $ip = "0.0.0.0";
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
                $ip = getenv("REMOTE_ADDR");
            } else {
                if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
                    $ip = getenv("HTTP_X_FORWARDED_FOR");
                } else {
                    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                }
            }
        }
        return $ip;
    
    }
    
    /**
     * Get the browser userAgent string
     *
     * @return string
     */
    function getUserAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return '';
    }
    
    
    /**
     * Returns the name of the HTTP method with which this request was made.
     *
     * For example, GET, POST, or PUT.
     *
     * @return string
     */
    function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Returns the raw post data.
     *
     * @return string
     */
    function getRawPostData()
    {
        global $HTTP_RAW_POST_DATA;
        return $HTTP_RAW_POST_DATA;
    }
    
    
    
    

    
    /**
     * Binds data to this request, using the name specified.
     *
     * @param string $key
     * @param mixed $value
     */
    static function set($key, $value)
    {
        self::getInstance()->setParameter($key, $value);
    }
    
    /**
     * Returns the value of a request parameter as a String,
     * or null if the parameter does not exist.
     *
     * @param string $key
     * @return mixed
     */
    static function get($key)
    {
        return self::getInstance()->getParameter($key);
    }
    
    /**
     * Returns an array of String objects containing all of the values the
     * given request parameter has, or null if the parameter does not exist.
     *
     * If the parameter has a single value, the array has a length of 1.
     *
     * @param string $key
     * @return array
     */
    static function getList($key)
    {
        return self::getInstance()->getParameter($key);
    }
    
    /**
     * Check if a parameter name exists in the request
     *
     * @param string $key
     * @return boolean
     */
    static function exists($key)
    {
        return self::getInstance()->existsParameter($key);
    }
    
    /**
     * Returns the referering Url for the current reuest.
     *
     * @return Tk_Type_Url
     */
    static function referer()
    {
        return self::getInstance()->getReferer();
    }
    
    /**
     * Returns the URI which was given in order to access the page.
     *
     * @return Tk_Type_Url
     */
    static function requestUri()
    {
        return self::getInstance()->getRequestUri();
    }
    
    /**
     * Get the browser userAgent string
     *
     * @return string
     */
    static function agent()
    {
        return self::getInstance()->getUserAgent();
    }
    
    /**
     * Get the browser remoteAddr string
     *
     * @return string
     */
    static function remoteAddr()
    {
        return self::getInstance()->getRemoteAddr();
    }
    
    /**
     * Get the browser Remote Host string
     *
     * @return string
     */
    static function remoteHost()
    {
        return self::getInstance()->getRemoteHost();
    }
    
    
    
}

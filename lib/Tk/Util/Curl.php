<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This library is a basic implementation of CURL capabilities.
 * It works in most modern versions of IE and FF.
 *
 * It exports the CURL object globally, so set a callback with setCallback($func).
 * (Use setCallback(array('class_name', 'func_name')) to set a callback as a func
 * that lies within a different class)
 * Then use one of the CURL request methods:
 *
 * get($url);<br>
 * post($url, $vars); vars is a urlencoded string in query string format.
 *
 * Your callback function will then be called with 1 argument, the response text.
 * If a callback is not defined, your request will return the response text.
 *
 * @package Tk
 */
class Tk_Util_Curl extends Tk_Object implements Tk_Util_DataPathInterface
{
    
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    
    /**
     * @var mixed
     */
    private $callback = null;
    
    /**
     * @var array
     */
    private $headers = array();
    
    /**
     * @var array
     */
    private $responseHeaders = array();
    
    /**
     * @var string
     */
    private $userAgent = '';
    
    /**
     * @var string
     */
    private $compression = 'gzip';
    
    /**
     * @var string
     */
    private $cookies = true;
    
    /**
     * @var string
     */
    private $cookieFile = 'cookies.txt';
    
    /**
     * @var string
     */
    private $proxy = '';
    
    /**
     * @var inter
     */
    private $timeout = 30;
    
    /**
     * Create a CURL Object to send GET and POST requests
     *
     * @param string $userAgent
     * @param boolean $cookies
     */
    function __construct($userAgent = '', $cookies = true)
    {
        if ($userAgent) {
            $this->userAgent = $userAgent;
        }
        $this->cookies = $cookies;
        if ($this->cookies == true) {
            $this->initCookie();
        }
    }
    
    /**
     * init cookie file
     *
     * @param string $cookieFile
     */
    function initCookie()
    {
        $cookieFile = $this->getDataPath()->toString() . '/' . $this->cookieFile;
        if (!file_exists($cookieFile)) {
            if (!$this->getDataPath()->isDir()) {
                mkdir($this->getDataPath(), 0775, true);
            }
            $fp = fopen($cookieFile, 'w');
            if ($fp == null) {
                throw new Tk_ExceptionIllegalArgument('The cookie file could not be opened. Check permissions');
            }
            fclose($fp);
        }
    }
    
    /**
     * setCallback
     *   Use setCallback(array('class_name', 'func_name')) to set a callback as a function
     *   that lies within a different class
     *
     * @param mixed $funcName
     */
    function setCallback($funcName)
    {
        $this->callback = $funcName;
    }
    
    /**
     * doRequest
     *
     * @param Tk_Type_Url $url
     * @param mixed $method
     * @param string $str
     * @return string
     */
    function doRequest(Tk_Type_Url $url, $method = 'GET', $str = '')
    {
        if (!$url instanceof Tk_Type_Url) {
            throw new Tk_Exception('Invalid url type.');
        }
        $cookieFile = $this->getDataPath()->toString() . '/' . $this->cookieFile;
        $ch = curl_init($url->toString());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        if ($this->cookies == true) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        }
        curl_setopt($ch, CURLOPT_ENCODING, $this->compression);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }
        if ($method == self::METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (ini_get('safe_mode') === false && ini_get('open_basedir') == '') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            $errmsg = curl_error($ch);
            $errcode = curl_errno($ch);
            curl_close($ch);
            throw new Tk_Exception($errmsg, $errcode);
        } else {
            curl_close($ch);
            if ($this->callback) {
                $callback = $this->callback;
                $this->callback = false;
                return $this->initResponse(call_user_func($callback, $data));
            } else {
                return $this->initResponse($data);
            }
        }
    }
    
    /**
     * Send a request to a server using the GET method
     *
     * @param Tk_Type_Url $url
     * @return Tk_Type_Url
     */
    function get(Tk_Type_Url $url)
    {
        return $this->doRequest($url, self::METHOD_GET);
    }
    
    /**
     * Send a request to a server using the POST method
     *
     * @param Tk_Type_Url $url
     * @param string $str
     */
    function post(Tk_Type_Url $url, $str = '')
    {
        return $this->doRequest($url, self::METHOD_POST, $str);
    }
    
    /**
     * Init the returned response string
     *
     * @param string $str
     * @return string
     */
    private function initResponse($str)
    {
        $lines = explode("\n", $str);
        
        $line = array_shift($lines);
        while ($line != '' && $line != "\r") {
            $this->responseHeaders[] = $line;
            $line = array_shift($lines);
        }
        return implode("\n", $lines);
    }
    
    /**
     * Add a header string to send to the server
     * eg:
     *   $header = 'Content-type: text/html; charset=iso-8859-1'
     *
     * @param string $header
     */
    function setHeader($header)
    {
        $pos = strpos($header, ':');
        $name = substr($header, 0, $pos);
        //$value = substr($header, $pos+1);
        foreach ($this->headers as $i => $head) {
            if (preg_match('/^' . $name . '/i', $head)) {
                $this->headers[$i] = $header;
                return;
            }
        }
        $this->headers[] = $header;
    }
    
    /**
     * get the response headers
     *
     * @return array
     */
    function getResponseHeaders()
    {
        return $this->responseHeaders;
    }
    
    /**
     * Set the location of the cookie file
     *
     * @param string $file
     */
    function setCookieFile($file)
    {
        $this->cookieFile = basename($file);
    }
    
    /**
     * Set the compression string.
     * DEFAULT: gzip
     *
     * @param string $str
     */
    function setCompression($str = 'gzip')
    {
        $this->compression = $str;
    }
    
    /**
     * Set the proxy server if required
     *
     * @param string $host
     */
    function setProxy($host)
    {
        $this->proxy = $host;
    }
    
    /**
     * Set the timeout in seconds
     *
     * @param integer $sec
     */
    function setTimeout($sec)
    {
        $this->timeout = intval($sec);
    }
    
    /**
     * Get the CURL data path
     *
     * @return Tk_Type_Path
     */
    function getDataPath()
    {
        return new Tk_Type_Path(Tk_Config::getDataPath() . '/curl');
    }
}
<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An OO Wrapper around a HTTP response.
 *
 * @package Tk
 */
class Tk_Response extends Tk_Object
{
    
    /**
     * @var Tk_Response
     */
    static $instance = null;
    
    /**
     * Status code 404 indicating that the requested resource is not
     *  available.
     * @var integer
     */
    const SC_NOT_FOUND = 404;
    /**
     * Status code 200 indicating all is OK
     * @var integer
     */
    const SC_OK = 200;
    
    /**
     * Status code (500) indicating an error inside the HTTP server which
     * prevented it from fulfilling the request.
     * @var integer
     */
    const SC_INTERNAL_SERVER_ERROR = 500;
    
    /**
     * Redirect 301 Moved Permanently.
     * Convert to GETConfirm re-POST
     * @var integer
     */
    const SC_REDIRECT_MOVED_PERMANENTLY = 301;
    
    /**
     * Redirect 302 Found.
     * Confirm re-POST
     * @var integer
     */
    const SC_REDIRECT_FOUND = 302;
    
    /**
     * Redirect 303 See Other.
     * dont cache, always use GET
     * @var integer
     */
    const SC_REDIRECT_SEE_OTHER = 303;
    
    /**
     * Redirect 304 Not Modified.
     * use cache
     * @var integer
     */
    const SC_REDIRECT_NOT_MODIFIED = 304;
    
    /**
     * Redirect 305 Use Proxy.
     * @var integer
     */
    const SC_REDIRECT_USE_PROXY = 305;
    
    /**
     * Redirect 307 Temorary Redirect.
     * @var integer
     */
    const SC_REDIRECT_TEMPORARY_REDIRECT = 307;
    
    /**
     * @var array
     */
    static $statusCodes = array(Tk_Response::SC_OK, Tk_Response::SC_NOT_FOUND, Tk_Response::SC_INTERNAL_SERVER_ERROR, Tk_Response::SC_REDIRECT_MOVED_PERMANENTLY, Tk_Response::SC_REDIRECT_FOUND, Tk_Response::SC_REDIRECT_SEE_OTHER, Tk_Response::SC_REDIRECT_NOT_MODIFIED, Tk_Response::SC_REDIRECT_USE_PROXY, Tk_Response::SC_REDIRECT_TEMPORARY_REDIRECT);
    
    /**
     * @var array
     */
    static $statusText = array(Tk_Response::SC_OK => 'OK', Tk_Response::SC_NOT_FOUND => 'Resource Not Found', Tk_Response::SC_INTERNAL_SERVER_ERROR => 'Internal Server Error', Tk_Response::SC_REDIRECT_MOVED_PERMANENTLY => 'Moved Permanently', Tk_Response::SC_REDIRECT_FOUND => 'Found', Tk_Response::SC_REDIRECT_SEE_OTHER => 'See Other', Tk_Response::SC_REDIRECT_NOT_MODIFIED => 'Not Modified', Tk_Response::SC_REDIRECT_USE_PROXY => 'Use Proxy', Tk_Response::SC_REDIRECT_TEMPORARY_REDIRECT => 'Temporary Redirect');
    
    /**
     * Valid 404 error page names
     * @var array
     */
    static $errorPgs = array('error.html', 'error.htm', 'error.php', '404.html', '404.htm', '404.php');
    
    /**
     * @var string
     */
    static $errorTemplate = '<html>
  <head>
    <title>%s %s</title>
    <style type="text/css">
      body {
        margin: 0;
        padding: 0;
      }
      h1 {
       margin: 0px;
       padding: 4px 10px;
       background: #369;
       color: #FFF;
      }
      pre {
        color: #000;
        padding: 10px;
        font-size: 12px;
      }
    </style>
  </head>
  <body>
    <h1>%s</h1>
    <pre class="msg">%s</pre>
  </body>
</html>';
    
    /**
     * @var string
     */
    protected $buffer = '';
    
    /**
     * @var boolean
     */
    protected $committed = false;
    
    /**
     * @var array
     */
    protected $headers = array();
    
    
    
    /**
     * Sigleton, No instances can be created.
     * Use:
     *   Tk_Response::getInstance()
     */
    private function __construct() { }
    
    /**
     * Get an instance of this object
     *
     * @return Tk_Response
     */
    static function getInstance()
    {
        if (ob_get_contents()) {
            ob_end_clean();
        }
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Add a header value to send with the response.
     *
     * @param string $name
     * @param string $value
     */
    function addHeader($name, $value = '')
    {
        $this->headers[$name] = $value;
    }
    
    /**
     * Forces any content in the buffer to be written to the client.
     *
     * A call to this method automatically commits the response, meaning the
     * status code and headers will be written.
     *
     * Alias to Tk_response::flush()
     * @deprecated
     */
    static function flushBuffer()
    {
        self::flush();
    }
    
    /**
     *  Forces any content in the buffer to be written to the client.
     *
     * A call to this method automatically commits the response, meaning the
     * status code and headers will be written.
     *
     */
    static function flush()
    {
        Tk_Session::getInstance()->writeClose();
        self::getInstance()->committed = true;
        self::getInstance()->flushHeaders();
        self::getInstance()->buffer = str_replace(array('<?xml version="1.0"?>' . "\n"), "", self::getInstance()->buffer);
        echo self::getInstance()->buffer;
    }
    
    /**
     * Write any headers to the buffer
     *
     */
    private function flushHeaders()
    {
        if (in_array(Tk_Request::getInstance()->getRequestUri()->getBasename(), self::$errorPgs)) {
            $this->addHeader('Status', 404);
            //header("404: Moved Permanently HTTP/1.1", true, 404);
        }
        
        // Date in the past
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        //header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        // propriotory stuff
        header("X-Author: Michael Mifsud <info@tropotek.com.au>");
        header("X-Developer: Tropotek Development");
        header("X-Copyright: (c)" . date('Y') . " Tropotek ");
        
        
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) == 'status') {
                header($_SERVER['SERVER_PROTOCOL'] . ' ' . $value . self::$statusText[$value]);
            } else {
                header("$name: $value");
            }
        }
        $this->headers = array();
    }
    
    /**
     * Returns a boolean indicating if the response has been committed.
     *
     * A committed response has already had its status code and headers written.
     * @return boolean
     */
    function isCommitted()
    {
        return $this->committed;
    }
    
    /**
     * Clears any data that exists in the buffer.
     *
     * @throws Tk_Exception
     */
    static function reset()
    {
        if (self::getInstance()->committed) {
            throw new Tk_Exception('1000: The response has already been committed.');
        }
        self::getInstance()->buffer = '';
    }
    
    /**
     * Sends an error response to the client using the specified status.
     *
     * The response will look like an HTML-formatted server error page
     * containing the specified message, The the content type will be set to
     * "text/html", and cookies and other headers will be left unmodified.
     *
     * If the response has already been committed, this method throws an
     * IllegalStateException. After using this method, the response should be
     * considered to be committed and should not be written to.
     *
     * @param const $statusCode The error status code
     * @param string $msg An optional descriptive message
     * @param Tk_Web_Dom_Template $template An optional template to use
     * @throws Tk_Exception
     *
     */
    static function sendError($statusCode, $msg = '')
    {
        if (!in_array($statusCode, self::$statusCodes)) {
            throw new Tk_Exception('Invalid status code');
        }
        if (self::getInstance()->committed) {
            throw new Tk_Exception('1001: The response has already been committed.');
        }
        
        //self::reset();
        //@header(self::$statusText[$statusCode], true, $statusCode);
        
        Tk_Session::set('Tk_Error', array(
            'statusCode' => $statusCode,
            'statusCodeText' => self::$statusText[$statusCode],
            'msg' => $msg,
            'uri' => Tk_Request::requestUri()->toString()
        ));
        
        if (Tk_Request::requestUri()->getBasename() != 'error.html') {
            //Tk_Type_Url::create('/error.html')->redirect();
        }
        $template = self::$errorTemplate;
//        if (is_file(Tk_Config::get('system.sitePath') . '/html/error.html')) {
//            $template = file_get_contents( Tk_Config::get('system.sitePath') . '/html/error.html' );
//        }
        
        self::getInstance()->addHeader(self::$statusText[$statusCode], $statusCode);
        self::write(sprintf($template, $statusCode, self::$statusText[$statusCode], $statusCode . ' ' . self::$statusText[$statusCode], $msg));
        
        self::getInstance()->committed = true;
        self::flush();
        exit();
    }
    
    /**
     * Writes to the response buffer.
     *
     * @param string
     * @throws Tk_Exception
     */
    static function write($data)
    {
        if (self::getInstance()->committed) {
            throw new Tk_Exception('1002: The response has already been committed.');
        }
        self::getInstance()->buffer .= $data;
    }
    
    /**
     * Returns a textual representation of the object.
     *
     * @return string
     */
    function toString()
    {
        return $this->buffer;
    }
}
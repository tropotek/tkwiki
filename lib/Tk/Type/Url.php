<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @author Darryl Ross <darryl.ross@aot.com.au>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A URL class.
 *
 * <b>[[&lt;scheme&gt;://][[&lt;user&gt;[:&lt;password&gt;]@]&lt;host&gt;[:&lt;port&gt;]]][/[&lt;path&gt;][?&lt;query&gt;][#&lt;fragment&gt;]]</b>
 *
 * where:<br/>
 *  o scheme defaults to http <br/>
 *  o host defaults to the current host.<br/>
 *  o port defaults to 80<br/>
 *
 * The spec must be for an absolute Url (with a scheme), or start with 'www'
 * or '/'.
 *
 * If the spec starts with '/' then the base Url defaults to
 * $_SERVER['HOST_NAME'] . Url::$pathPrefix
 *
 * Note: There is no support for relative paths.
 *
 * @package Tk
 */
class Tk_Type_Url extends Tk_Object
{
    /**
     * A prefix to append to path.
     *
     * Useful for when working in a dev enviroment, where the application is
     * not installed in the docroot of the domain.
     * NOTE: This value should never be '/' use '' instead.
     *
     * @var string
     */
    static $pathPrefix = '';

    /**
     * For session vars
     */
    protected $sesPrefix = null;
    protected $sesQuery = null;

    /**
     * This is the supplied full url
     * @var string
     */
    protected $spec = '';

    /**
     * @var boolean
     */
    private $relative = false;
    /**
     * @var string
     */
    private $fragment = '';
    /**
     * @var string
     */
    private $host = '';
    /**
     * @var string
     */
    private $password = '';
    /**
     * @var string
     */
    private $path = '';
    /**
     * @var string
     */
    private $port = '80';
    /**
     * @var aray
     */
    private $query = array();
    /**
     * @var string
     */
    private $scheme = 'http';
    /**
     * @var string
     */
    private $user = '';




    /**
     * __construct
     *
     * @param string $spec The String to parse as a URL
     */
    function __construct($spec = null)
    {
        $this->spec = $spec;
        $this->init();
    }

    /**
     * Create a url
     *
     * @return Tk_Type_Url
     */
    static function create($spec = null)
    {
        return new self($spec);
    }

    /**
     * Create a url that prepends the data thdoc directory to the spec.
     *
     * @return Tk_Type_Url
     */
    static function createDataUrl($spec)
    {
        if (substr($spec, 0, 5) == '/data') {
            $spec = substr($spec, 5);
        }
        //$htdata = substr(Tk_Config::get('system.dataPath'), strlen(Tk_Config::get('system.sitePath')));
        return new self(Tk_Config::get('system.dataUrl').$spec);
    }

    /**
     * Create a url
     *
     * @return Tk_Type_Url
     * @deprecated  Use ::create()
     */
    static function createUrl($spec)
    {
        return new self($spec);
    }

    /**
     * Initalise the url object
     */
    private function init()
    {
        $spec = $this->spec;
        $host = 'localhost';

        if ($spec == null) {
            //$spec = 'http://localhost/';
            $spec = $_SERVER['REQUEST_URI'];
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $this->scheme = 'https';
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }

        if (!preg_match("/^(http|https|ftp|file|gopher|news)/i", $spec)) {
            if (strtolower(substr($spec, 0, 3)) == 'www') {
                $spec = $this->scheme . '://' . $spec;
            } else {
                if ($spec && $spec{0} != '/') {
                    $spec = '/' . $spec;
                }
                $spec = $this->scheme . '://' . $host . $spec;
                $this->relative = true;
            }
        }


        $components = parse_url($spec);
        if ($components) {
            if (array_key_exists('scheme', $components)) {
                $this->scheme = $components['scheme'];
            }
            if (array_key_exists('host', $components)) {
                $this->host = $components['host'];
            }
            if (array_key_exists('port', $components)) {
                $this->port = $components['port'];
                if ($this->relative) {
                    $this->port = $_SERVER['SERVER_PORT'];
                }
            }
            if (array_key_exists('user', $components)) {
                $this->user = $components['user'];
            }
            if (array_key_exists('pass', $components)) {
                $this->password = $components['pass'];
            }
            if (array_key_exists('path', $components)) {
                $this->path = $components['path'];
                if ($this->host == '' && substr($this->path, 0, 3) == 'www') {
                    $this->host = $this->path;
                    $this->path = '';
                }
            }
            if (array_key_exists('query', $components)) {
                $components['query'] = html_entity_decode($components['query']);
                parse_str($components['query'], $this->query);
            }

            if (array_key_exists('fragment', $components)) {
                $this->fragment = urldecode($components['fragment']);
            }

            if ($this->path != '' && $this->path{0} == '.') {
                $this->path = substr($this->path, 1);
            }
            if ($this->path != '' && $this->path{0} != '/') {
                $this->path = '/' . $this->path;
            }
        }

        if (strlen(self::$pathPrefix) > 1 && $this->relative) {
            $len = strlen(self::$pathPrefix);
            if (substr($this->path, 0, $len) != self::$pathPrefix) {
                $this->path = self::$pathPrefix . $this->path;
            }
        }

        if ($this->host == '') {
            $this->host = $host;
        }
    }

    /**
     * on serialise
     *
     * @return array
     */
    public function __sleep()
    {
        $this->init();
        $this->sesPrefix = self::$pathPrefix;
        $this->sesQuery = $this->query;
        //vd(self::$pathPrefix, $this->sesPrefix, $this->sesQuery);
        return array('spec', 'sesPrefix', 'sesQuery');
    }

    /**
     * on unserialise
     *
     */
    public function __wakeup()
    {
        //vd($this->sesPrefix, $this->sesQuery);
        self::$pathPrefix = $this->sesPrefix;
        $this->init();
        if (is_Array($this->sesQuery) && count($this->sesQuery)) {
            $this->query = $this->sesQuery;
        }
        $this->sesQuery = null;
        $this->sesPrefix = null;
    }

    /**
     * Normalize the url, this is as if the url had been reloaded
     * Useful for when the object is serialised/unserialised
     *
     * @return Tk_Type_Url
     */
    function normlize()
    {
        $this->spec = $this->toString();
        $this->init();
        return $this;
    }

    /**
     * Get the fragment of the url
     *
     * @return string
     */
    function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Set the fragment portion of the url
     *
     * @param string $str
     * @return Tk_Type_Url
     */
    function setFragment($str)
    {
        $this->fragment = $str;
        return $this;
    }

    /**
     * Get the host name
     *
     * @return string
     */
    function getHost()
    {
        return $this->host;
    }

    /**
     * Set the host portion of the url
     *
     * @param string $str
     * @return Tk_Type_Url
     */
    function setHost($str)
    {
        $this->host = $str;
        return $this;
    }

    /**
     * Get the password if available
     *
     * @return string
     */
    function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the url path
     *
     * @return string
     */
    function getPath($removePrefix = false)
    {
        if ($removePrefix && strlen(self::$pathPrefix) > 1) {
            return str_replace(self::$pathPrefix, '', urldecode($this->path));
        }
        return urldecode($this->path);
    }

    /**
     * Get the port of the url
     *
     * @return string
     */
    function getPort()
    {
        return $this->port;
    }

    /**
     * Set the scheme
     *
     * @param string $scheme
     * @return Tk_Type_Url
     */
    function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Get the scheme
     *
     * @return string
     */
    function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the user
     *
     * @return string
     */
    function getUser()
    {
        return $this->user;
    }


    /**
     * Get the query string of the url
     *
     * @return string
     */
    function getQuery()
    {
        $query = '';
        foreach ($this->query as $field => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $query .= urlencode($field) . '[]=' . urlencode($v) . '&';
                }
            } else {
                $query .= urlencode($field) . '=' . urlencode($value) . '&';
            }
        }
        $query = substr($query, 0, -1);
        return $query;
    }

    /**
     * Add a field to the query string
     *
     * @param string $field
     * @param string $value
     * @return Tk_Type_Url
     */
    function setQueryField($field, $value = null)
    {
        if ($value === null) {
            $value = $field;
        }
        $this->query[$field] = $value;
        return $this;
    }

    /**
     * Remove a field in the querystring
     *
     * @param string $field
     * @return Tk_Type_Url
     */
    function removeQueryField($field)
    {
        if (array_key_exists($field, $this->query)) {
            unset($this->query[$field]);
        }
        return $this;
    }


    /**
     * clear and reset the querystring
     *
     * @return Tk_Type_Url
     */
    function resetQueryFields()
    {
        $this->query = array();
        return $this;
    }

    /**
     * Get the array of queryfields in a map
     *
     * @return array
     */
    function getQueryFields()
    {
        return $this->query;
    }

    /**
     * Get the array of queryfields in a map
     *
     * @return Tk_Type_Url
     */
    function setQueryFields($map)
    {
        if ($map != null) {
            $this->query = $map;
        }
        return $this;
    }

    /**
     * Get a value from the query string.
     *
     * @param string $field
     * @return string
     */
    function getQueryFieldValue($field)
    {
        if (array_key_exists($field, $this->query)) {
            return $this->query[$field];
        }
    }

    /**
     * Returns file extension for this pathname.
     *
     * A the last period ('.') in the pathname is used to delimit the file
     * extension .If the pathname does not have a file extension null is
     * returned.
     *
     * @return string
     */
    function getExtension()
    {
        if (substr($this->getPath(), -6) == 'tar.gz') {
            return 'tar.gz';
        }
        $pos = strrpos(basename($this->getPath()), '.');
        if ($pos) {
            return substr(basename($this->getPath()), $pos + 1);
        }
        return '';
    }

    /**
     * Get the basename of this url.
     *
     * @return string
     */
    function getBasename()
    {
        return basename($this->getPath());
    }



    // --- Easy methods
    /**
     * Alias for getQueryFieldValue($field)
     *
     * @param string $field
     * @return string
     */
    function get($field)
    {
        return $this->getQueryFieldValue($field);
    }

    /**
     * Alias for setQueryField($field, $name)
     *
     * @param string $field
     * @param string $value
     */
    function set($field, $value = null)
    {
        return $this->setQueryField($field, $value);
    }

    /**
     * Alias for removeQueryField($field)
     *
     * @param string $field
     * @return Tk_Type_Url
     */
    function delete($field)
    {
        return $this->removeQueryField($field);
    }

    /**
     * Check if a query field exists in the array
     *
     * @param string $field
     * @return boolean
     */
    function exists($field)
    {
        return array_key_exists($field, $this->query);
    }

    /**
     * Alias for resetQueryFields
     * clear and reset the querystring
     *
     * @return Tk_Type_Url
     */
    function reset()
    {
        return $this->resetQueryFields();
    }

    /**
     * Alias for resetQueryFields
     * clear and reset the querystring
     *
     * @return Tk_Type_Url
     */
    function clear()
    {
        return $this->resetQueryFields();
    }


    /**
     * redirect
     *
     * Codes:
     *
     *  301: Moved Permanently
     *
     *    - The requested resource has been assigned a new permanent URI and any
     *      future references to this resource SHOULD use one of the returned URIs.
     *      Clients with link editing capabilities ought to automatically re-link
     *      references to the Request-URI to one or more of the new references
     *      returned by the server, where possible. This response is cacheable
     *      unless indicated otherwise.
     *
     *  302: Found
     *
     *    - The requested resource resides temporarily under a different URI. Since
     *      the redirection might be altered on occasion, the client SHOULD continue to
     *      use the Request-URI for future requests. This response is only cacheable
     *      if indicated by a Cache-Control or Expires header field.
     *
     *  303: See Other
     *
     *    - The response to the request can be found under a different URI and SHOULD
     *      be retrieved using a GET method on that resource. This method exists primarily
     *      to allow the output of a POST-activated script to redirect the user agent
     *      to a selected resource. The new URI is not a substitute reference for
     *      the originally requested resource. The 303 response MUST NOT be cached,
     *      but the response to the second (redirected) request might be cacheable.
     *
     *  304: Not Modified
     *
     *    - If the client has performed a conditional GET request and access is allowed,
     *      but the document has not been modified, the server SHOULD respond with this
     *      status code. The 304 response MUST NOT contain a message-body, and thus is
     *      always terminated by the first empty line after the header fields.
     *
     *  305: Use Proxy
     *
     *    - The requested resource MUST be accessed through the proxy given by the Location
     *      field. The Location field gives the URI of the proxy. The recipient is expected
     *      to repeat this single request via the proxy. 305 responses MUST only be
     *      generated by origin servers.
     *
     *  306: (Unused)
     *
     *    - The 306 status code was used in a previous version of the specification, is
     *      no longer used, and the code is reserved.
     *
     *  307: Temporary Redirect
     *
     *    - The requested resource resides temporarily under a different URI. Since the
     *      redirection MAY be altered on occasion, the client SHOULD continue to use the
     *      Request-URI for future requests. This response is only cacheable if indicated
     *      by a Cache-Control or Expires header field.
     *
     *
     *
     *
     * func: redirect($to,$code=307)
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @link http://edoceo.com/creo/php-redirect.php
     */
    function redirect($code = 301)
    {
        $response = Tk_Response::getInstance();
        $response->reset();
        $location = $this->toString();
        $hs = headers_sent();
        if ($hs == false) {
            switch ($code) {
                case 301:
                    // Convert to GET
                    header("301: Moved Permanently HTTP/1.1", true, $code);
                    break;
                case 302:
                    // Conform re-POST
                    header("302: Found HTTP/1.1", true, $code);
                    break;
                case 303:
                    // dont cache, always use GET
                    header("303: See Other HTTP/1.1", true, $code);
                    break;
                case 304:
                    // use cache
                    header("304: Not Modified HTTP/1.1", true, $code);
                    break;
                case 305:
                    header("305: Use Proxy HTTP/1.1", true, $code);
                    break;
                case 306:
                    header("306: Not Used HTTP/1.1", true, $code);
                    break;
                case 307:
                    header("307: Temporary Redirect HTTP/1.1", true, $code);
                    break;
                default :
                    throw new Tk_ExceptionIllegalArgument("Unhandled redirect() HTTP Code: $code", E_USER_ERROR);
                    break;
            }
            $response->addHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->addHeader('Location', $location);
            Tk_Response::flush();
        } elseif (($hs == true) || ($code == 302) || ($code == 303)) {
            throw new Tk_ExceptionIllegalArgument("Headers Allready Sent.", E_USER_ERROR);
        }
        exit();
    }

    /**
     * Return a string representation of this object
     *
     * @return string
     */
    function toString()
    {
        if (preg_match("/^(javascript|mailto)/i", $this->spec)) {
            return $this->spec;
        }
        $url = '';
        if ($this->scheme != '') {
            $url .= $this->scheme . '://';
        }
        if ($this->user != '' || $this->password != '') {
            $url .= $this->user . ':' . $this->password . '@';
        }
        if ($this->host != '') {
            $url .= $this->host;
            if ($this->port != 80) {
                $url .= ':' . $this->port;
            }
        }
        if ($this->path != '') {
            $url .= $this->path;
        }
        $query = $this->getQuery();
        if ($query != '') {
            $url .= '?' . $query;
        }
        if ($this->fragment != '') {
            $url .= '#' . $this->fragment;
        }
        return $url;
    }

    /**
     * Return a string representation of this object without the host portion
     *
     * @return string
     */
    function toUriString()
    {
        $url = '';
        if ($this->path != '' && $this->path != '/' && $this->path != '\\') {
            $url .= $this->path;
            if (self::$pathPrefix && self::$pathPrefix != '/') {
                $url = str_replace(self::$pathPrefix, '', $url);
            }
        }
        $query = $this->getQuery();
        if ($query != '') {
            $url .= '?' . $query;
        }
        if ($this->fragment != '') {
            $url .= '#' . $this->fragment;
        }
        return $url;
    }
}
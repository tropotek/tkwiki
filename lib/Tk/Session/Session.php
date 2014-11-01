<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 *
 * @package Tk
 */
class Tk_Session
{

    /**
     * @var Session
     */
    protected static $instance = null;

    /**
     * @var Session_Driver_Interface
     */
    protected static $driver;

    /**
     * Protected session keys
     * @var array
     */
    protected static $protect = array('_session_id', '_user_agent', '_last_activity', '_ip_address', '_total_hits', '_site_referer');




    /**
     * Get an instance of this object
     *
     * @return Tk_Session
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * On first session instance creation, sets up the driver and creates session.
     */
    function __construct()
    {
        // This part only needs to be run once
        if (self::$instance === NULL) {
            // Load config
            //Tk::loadConfig('tk.session');
            // Makes a mirrored array, eg: foo=foo
            self::$protect = array_combine(self::$protect, self::$protect);
            // Configure garbage collection
            ini_set('session.gc_probability', (int)Tk_Config::get('tk.session.gc_probability'));
            ini_set('session.gc_divisor', 100);
            ini_set('session.gc_maxlifetime', (Tk_Config::get('tk.session.expiration') == 0) ? 86400 : Tk_Config::get('tk.session.expiration'));

            // Create a new session
            $this->create();

            if (Tk_Config::get('tk.session.regenerate') > 0 && ($_SESSION['_total_hits'] % Tk_Config::get('tk.session.regenerate')) === 0) {
                // Regenerate session id and update session cookie
                $this->regenerate();
            } else {
                // Always update session cookie to keep the session alive
                //Tk_Cookie::set(Tk_Config::get('tk.session.name'), $_SESSION['_session_id'], Tk_Config::get('tk.session.expiration'));
                Tk_Cookie::set(Tk_Config::get('tk.session.name'), $_SESSION['_session_id'], time() + Tk_Config::get('tk.session.expiration'));
            }

            // Make sure that sessions are closed before exiting
            register_shutdown_function(array($this, 'writeClose'));

            // Singleton instance
            self::$instance = $this;
        }
        Tk::log('Session Library initialized', TK::LOG_INFO);
    }

    /**
     * Create a new session.
     *
     */
    function create()
    {
        // Destroy any current sessions
        $this->destroy();

        if (Tk_Config::get('tk.session.driver') && strtolower(Tk_Config::get('tk.session.driver')) !== 'native') {
            // Initialize the driver
            $driver = 'Tk_Session_Driver_' . ucfirst(Tk_Config::get('tk.session.driver'));
            self::$driver = new $driver();
            // Validate the driver
            if (!(self::$driver instanceof Tk_Session_Driver_Interface)) {
                throw new Tk_Exception('Invalid driver object: ' . $driver);
            }
            // Register non-native driver as the session handler
            session_set_save_handler(array(self::$driver, 'open'), array(self::$driver, 'close'),
                array(self::$driver, 'read'), array(self::$driver, 'write'),
                array(self::$driver, 'destroy'), array(self::$driver, 'gc'));
        }

        // Name the session, this will also be the name of the cookie
        $sesName = 'default';
        if (Tk_Config::get('tk.session.name')) {
            $sesName = Tk_Config::get('tk.session.name');
        }

        // Validate the session name
        if (!preg_match('~^(?=.*[a-z])[a-z0-9_]++$~iD', $sesName)) {
            throw new Tk_Exception('Invalid Session Name: ' . $sesName);
        }
        if (Tk_Request::exists($sesName) && isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
            session_id(Tk_Request::get($sesName));
        }
        session_name($sesName);

        // Start the session!
        session_start();


        // reset the session cookie expiration
        if (isset($_COOKIE[$sesName])) {
            Tk_Cookie::set($sesName, $_COOKIE[$sesName], time() + Tk_Config::get('tk.session.expiration'));
            //Tk_Cookie::set($sesName, $_COOKIE[$sesName], Tk_Config::get('tk.session.expiration'));
        }

        // Put session_id in the session variable
        $_SESSION['_session_id'] = session_id();
        if(!isset($_SESSION['_user_agent'])) {
            $_SESSION['_user_agent'] = Tk_Request::getInstance()->getUserAgent();
            $_SESSION['_ip_address'] = Tk_Request::getInstance()->getRemoteAddr();
            $_SESSION['_site_referer'] = Tk_Request::getInstance()->getReferer();
            $_SESSION['_total_hits'] = 0;
        }

        // Increase total hits
        $_SESSION['_total_hits'] += 1;

        // Validate data only on hits after one
        if ($_SESSION['_total_hits'] > 1) {
            // Validate the session
            foreach (Tk_Config::get('tk.session.validate') as $valid) {
                switch ($valid) {
                    // Check user agent for consistency
                    case 'user_agent' :
                        if ($_SESSION['_'.$valid] !== Tk_Request::getInstance()->getUserAgent())
                            return $this->create();
                        break;
                    // Check ip address for consistency
                    case 'ip_address' :
                        if ($_SESSION['_'.$valid] !== Tk_Request::getInstance()->getRemoteAddr())
                            return $this->create();
                        break;
                    // Check expiration time to prevent users from manually modifying it
                    case 'expiration' :
                        if (time() - $_SESSION['_last_activity'] > time() + ini_get('session.gc_maxlifetime'))
                            return $this->create();
                        break;
                }
            }
        }
        // Update last activity
        $_SESSION['_last_activity'] = time();
    }

    /**
     * Regenerates the global session id.
     *
     */
    function regenerate()
    {
        Tk::log('Regenerating the session.', TK::LOG_INFO);
        if (strtolower(Tk_Config::get('tk.session.driver')) === 'native') {
            // Generate a new session id
            // Note: also sets a new session cookie with the updated id
            session_regenerate_id(TRUE);
            // Update session with new id
            $_SESSION['_session_id'] = session_id();
        } else {
            // Pass the regenerating off to the driver in case it wants to do anything special
            $_SESSION['_session_id'] = self::$driver->regenerate();
        }
        // Get the session name
        $name = session_name();
        if (isset($_COOKIE[$name])) {
            // Change the cookie value to match the new session id to prevent "lag"
            $_COOKIE[$name] = $_SESSION['_session_id'];
        }
    }

    /**
     * Destroys the current session.
     *
     *
     */
    function destroy()
    {
        if (session_id() !== '') {
            Tk::log('Destroying the session.', TK::LOG_INFO);
            // Get the session name
            $name = session_name();
            // Destroy the session
            session_destroy();
            // Delete the session cookie
            Tk_Cookie::delete($name);
            // Re-initialize the array
            $_SESSION = array();
        }
    }

    /**
     * Runs the system.session_write event, then calls session_write_close.
     *
     *
     */
    function writeClose()
    {
        static $run = null;
        if ($run === null) {
            // Close the session
            Tk::log("Closing the session.", TK::LOG_INFO);
            session_write_close();
            Tk::cleanup();
            $run = true;
        }
    }

    /**
     * Get the session id.
     *
     * @return  string
     */
    function getId()
    {
        return $_SESSION['_session_id'];
    }

    /**
     * Return the session id name
     *
     * @return string
     */
    function getName()
    {
        return session_name();
    }



    /**
     * Binds data to this session, using the name specified.
     *
     * @param string $key A key to retrieve the data
     * @param mixed $value
     */
    function setParameter($key, $value = null)
    {
        if ($value === null) {
            $this->removeParameter($key);
        } else {
            if (isset(self::$protect[$key]))
                return;
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Returns the data bound with the specified name in this session,
     * or null if data is bound under the name.
     *
     * @param string $key The key to retrieve the data.
     * @return mixed
     */
    function getParameter($key)
    {
        if (isset($_SESSION[$key])) {
            return  $_SESSION[$key];
        }
    }

    /**
     * Returns the data bound with the specified name in this session,
     * or null if data is bound under the name.
     * Once returned removes the data from the session
     *
     * @param string $key The key to retrieve the data.
     * @return mixed
     */
    function getParameterOnce($key)
    {
        $value = $this->getParameter($key);
        $this->removeParameter($key);
        return $value;
    }

    /**
     * Unset an element from the session
     *
     * @param string $key
     */
    function removeParameter($key)
    {
        if (isset(self::$protect[$key]))
            return;
        unset($_SESSION[$key]);
    }

    /**
     * Check if a parameter name exists in the request
     *
     * @param string $name
     * @return boolean
     */
    function parameterExists($key)
    {
        return (isset($_SESSION[$key]));
    }










    /**
     * Binds data to this session, using the name specified.
     *
     * @param string $key A key to retrieve the data
     * @param mixed $value
     */
    static function set($key, $value = null)
    {
        return self::getInstance()->setParameter($key, $value);
    }

    /**
     * Returns the data bound with the specified name in this session,
     * or null if data is bound under the name.
     *
     * @param string $key The key to retrieve the data.
     * @return mixed
     */
    static function get($key)
    {
        return self::getInstance()->getParameter($key);
    }

    /**
     * Returns the data bound with the specified name in this session,
     * or null if data is bound under the name.
     * Once returned removes the data from the session
     *
     * @param string $key The key to retrieve the data.
     * @return mixed
     */
    static function getOnce($key)
    {
        return self::getInstance()->getParameterOnce($key);
    }

    /**
     * Unset an element from the session
     *
     * @param string $key
     */
    static function delete($key)
    {
        return self::getInstance()->removeParameter($key);
    }

    /**
     * Check if a parameter name exists in the request
     *
     * @param string $name
     * @return boolean
     */
    static function exists($key)
    {
        return self::getInstance()->parameterExists($key);
    }

}
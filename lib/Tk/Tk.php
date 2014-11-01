<?php
/* @NEW-LIB@
 *
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Tropotek
 */

/**
 * This is the lib main object
 *
 * @package Tk
 */
class Tk
{
    // Log levels
    const LOG_DISABLED = 0;
    const LOG_ERROR = 1;
    const LOG_EMAIL = 2;
    const LOG_ALERT = 3;
    const LOG_INFO  = 4;
    const LOG_DEBUG = 5;
    
    
    static $classConfig = array();
    
    static $scriptTime = null;
    
    
    /**
     * This methos will setup and install the Tk development API
     *
     * @param string $sitePath
     * @param string $libPath
     * @param string $prepend
     * @param string $htdocRoot If not supplied the app will try to determin using PHP_SELF global
     * @param $boolean $verbose If set to false all log messages are disabled for the Tk::init() function
     * @return Tk_Web_SiteFrontController
     */
    static function init($sitePath, $libPath, $prepend = 'Tk/_prepend.php', $htdocRoot = null, $verbose = true)
    {
        self::$scriptTime = microtime(true);
        ini_set('include_path', $libPath . (stristr(PHP_OS, "Win") ? ';' : ':') . ini_get('include_path'));
        
        require_once ($prepend);
        
        
        Tk::loadConfig('tk.session');
        Tk::loadConfig('tk.cookie');
        
        
        // include the site prepend if it exists
        if (is_file($sitePath . '/prepend.php')) {
            include_once ($sitePath. '/prepend.php');
        }
        
        Tk_Config::setSitePath($sitePath);
        Tk_Config::setDataPath($sitePath . '/data');
        Tk_Config::setLibPath($libPath);
        
        // Load Config files
        Tk_Config::setDebugMode(false);
        Tk_Config::getInstance()->parseConfigFile(new Tk_Type_Path(Tk_Config::getSitePath().'/config.ini'));
        Tk_Config::getInstance()->parseConfigFile(new Tk_Type_Path(Tk_Config::getDataPath().'/config.ini'));
        Tk_Config::getInstance()->parseConfigFile(new Tk_Type_Path(Tk_Config::getSitePath().'/config.php'));
        
        $logLevel = Tk_Config::get('system.logLevel');
        if (!$verbose) {
            Tk_Config::set('system.logLevel', $verbose);
        }
        
        
        // Set up PHP environment from config settings
        if (Tk_Config::getTmpPath()) {
            ini_set('upload_tmp_dir', Tk_Config::getTmpPath());
        }
        if (Tk_Config::isDebugMode()) {
            error_reporting(-1);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
        if (Tk_Config::isDebugMode() && Tk_Config::getErrorLog()) {
            ini_set('error_log', Tk_Config::getErrorLog());
        }
        if (Tk_Config::getTimezone()) {
            ini_set('date.timezone', Tk_Config::getTimezone());
        }
        if (Tk_Config::getTimezone()) {
            ini_set('date.timezone', Tk_Config::getTimezone());
        }
        if (Tk_Config::getCurrency()) {
            $eregStr = '';
            foreach (Tk_Type_Currency::$currencyList as $k => $v) {
                $eregStr .= "$k|";
            }
            $eregStr = '/^(' . substr($eregStr, 0, -1) . ')$/i';
            if (!preg_match($eregStr, Tk_Config::getCurrency())) {
                throw new Tk_ExceptionIllegalArgument('Valid currency values are: AUD, NZD, USD, THB');
            }
        }
        if (Tk_Config::getTmpPath() && !is_dir(Tk_Config::getTmpPath())) {
            mkdir(Tk_Config::getTmpPath(), 0777, true);
        }
        
        Tk_Cookie::getInstance();
        
        if ($verbose) {
            Tk::log('', TK::LOG_INFO);
            Tk::log('----------------------------------------------------------------------', TK::LOG_INFO);
            Tk::log('----------------------------------------------------------------------', TK::LOG_INFO);
            if (!empty($_SERVER['REQUEST_URI'])) {
                Tk::log('Script: ' . $_SERVER['REQUEST_URI'], TK::LOG_INFO);
            }
            if (!empty($_SERVER['HTTP_HOST'])) {
                Tk::log('Domain: ' . $_SERVER['HTTP_HOST'], TK::LOG_INFO);
            }
            if (Tk_Request::getInstance()->getRemoteAddr()) {
                Tk::log('Client: ' . Tk_Request::getInstance()->getRemoteAddr(), TK::LOG_INFO);
            }
            if (Tk_Request::getInstance()->agent()) {
                Tk::log('Agent: ' . Tk_Request::getInstance()->agent(), TK::LOG_INFO);
            }
            if (Tk_Session::getInstance()->getId()) {
                Tk::log('SessionId: ' . Tk_Session::getInstance()->getId(), TK::LOG_INFO);
            }
            if (Tk_Session::getInstance()->getName()) {
                Tk::log('SessionName: ' . Tk_Session::getInstance()->getName(), TK::LOG_INFO);
            }
            Tk::log('----------------------------------------------------------------------', TK::LOG_INFO);
            Tk::log('Libraries Initalised', TK::LOG_INFO);
        }
        
        /*
         *  Initalise the site session
         *  WARNING: DO NOT INITALISE ANY CLASS STATIC VARS BEFORE THIS POINT!
         */
        if (!isset($session)) {
            //ini_set('session.gc_maxlifetime', 10800);  // 3hrs
            $session = Tk_Session::getInstance();
        }
        
        /*
         * Init any class static variables
         * TODO: Re-evaluate this, we could use the config file instead
         */
        Tk_Type_Url::$pathPrefix = $htdocRoot;
        Tk_Type_Path::$pathPrefix = $sitePath;
        Tk_Type_Money::$defaultCurrencyCode = Tk_Config::get('system.currency');
        
        Tk_Config::set('system.logLevel', $logLevel);
    }

    
    
    /**
     * System Cleanup
     * This method will be called after the session cleanup command
     * 
     */
    static function cleanup()
    {
        // Get Script run time and display..
        session_write_close();
        $time = self::scriptDuration();
        Tk::log('System Cleanup.', Tk::LOG_INFO);
        Tk::log('----------------------------------------------------------------------', Tk::LOG_INFO);
        Tk::log('Object Db Loads: ' . Tk_Db_Factory::getLoadCount(), Tk::LOG_INFO);
        Tk::log('Class Loads: ' . Tk_AutoLoader::getLookupCount(), Tk::LOG_INFO);
        Tk::log('Script Time: ' . round($time, 4) . ' sec', Tk::LOG_INFO);
        Tk::log('======================================================================'. "\n", Tk::LOG_INFO);
    }
    
    /**
     * Add a page to the admin pages list
     *
     * @param string $requestPath
     * @param Com_Web_MetaData $metaData
     * @return Cms_Config
     */
    static function registerUrl($requestPath, $metaData)
    {
        $arr = Tk_Config::get('system.url.pages');
        if (!is_array($arr)) {
            $arr = array();
        }
        $arr[$requestPath] = $metaData;
        Tk_Config::set('system.url.pages', $arr);
    }
    
    /**
     * remove a page to the uri list
     *
     * @param string $requestPath
     * @return Cms_Config
     */
    static function removeUrl($requestPath)
    {
        $arr = Tk_Config::get('system.url.pages');
        if (is_array($arr)) {
            if (array_key_exists($requestPath, $arr)) {
                unset($arr[$requestPath]);
                Tk_Config::set('system.url.pages', $arr);
            }
        }
    }
    
    /**
     * Enter description here ...
     *
     * @param string $name
     * @return boolean
     */
    static function moduleExists($name)
    {
        if (@is_dir(Tk_Config::get('system.libPath') . '/' . $name)) {
            return true;
        }
        return false;
    }
    
    /**
     * Get a timestamp is microseconds when the script execution started
     *
     * @return float
     */
    static function scriptTime()
    {
        return self::$scriptTime;
    }
    
    /**
     * Get the current script running time in seconds
     *
     * @return string
     */
    static function scriptDuration()
    {
    	return (string)(microtime(true)-self::$scriptTime);
    }
    


    
    /**
     * Returns the value of a key, defined by a 'dot-noted' string, from an array.
     *
     * @param   array $array  array to search
     * @param   string $keys  dot-noted string: foo.bar.baz
     * @return  mixed         if the key is found, null if not
     */
    static function getDotKey($array, $keys)
    {
        if (empty($array)) {
            return;
        }
        $keys = explode('.', $keys);
        do {
            $key = array_shift($keys);
            if (!isset($array[$key])) {
                break;
            }
            if (is_array($array[$key]) && !empty($keys)) {
                $array = $array[$key];
            } else {
                return $array[$key];
            }
        } while (!empty($keys));
    }
    
    /**
     * Check if a dot key exists in the array
     *
     * @param   array $array  array to search
     * @param   string $keys  dot-noted string: foo.bar.baz
     * @return  mixed         if the key is found, null if not
     */
    static function existsDotKey($array, $keys)
    {
        if (empty($array)) {
            return false;
        }
        $keys = explode('.', $keys);
        do {
            $key = array_shift($keys);
            if (!isset($array[$key])) {
                break;
            }
            if (is_array($array[$key]) && !empty($keys)) {
                $array = $array[$key];
            } else {
                if (isset($array[$key])) {
                    return true;
                }
                return false;
            }
        } while (!empty($keys));
        return false;
    }
    
    /**
     * Set the value of a key, defined by a 'dot-noted' string, from an array.
     *
     * @param   array $array array to add the parameter to
     * @param   string $keys dot-noted string: foo.bar.baz
     * @param   mixed $item  The object to add to the config
     */
    static function setDotKey(&$array, $keys, $item)
    {
        if (!is_array($array)) {
            return;
        }
        $keyStr = $keys;
        $keys = explode('.', $keys);
        do {
            $key = array_shift($keys);
            if (empty($keys)) {
                $array[$key] = $item;
                return;
            } else {
                if (isset($array[$key]) && !is_array($array[$key])) {
                    throw new Tk_Exception('Config parameter contains a value, invalid config key: ' . $keyStr);
                }
                if (!isset($array[$key])) {
                    $array[$key] = array();
                }
                $array = &$array[$key];
            }
        } while (!empty($keys));
    }
    
    /**
     * This will lazy load custom settings files based on dot name notation.
     * Eg: tk.session will be the file {site}/lib/Tk/config/session.php and {site}/lib/config/session.php
     * This will not overwrite any settings set in the config.ini/php at system start
     *
     * @param string $key
     * @return array
     */
    static function loadConfig($key)
    {
        if (isset(self::$classConfig[$key])) {
            return;
        }
        $arr = explode('.', $key);
        if (count($arr) != 2) {
            Tk::log('Invalid loadConfig() key: ' . $key);
            return;
        }
        $lib = ucfirst($arr[0]);
        $name = $arr[1];
        $config = array();
        
        $defaultConfig = Tk_Config::get('system.libPath') . '/' . $lib . '/config/' . $name . '.php';
        Tk_Config::getInstance()->parseConfigFile(new Tk_Type_Path($defaultConfig), $key, false);
        $userConfig = Tk_Config::get('system.libPath') . '/config/' . $name . '.php';
        Tk_Config::getInstance()->parseConfigFile(new Tk_Type_Path($userConfig), $key, false);
        
        Tk::log('Loaded config for: ' . $key, Tk::LOG_INFO);
        return self::$classConfig[$key] = true;
    }
    
    
    
    /**
     * Quick debugging of any variable.
     * Any number of parameters can be sent.
     *
     * @param mixed args[]
     * @return  string
     */
    static function debug()
    {
        if (!Tk_Config::get('debug.enable')) {
            return;
        }
        $output = '';
        foreach (func_get_args() as $var) {
            $objStr = $var;
            if ($var === null) {
                $objStr = 'NULL';
            } else if (is_bool($var)) {
                $objStr = $var == true ? 'true' : 'false';
            } else if (is_string($var)) {
                $objStr = str_replace("\0", '|', $var);
            } else if (is_object($var) && method_exists($var, '__toString')) {
                $objStr = $var->__toString();
            } else if ((is_object($var) && !method_exists($var, '__toString')) || is_array($var)) {
                $objStr = str_replace("\0", '|', print_r($var, true));
            }
            
            $type = gettype($var);
            if ($type == 'object') {
                $type = get_class($var);
            }
            
            $output .= '(' . $type . '): ' . $objStr . "\n" ;
        }
        
        $trace = debug_backtrace();
        $traceSkip = 0;
        if (isset($trace[1]['function']) && $trace[1]['function'] == 'invokeArgs' && isset($trace[1]['class']) && $trace[1]['class'] == 'ReflectionMethod') {
            $traceSkip = 3;
        }
        
        $class = "";
        
        if (isset($trace[$traceSkip]['file'])) {
            $s = 0;
            if ($traceSkip > 0) {
                $s = $traceSkip-1;
            }
            $class = substr(str_replace('/', '_', substr($trace[$s]['file'], strlen(Tk_Config::get('system.libPath'))+1)), 0, -4);
        }
        $line = '--';
        if (isset($trace[$traceSkip]['line'])) {
            $line = $trace[$traceSkip]['line'];
        }
        
        $msg = $class . '(' . $line . ")\n" . $output . "\n" . self::traceToString($traceSkip) . "\n";
        return self::log($msg, self::LOG_DEBUG);
    }
    
    /**
     * Add a new message to the log file.
     *
     * @param string $message
     * @param integer $level
     * @return string
     * @tod Fix to use new Tk_Log object
     */
    static function log($message, $level = self::LOG_DEBUG)
    {
        $output = '';
        if (!is_writable(Tk_Config::get('system.log'))) {
            return $output;
        }
        if ($level <= Tk_Config::get('system.logLevel')) {
            $l = array('DISABLED', 'ERROR' ,'EMAIL' ,'ALERT' ,'INFO' ,'DEBUG');
            $output = sprintf('[%s][%5.2f][%9s][%s]', date('Y-m-d H:i:s'), round(Tk::scriptDuration(), 2), Tk_Type_Path::bytes2String(memory_get_usage()), $l[$level]);
            $output = $output . ': ' . $message;
            $file = fopen(Tk_Config::get('system.log'), 'a');
            fwrite($file, $output . "\n");
            fclose($file);
            if ($level <= self::LOG_EMAIL) {
                self::emailLog($message, $level);
            }
        }
        return $output;
    }
    
    
    static private function emailLog($message, $level)
    {
        if ($level > Tk_Config::get('system.emailLogLevel')) {
            return true;
        }
        $l = array('DISABLED', 'ERROR' ,'EMAIL' ,'ALERT' ,'INFO' ,'DEBUG');
        $msg  = 'Script: ' . $_SERVER['REQUEST_URI'] . "\n";
        $msg .= 'Client: ' . Tk_Request::getInstance()->getRemoteAddr() . "\n";
        $msg .= 'Agent: ' . Tk_Request::getInstance()->agent() . "\n\n ---------------------------\n\n";
        $msg .= $message;
        return mail(Tk_Config::getSupportEmail(), Tk_Request::getInstance()->getRemoteAddr() . ' - Log email message (' . $l[$level] . ')', $msg);
    }
    

    /**
     * Get the request key for an event.
     * This can be nessasery to create event/query names that do not clash
     * Typically the object Id is used as the $keyId
     *
     * @param string $eventName
     * @param string $keyId
     * @return string
     */
    static function createEventKey($eventName, $keyId)
    {
        if ($keyId === null || $keyId === '') {
            return $eventName;
        }
        return $eventName . '_' . $keyId;
    }
    
    
    /**
     * Get a string representation of the backtrace
     *
     * @param array $trace get this from calling the php function debug_backtrace()
     * @param integer $skip The number of entries to skip in the backtrace
     * @return string
     */
    static function traceToString($skip = 0)
    {
        $trace = debug_backtrace();
        $str = '';
        for($i = 1; $i < $skip; $i++) {
            array_shift($trace);
        }
        foreach ($trace as $i => $t) {
            $type = '';
            if (isset($t['type'])) {
                $type = $t['type'];
            }
            $class = '';
            if (isset($t['class'])) {
                $class = $t['class'];
            }
            $file = '';
            if (isset($t['file'])) {
                $t['file'] = str_replace(Tk_Config::getSitePath(), '', $t['file']);
                $file = $t['file'];
            }
            $line = '';
            if (isset($t['line'])) {
                $line = $t['line'];
            }
            $function = '';
            if (isset($t['function'])) {
                $function = $t['function'];
            }
            $str .= sprintf("[%s] %s(%s): %s%s%s \n", $i, $file, $line, $class, $type, $function);
        }
        return $str;
    }
    
}
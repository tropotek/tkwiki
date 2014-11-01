<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

if (!defined('E_DEPRECATED')) {
   define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
   define('E_USER_DEPRECATED', 16384);
}


/**
 * A custom exception thrower to turn PHP errors into execeptions.
 *
 * @param integer $context
 * @param string $msg
 * @param string $file
 * @param integer $line
 * @see http://au.php.net/manual/en/class.errorexception.php
 * @package Exception
 */
function exceptions_error_handler($context, $msg, $file, $line)
{
    $e = new Tk_Exception($msg, 0, $file, $line, $context);
    /*
    $severity = 1 * E_ERROR | 1 * E_WARNING | 0 * E_PARSE | 0 * E_NOTICE | 0 * E_CORE_ERROR | 0 * E_CORE_WARNING | 0 * E_COMPILE_ERROR | 0 * E_COMPILE_WARNING |
            0 * E_USER_ERROR | 0 * E_USER_WARNING | 0 * E_USER_NOTICE | 0 * E_STRICT | 0 * E_RECOVERABLE_ERROR | 0 * E_DEPRECATED | 0 * E_USER_DEPRECATED;
    if (($e->getSeverity() & $severity) != 0) {
           throw $e;
    } else {
        Tk::log($e->toString(), Tk::LOG_INFO);
    }
    */
    
    switch ($context) { // Ignore all warnings and notices in live mode
        case E_WARNING : // 2
        case E_NOTICE : // 8
        case E_CORE_WARNING : // 32
        case E_USER_WARNING : // 512
        case E_USER_NOTICE : // 1024
        case E_STRICT : // 2048
        case E_DEPRECATED:      // 8192       // PHP 5.3
        case E_USER_DEPRECATED: // 16384      // PHP 5.3
            Tk::log($e->toString(), Tk::LOG_INFO);
            return;
    }
    throw $e;
}
set_error_handler('exceptions_error_handler');

/**
 * Base class for all Tk exceptions.
 *
 * @package Exception
 */
class Tk_Exception extends Exception
{
    
    /**
     * Define an assoc array of error string
     * in reality the only entries we should
     * consider are E_WARNING, E_NOTICE, E_USER_ERROR,
     * E_USER_WARNING and E_USER_NOTICE
     */
    static $errorStr = array('E_ERROR' => 'Error', 'E_WARNING' => 'Warning', 'E_PARSE' => 'Parsing Error', 'E_NOTICE' => 'Notice', 
        'E_CORE_ERROR' => 'Core Error', 'E_CORE_WARNING' => 'Core Warning', 'E_COMPILE_ERROR' => 'Compile Error', 'E_COMPILE_WARNING' => 'Compile Warning', 
        'E_USER_ERROR' => 'User Error', 'E_USER_WARNING' => 'User Warning', 'E_USER_NOTICE' => 'User Notice', 'E_STRICT' => 'Runtime Notice', 
        'E_RECOVERABLE_ERROR' => 'Catchable Fatal Error', 'E_DEPRECATED' => 'Deprecated Code Warning', 'E_USER_DEPRECATED' => 'User Deprecated Code Warning');
    
    static $errorType = array(1 => 'E_ERROR', 2 => 'E_WARNING', 4 => 'E_PARSE', 8 => 'E_NOTICE', 16 => 'E_CORE_ERROR', 32 => 'E_CORE_WARNING', 64 => 
        'E_COMPILE_ERROR', 128 => 'E_COMPILE_WARNING', 256 => 'E_USER_ERROR', 512 => 'E_USER_WARNING', 1024 => 'E_USER_NOTICE', 2048 => 'E_STRICT', 
        4096 => 'E_RECOVERABLE_ERROR', 8192 => 'E_DEPRECATED', 16384 => 'E_USER_DEPRECATED');
    
    private $dump = '';
    
    protected $context = 0;
    
    /**
     * redefine the constructor, make the message required
     *
     * @param string $message
     * @param integer $code
     */
    function __construct($message, $code = 0, $file = '', $line = -1, $context = 1)
    {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }
    
    /**
     * Set any memory, code dump data to display in the eception error
     *
     * @param string $dump
     */
    function setDump($dump)
    {
        $this->dump = $dump;
    }
    
    /**
     * Redefine if toString()
     *
     * @return string
     */
    function toString($hideTrace = false)
    {
        $str = '';
        if ($this->message != null) {
            $str .= preg_replace("/<a href='(\S+)'>(\S+)<\/a>/", '<a href="http://www.php.net/manual/en/$1.php" target="_blank">$1</a>', $this->message) . "\n";
        }
        
        $str .= "Location:    " . $this->getFile() . " (" . $this->getLine() . ")" . "\n";
        $str .= "Exception:   `" . get_class($this) . "` [{$this->code}]: \n";
        $str .= "Type:        " . self::$errorType[$this->context] . ' (' . self::$errorStr[self::$errorType[$this->context]] . ")\n";
        $str .= "PHP:         " . PHP_VERSION . ' (' . PHP_OS . ")\n";
        if (isset($_SERVER['REQUEST_URI'])) {
            $str .= "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $str .= "Referrer:    " . $_SERVER['HTTP_REFERER'] . "\n";
        }
        if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_ADDR'])) {
            $str .= "Server:      " . ($_SERVER['SERVER_NAME'] != '' ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR']) . "\n";
        }
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $str .= "Client:      " . $_SERVER['REMOTE_ADDR'] . "\n";
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $str .= "User Agent:  " . $_SERVER['HTTP_USER_AGENT'] . "\n";
        }
        $str .= "\n";
        
        if (!$hideTrace) {
            if ($this->dump != null) {
                $str .= $this->dump . "\n\n";
            }
            //$str .= $this->getTraceAsString() . "\n";
            $str .= Tk::traceToString() . "\n";
        }
        return $str;
    }
    
    function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Create a browser safe string for the error
     *
     * @return string
     */
    function toWebString($hideTrace = false)
    {
        $str = $this->toString(true);
        
        if (!$hideTrace) {
            if ($this->dump != null) {
                $str .= $this->dump . "\n\n";
            }
            $str .= htmlentities($this->getTraceAsString()) . "\n";
        }
        return $str;
    }
}

/**
 * Stops Code execution
 *
 * @package Exception
 */
class Tk_ExceptionFatal extends Tk_Exception
{
}

/**
 * RuntimeException is the superclass of those exceptions that can be thrown
 * during normal operation.
 *
 * @package Exception
 */
class Tk_ExceptionRuntime extends Tk_ExceptionFatal
{
}

/**
 * An Illegal Argument Exception.
 *
 * Thrown to indicate that a method has been passed an illegal or
 * inappropriate argument.
 * @package Exception
 */
class Tk_ExceptionIllegalArgument extends Tk_ExceptionRuntime
{
}

/**
 * Thrown to indicate that an index of some sort of variable is out of range.
 *   (Such as to an array, to a string, or to a vector)
 *
 * @package Exception
 */
class Tk_ExceptionIndexOutOfBounds extends Tk_ExceptionRuntime
{
}

/**
 * Thrown to indicate that an index of some sort of variable is of null.
 *
 * @package Exception
 */
class Tk_ExceptionNullPointer extends Tk_ExceptionRuntime
{
}

/**
 * ExceptionLogic, for all logical errors
 *
 * @package Exception
 */
class Tk_ExceptionLogic extends Tk_ExceptionFatal
{
}

/**
 * SqlException, Thrown for all database errors
 *
 * @package Exception
 */
class Tk_ExceptionSql extends Tk_ExceptionFatal
{
}


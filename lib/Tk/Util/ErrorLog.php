<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A log object to log messages to the data/log directory.
 *
 * @package Tk
 * @TODO remove
 * @deprecated
 */
final class Tk_Util_ErrorLog1 extends Tk_Util_Log
{
    
    private static $instance = null;
    
    /**
     * Get the instance of the error log
     *
     * @return Tk_Util_ErrorLog
     */
    static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(Tk_Config::getErrorLog());
        }
        return self::$instance;
    }
    
    /**
     * Log a message to the selected log
     *
     * @param mixed $args Multiple vars retrived using func_get_args()
     * @return Tk_Util_Log
     */
    function log()
    {
        $message = '';
        $objects = func_get_args();
        foreach ($objects as $object) {
            if ($object === null) {
                $objStr = '{NULL}';
            } else if (is_object($object) && method_exists($object, '__toString')) {
                $objStr = $object->__toString();
            } else if (is_object($object) && !method_exists($object, '__toString')) {
                $objStr = str_replace("\0", '|', print_r($object, true));
            } else {
                $objStr = str_replace("\0", '|', $object);
            }
            $message .= $objStr . "\n";
        }
        $this->write($message);
    }

}
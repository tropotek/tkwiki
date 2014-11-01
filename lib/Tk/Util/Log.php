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
 * @deprecated
 * @todo remove
 */
class Tk_Util_Log
{
    
    private $path = '';
    
    /**
     * __construct
     *
     * @param string $path
     */
    function __construct($path)
    {
        $this->path = $path;
        if (!is_dir(dirname($path))) {
            if (!mkdir(dirname($path), 0777, true)) {
                throw new Tk_Exception('Cannot create path to log file: ' . dirname($path));
            }
        }
    }
    
    /**
     * Write a message to the log
     *
     * @param string $message
     * @return Tk_Util_Log
     */
    function write($message)
    {
        $file = fopen($this->path, 'a+');
        fwrite($file, "[" . Tk_Type_Date::createDate()->getIsoDate() . "]\n" . $message . "\n");
        fclose($file);
        return $this;
    }
    
    /**
     * __clone
     *
     */
    function __clone()
    {
        throw new Tk_ExceptionLogic('Cannot clone the log object.');
    }

}
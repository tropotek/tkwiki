<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This is the base class to all Front Controllers.
 *
 * @package Web
 */
abstract class Tk_Web_FrontController extends Tk_Object implements Tk_Util_CommandInterface
{
    
    /**
     * Process the request and response of page requested
     *
     */
    function execute()
    {
        
        try {
            switch (strtoupper(Tk_Request::getInstance()->getMethod())) {
                case 'GET' :
                    $this->doGet();
                    break;
                case 'POST' :
                    $this->doPost();
                    break;
                case 'HEAD' :
                case 'OPTIONS' :
                    break;
                default :
                    Tk::log("Unknown request method `" . Tk_Request::getInstance()->getMethod() . "`.", Tk::LOG_ERROR);
                    break;
            }
            
            Tk::log("End Script, Shutting down...", TK::LOG_INFO);
        } catch (Exception $e) {
            Tk::log($e->__toString(), Tk::LOG_ERROR);
            $this->showFatalError($e);
            exit();
        }
    }
    
    /**
     * doGet
     *
     * @throws Exception
     */
    abstract function doGet();
    
    /**
     * doPost
     *
     * @throws Exception
     */
    abstract function doPost();
    
    /**
     * Displays a fatal error message.
     *
     * @param Exception $e
     */
    abstract function showFatalError(Exception $e);

}
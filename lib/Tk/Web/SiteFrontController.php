<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A web site front controller to take requests and serve a website response.
 *
 * @package Tk
 */
class Tk_Web_SiteFrontController extends Tk_Web_FrontController
{
    
    /**
     * @var array
     */
    protected $controllerChain = array();
    
    /**
     * init
     *
     */
    function init()
    {
    }
    
    /**
     * doPost (execute)
     *
     */
    function doPost()
    {
        $this->executeController('init');
        $this->executeController('execute');
        $this->executeController('postInit');
    }
    
    /**
     * doPost (execute)
     *
     */
    function doGet()
    {
        $this->doPost();
    }
    
    /**
     * postInit
     *
     */
    function postInit()
    {
    }
    
    /**
     * return the array containing the chain of controller
     *
     * @return array
     */
    function getControllerChain()
    {
        return $this->controllerChain;
    }
    
    /**
     * Add a controller to be executed when the front controller is executed
     * 
     * @param Tk_Util_ControllerInterface $controller
     * @return Tk_Util_ControllerInterface
     */
    function addController(Tk_Util_ControllerInterface $controller)
    {
        $this->controllerChain[] = $controller;
        return $controller;
    }
    
    /**
     * Helper method to execute the methods 'execute', 'init', 'postInit' methods
     */
    private function executeController($method)
    {
        if (!preg_match('/^(execute|init|postInit)$/i', $method)) {
            return;
        }
        foreach ($this->controllerChain as $controller) {
            $controller->$method();
        }
    }
    
    /**
     * Display a fatal error.
     *
     * @param Exception $e
     */
    function showFatalError(Exception $e)
    {
        Tk_Response::reset();
        $msg = '';
        if (Tk_Config::isDebugMode()) {
            $msg = wordwrap($e->__toString(), 180);
        } else {
            $msg = "Page Down. \nThe site administrator has been notified of the problem. \n\nPlease check back soon.";
        }
        
        Tk_Response::sendError(Tk_Response::SC_INTERNAL_SERVER_ERROR, $msg);
    }
    
    /**
     * Send a 404 error page.
     *
     */
    function set404()
    {
        Tk_Response::sendError(Tk_Response::SC_NOT_FOUND, "The requested URL " . Tk_Request::getInstance()->getRequestUri()->getPath() . " was not found on this server.");
    }
    
}

<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 * This is to be installed in the index.php just after creation of the front controller:
 *
 * <code>
 *   ...
 *   // Create Site Front Controller
 *   $controller = new Tk_Web_SiteFrontController();
 *   // Enable ajax calls
 *   $controller->addController(new Com_Web_AjaxController());
 *   ...
 * </code>
 *
 * Enable ajax calls '/ajax/Form_Field_Autocomplete_Ajax' URL will call and execute '/lib/Form/Field/Autocomplete/ajax.php' class
 *
 * All Ajax objects must implement the Tk_Util_CommandInterface class
 *
 *
 * @package Com
 */
class Com_Web_AjaxController implements Tk_Util_ControllerInterface
{
    
    
    /**
     * __construct
     *
     */
    function __construct()
    {
        Tk::log('Initalising AJAX Controller', Tk::LOG_INFO);
    }
    
    /**
     * Do all pre-initalisation operations
     * This method called before the execution method
     *
     */
    function init()
    {
        $path = Tk_Request::requestUri()->getPath();
        if (strlen(Tk_Config::getHtdocRoot()) > 1) {
            $path = str_replace(Tk_Config::getHtdocRoot(), '', Tk_Request::requestUri()->getPath());
        }
        
        if ($path[0] != '/') {
        	$path = '/' . $path;
        }
        
        if (!preg_match('/\/ajax\//', $path)) {
        	return; // Not an ajax call
        }
        $class = str_replace(array('/ajax/', '/', '\\'), '', $path);
        
        if (!class_exists($class)) {
        	Tk::log('AJAX: [' . $class . '] - Cannot locate Class', Tk::LOG_ALERT);
        	return;
        }
        
        Tk::log('AJAX: [' . $path . '] Executing...');
        $obj = new $class();
        if (!$obj instanceof Tk_Util_CommandInterface) {
        	Tk::log('AJAX: [' . $class . '] - Class not an instance of Tk_Util_CommandInterface', Tk::LOG_ALERT);
        	return;
        }
        $obj->execute();
        Tk::log('AJAX: [' . $path . '] Execution time (sec): ' . Tk::scriptDuration());
        exit();
    }
    
    /**
     * Execute the controller
     *
     */
    function execute() { }
    
    /**
     * Do all post initalisation operations here
     * This method called after the execute method
     *
     */
    function postInit() { }
    
}
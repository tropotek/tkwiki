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
class Tk_Web_WsFrontController extends Tk_Web_FrontController
{
    
    /**
     * @var string
     */
    protected $classPrepend = 'Ext_Ws_';
    
    /**
     * __construct
     *
     * @param string $classPrepend
     */
    function __construct($classPrepend = 'Ext_Ws_')
    {
        
        $this->classPrepend = $classPrepend;
    }
    
    /**
     * doPost (execute)
     *
     */
    function doPost()
    {
        $service = $class = '';
        $regs = array();
        $version = '1.0';
        
        if (!Tk_Request::getInstance()->getRawPostData()) {
            throw new Tk_ExceptionLogic('Empty query string');
        }
        $xmlRequest = new Tk_Xml_Result(Tk_Request::getInstance()->getRawPostData());
        
        if ($xmlRequest->getDomDocument()->documentElement->hasAttribute('version')) {
            $version = $xmlRequest->getDomDocument()->documentElement->getAttribute('version');
        }
        
        $service = '';
        $class = '';
        if ($version < '2.0') {
            preg_match('/<([0-9A-Za-z_-]+)Request[^0-9A-Za-z_-]/', Tk_Request::getInstance()->getRawPostData(), $regs);
            $service = $regs[1];
            $class = $this->classPrepend . $service;
        }
        if ($version >= '2.0') {
            $service = $this->getFirstChild($xmlRequest->getDomDocument()->documentElement)->nodeName;
            $class = $this->classPrepend . ucfirst($service);
        }
        
        if (!class_exists($class)) {
            $msg = "Unknown request for `{$service}'.";
            throw new Tk_ExceptionRuntime($msg, 1000);
        } else {
            /* @var $cmd Tk_Xml_Server */
            $cmd = new $class();
            $cmd->execute($xmlRequest);
        }
    }
    
    /**
     * Return the nodes first child DOMElement not node.
     *
     * @param DOMElement $node
     * @return DOMElement
     */
    protected function getFirstChild(DOMNode $node)
    {
        if ($node->nodeType != XML_ELEMENT_NODE) {
            return;
        }
        foreach ($node->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                return $child;
            }
        }
    }
    
    /**
     * doGet
     *
     * @throws Exception
     */
    function doGet()
    {
        $url = new Tk_Type_Url('/docs/index.html');
        if (is_file($url->toString())) {
            $url->redirect();
        }
        throw new Tk_ExceptionRuntime('This service does not accept GET requests.');
    }
    
    /**
     * showFatalError
     *
     * @param Exception $e
     */
    function showFatalError(Exception $e)
    {
        $code = intval($e->getCode());
        if ($code == 0) {
            $code = 1000;
        }
        Tk_Response::reset();
        Tk_Response::write(sprintf('<?xml version="1.0" encoding="utf-8"?>
<Response>
  <ErrorResponse>
    <ErrorCode>%s</ErrorCode>
    <ErrorMessage>%s</ErrorMessage>
  </ErrorResponse>
</Response>
', htmlspecialchars($code, ENT_COMPAT, 'UTF-8'), htmlspecialchars($e->getMessage(), ENT_COMPAT, 'UTF-8')));
        Tk_Response::flush();
    }
    
}
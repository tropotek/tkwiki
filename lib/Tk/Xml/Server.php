<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An XML webservice base
 *
 *
 * @package Tk
 */
abstract class Tk_Xml_Server extends Tk_Object implements Tk_Util_CommandInterface
{
    /**
     * @var string
     */
    protected $encoding = 'utf-8';
    
    /**
     * @var Tk_Xml_Result
     */
    private $xmlRequest = null;
    
    /**
     * Main execution thread of the webservice
     *
     * @param Tk_Xml_Result $xmlRequest
     */
    function execute($xmlRequest = null)
    {
        if (!$xmlRequest instanceof Tk_Xml_Result) {
            $xmlRequest = new Tk_Xml_Result(Tk_Request::getInstance()->getRawPostData());
        }
        $this->xmlRequest = $xmlRequest;
        Tk_Response::getInstance()->addHeader('Content-Type', 'text/xml; charset=' . $this->encoding);
        Tk_Response::getInstance()->write('<?xml version="1.0" encoding="' . $this->encoding . '"?>' . "\n");
        /* Tk_Response::getInstance()->write('<?xml version="1.0"?>' . "\n"); */
        $this->process();
    }
    
    /**
     * Implement this method in your own webservice objects.
     *
     */
    abstract protected function process();
    
    /**
     * Get the stdClass object of the post XML request
     *
     * @return stdClass
     */
    function getXmlRequestObj()
    {
        return $this->xmlRequest->getXmlObj();
    }
    
    /**
     * Get the post XML request result object
     *
     * @return Tk_Xml_Result
     */
    function getXmlRequestResult()
    {
        return $this->xmlRequest;
    }
    
    /**
     * encode string to put into xml document
     *
     * @param string $value
     * @return string
     */
    protected function encode($value)
    {
        return htmlspecialchars($value, ENT_COMPAT, $this->encoding);
    }
    
    /**
     * Convert a string to its boolean equivalent
     *  o 'true' = true
     *  o '1' = true
     *
     * @param string $str
     * @return string
     */
    protected function strToBoolean($str)
    {
        if ($str == 'true' || $str == '1') {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Set the encoding for the response...
     * EG:
     *  o UTF-8 (defult)
     *  o UTF-16
     *  o ISO-8859-1
     *
     * @param string $encoding
     */
    function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }
    
    /**
     * Get teh response encoding type
     *
     * @return string
     */
    function getEncoding()
    {
        return $this->encoding;
    }
    
    /**
     * Get the full list of received headers with the request
     *
     * @return array
     */
    function getHeaderList()
    {
        return apache_request_headers();
    }
    
    /**
     * Get a header value by its name
     *
     * @param string $name
     * @return string Returns null if not exist
     */
    function getHeader($name)
    {
        $headers = $this->getHeaderList();
        if (isset($headers[$name])) {
            return $headers[$name];
        }
    }
}
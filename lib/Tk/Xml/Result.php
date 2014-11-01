<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The xml webservice result object.
 * This object takes XML and converts it to a PHP stdClass object to make it
 * easy for iteration without traversing the DOM tree continually
 *
 * @package Tk
 */
class Tk_Xml_Result extends Tk_Object
{
    /**
     * @var DOMDocument
     */
    private $dom = null;
    
    /**
     * @var string
     */
    private $xml = '';
    
    /**
     * @var string
     */
    private $xmlObject = null;
    
    /**
     * __construct
     *
     * @param string $xml
     */
    function __construct($xml)
    {
        $pos = strpos($xml, '<?xml ');
        $this->xml = substr($xml, $pos);
        $this->dom = $this->createDom($this->xml);
        $this->xmlObject = $this->domToStdClass($this->dom->documentElement);
    
    }
    
    /**
     * Create a DomDocument object from XML string
     *
     * @param string $xmlStr
     * @return DomDocument
     */
    private function createDom($xmlStr)
    {
        $hash = md5($xmlStr);
        //file_put_contents(Tk_Config::getSitePath() . "/../log/$hash.xml", $xmlStr);
        $dom = new DOMDocument();
        $dom->loadXML($xmlStr);
        if ($dom == null || $dom->documentElement == null) {
            $e = new Tk_Exception("Invalid XML cannot convert XML string to DOM.");
            $e->setDump($xmlStr);
            throw $e;
        }
        return $dom;
    }
    
    /**
     * Convert a dom node and its children to a stdClass object
     *
     * @param DOMNode $node
     * @return stdClass
     */
    private function domToStdClass(DOMNode $node)
    {
        //$obj = simplexml_load_string()
        $node->normalize();
        if ($node->firstChild != null) {
            if ($node->childNodes->length == 1 && $node->firstChild->nodeType == XML_TEXT_NODE) {
                return trim($node->firstChild->nodeValue);
            }
        } else {
            return null;
        }
        $obj = new stdClass();
        $children = $node->childNodes;
        foreach ($children as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $property = $child->nodeName;
                $value = $this->domToStdClass($child);
                if (isset($obj->$property)) {
                    if (!is_array($obj->$property)) {
                        $tmp = $obj->$property;
                        $obj->$property = array();
                        $obj->{$property}[] = $tmp;
                    }
                    $obj->{$property}[] = $value;
                } else {
                    $obj->$property = $value;
                }
            }
        }
        return $obj;
    }
    
    /**
     * Get a stdClass object representation of the XML
     *
     * @return stdClass
     */
    function getXmlObj()
    {
        return $this->xmlObject;
    }
    
    /**
     * get the raw xml string
     *
     * @return string
     */
    function getXmlString()
    {
        return $this->xml;
    }
    
    /**
     * Get a dom document of the XML
     *
     * @return DOMDocument
     */
    function getDomDocument()
    {
        $dom = new DOMDocument();
        $dom->loadXML($this->xml);
        if ($dom != null) {
            return $dom;
        }
    }
    
    /* NOTICE:
     * The following function are usefull for errors generated in the following format:
     * <?xml version="1.0" encoding="utf-8"?>
     * <Response>
     *   <ErrorResponse>
     *     <ErrorCode>1000</ErrorCode>
     *     <ErrorMessage>Unknown request `Request'.</ErrorMessage>
     *   </ErrorResponse>
     * </Response>
     */
    
    /**
     * Test if the xml contains an error message
     *
     * @return boolean
     */
    function isError()
    {
        if (isset($this->getXmlObj()->ErrorResponse)) {
            return true;
        }
        return false;
    }
    
    /**
     * Test if the xml contains an error message
     *
     * @return Tk_Exception Or null if no error
     */
    function getError()
    {
        if (isset($this->getXmlObj()->ErrorResponse)) {
            return new Tk_Exception($this->getXmlObj()->ErrorResponse->ErrorMessage, $this->getXmlObj()->ErrorResponse->ErrorCode);
        }
    }

}
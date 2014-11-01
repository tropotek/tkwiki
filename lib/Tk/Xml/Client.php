<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 *
 * NOTE: A Get request does not use the  $requestBody variable
 *
 * @package Tk
 */
class Tk_Xml_Client extends Tk_Object
{
    /**
     * @var Tk_Type_Url
     */
    private $xmlServiceUrl = null;
    
    /**
     * @var string
     */
    private $responseXml = '';
    
    /**
     * HTTP Request timeout in secs
     * @var integer
     */
    private $timeout = 15;
    
    
    /**
     * __construct
     *
     * @param Tk_Type_Url $xmlServiceUrl
     */
    function __construct(Tk_Type_Url $xmlServiceUrl)
    {
        $this->xmlServiceUrl = $xmlServiceUrl;
    }
    
    
    
    /**
     * Get
     *
     * @param Tk_Type_Url $url
     * @param array $headers
     * @return Tk_Xml_Result
     */
    static function get($url, $headers = array())
    {
    	$client = new self($url);
    	return $client->sendXmlRequest('', $headers, 'GET');
    }
    
    /**
     * Post
     *
     * @param Tk_Type_Url $url
     * @param string $requestBody
     * @param array $headers
     * @return Tk_Xml_Result
     */
    static function post($url, $requestBody = '', $headers = array())
    {
    	  $client = new self($url);
        return $client->sendXmlRequest($requestBody, $headers);
    }
    
    
    
    /**
     * Send a request and recive a response from an xml service
     *
     * @param string $requestBody
     * @param array $headers
     * @param striung $method One of 'GET' or 'POST'
     * @return Tk_Xml_Result
     */
    function sendXmlRequest($requestBody = '', $headers = array(), $method = 'POST')
    {
        $curl = new Tk_Util_Curl();
        $curl->setTimeout($this->timeout);
        $curl->setHeader("Content-type: text/xml; charset=UTF-8");
        foreach ($headers as $v) {
            $v = trim($v);
            if ($v) {
                $curl->setHeader($v);
            }
        }
        if (strtolower(trim($requestBody)) !== 'post') {
            $this->responseXml = $curl->post($this->xmlServiceUrl, $requestBody);
        } else {
        	$this->responseXml = $curl->get($this->xmlServiceUrl);
        }
        
        $result = new Tk_Xml_Result($this->getResponseXml());
        return $result;
    }
    
    /**
     * Return the last request's response XML
     * Returns an empty string if no request made yet
     * @return string
     */
    function getResponseXml()
    {
        return trim($this->responseXml);
    }
    
    /**
     * Send a request from a DOM xml document
     * This method assumes the code is in the format:
     * <code>
     *   <?xml version="1.0"?>
     *   <Request>
     *     <PingRequest>...</PingRequest>
     *   </Request>
     * </code>
     *
     *
     * @param DOMDocument $doc
     * @param array $headers
     * @return Tk_Xml_Result
     */
    function sendXmlDomRequest(DOMDocument $doc, $headers = array())
    {
        $xmlStr = $doc->saveXML();
        $result = $this->sendXmlRequest($xmlStr, $headers);
        return $result;
    }
    
    /**
     * Set the query timeout in seconds
     *
     * @param integer $i
     */
    function setTimeout($i)
    {
        $this->timeout = intval($i);
    }
    
    
    
}
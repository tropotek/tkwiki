<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The interface for all NodeModifier type classes
 *
 *
 * @package Com
 */
interface Com_Web_NodeModifierInterface
{



    /**
     * pre init the front controller
     *
     * @param DOMDocument $doc
     */
    function init($doc);
    
    /**
     * Post init the front controller
     *
     * @param DOMDocument $doc
     */
    function postInit($doc);
    
    
    /**
     * The code to perform any modification to the node goes here.
     *
     * @param DOMElement $node
     */
    function executeNode(DOMElement $node);

}
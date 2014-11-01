<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A holder for module node metadata pulled from a template src.
 *
 * Metadata nodes can also be created for content areas via the com frontcontroller
 * these will be added to the var named '_Module_Content_' in a page
 *
 * @package Com
 */
class Com_Web_MetaData extends Tk_Object
{
    
    /**
     * @var string
     */
    protected $classname = '';
    
    /**
     * @var array
     */
    protected $parameters = array();
    
    /**
     * @var string
     */
    protected $insertVar = '';
    
    /**
     * @var DOMDocument
     */
    private $inlineDom = null;
    
    
    /**
     * __construct
     *
     * @param DOMElement $node
     */
    function __construct($node = null)
    {
        static $idx = 0;
        $this->id = $idx++;
        if ($node instanceof DOMElement) {
            // collect component classname
            $this->classname = $node->getAttribute('com-class');
            $node->removeAttribute('com-class');
            
            // collect component insertId (Default: com-class)
            $this->insertVar = $node;
//            $this->insertVar = $this->classname;
//            if ($node->hasAttribute('var')) {
//                $this->insertVar = $node->getAttribute('var');
//            }
            
            //  Collect component parameters
            $this->parameters = array();
            $attList = $node->attributes;
            foreach ($attList as $att) {
                if (substr($att->nodeName, 0, 6) == 'param-') {
                    $this->parameters[substr($att->nodeName, 6)] = $att->nodeValue;
                }
            }
            foreach (array_keys($this->parameters) as $name) {
                $node->removeAttribute('param-' . $name);
            }
            
            // NOTE: If the calling component tag has child elements then
            // it is assumed that we are using an inline template
            if ($this->hasChildElements($node)) {
                $tmpDoc = new DOMDocument();
                $newNode = $tmpDoc->importNode($node, true);
                $tmpDoc->appendChild($newNode);
                $this->inlineDom = $tmpDoc;
            }
        }
    }
    
    /**
     * does the node contain child elements
     *
     * @param DOMElement $node
     * @return boolean
     */
    private function hasChildElements($node)
    {
        $children = $node->childNodes;
        foreach ($children as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get the objects classname.
     *
     * @return string
     */
    function getClassname()
    {
        return $this->classname;
    }
    
    /**
     * Get the parameters array.
     *
     * @return array
     */
    function getParameters()
    {
        return $this->parameters;
    }
    
    /**
     * Get the insert var for this component into its parent
     *
     * @return string
     */
    function getInsertVar()
    {
        return $this->insertVar;
    }
    
    /**
     * Get the inline template data
     *
     * @return DOMDocument
     */
    function getInlineDom()
    {
        return $this->inlineDom;
    }

}


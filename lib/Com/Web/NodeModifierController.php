<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This class is designed to take a DOMDocument, traverse it and pass each Element to
 * the child Dom iterator. This can be extended by adding DomIteratorInterface objects
 * to the object.
 *
 * NOTE: Using this will affect page loading performance as it traverses
 *   the entire document.
 *
 * Example:
 * <code>
 * <?php
 *
 * $modifier = new Com_Web_NodeModifier();
 * $modifier->add(new Sdk_Web_NodeModifierPath());
 * $modifier->execute();
 *
 * ?>
 * </code>
 *
 *
 * @package Com
 */
class Com_Web_NodeModifierController extends Tk_Object implements Tk_Util_CommandInterface
{
    /**
     * @var array
     */
    private $modifierList = array();
    
    /**
     * @var Com_Web_ComponentController
     */
    protected $comCon = null;
    
    /**
     * __construct
     *
     * @param Com_Web_ComponentController $comCon
     */
    function __construct(Com_Web_ComponentController $comCon)
    {
        $this->comCon = $comCon;
    }
    
    /**
     * Enter description here...
     *
     * @param Com_Web_NodeModifierInterface $mod
     */
    function add(Com_Web_NodeModifierInterface $mod)
    {
        $this->modifierList[] = $mod;
    }
    
    /**
     * Remove a modifier from the execution list
     *
     * @param Com_Web_NodeModifierInterface $mod
     */
    function remove(Com_Web_NodeModifierInterface $mod)
    {
        foreach ($this->modifierList as $i => $lMod) {
            if ($lMod == $mod) {
                unset($this->modifierList[$i]);
            }
        }
    }
    
    /**
     * pre init the front controller
     *
     */
    function init()
    {
    }
    
    /**
     * Call this method to travers a document
     *
     */
    function execute()
    {
        $doc = $this->comCon->getPageComponent()->getTemplate()->getDocument(true);
        if ($doc) {
            foreach ($this->modifierList as $mod) {
                $mod->init($doc);
            }
            $this->traverse($doc->documentElement);
            foreach ($this->modifierList as $mod) {
                $mod->postInit($doc);
            }
        }
    
    }
    
    /**
     * Post init the front controller
     *
     */
    function postInit()
    {
    
    }
    
    /**
     * Traverse a document converting element attributes.
     *
     * @param DOMNode $node
     */
    private function traverse(DOMNode $node)
    {
        if ($node->nodeType == XML_ELEMENT_NODE) {
            /* @var $iterator Com_Web_NodeModifierInterface */
            foreach ($this->modifierList as $mod) {
                $mod->executeNode($node);
            }
            
            $children = $node->childNodes;
            foreach ($children as $child) {
                $this->traverse($child);
            }
        }
    }

}
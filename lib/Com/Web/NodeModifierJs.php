<?php

/*
 * This file is part of the DkLib.
 *   You can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Convert all relative paths to full path url's
 *
 * @package Com
 */
class Com_Web_NodeModifierJs extends Tk_Object implements Com_Web_NodeModifierInterface
{
    
    private $head = null;
    
    private $scriptNodes = array();
    
    /**
     * __construct
     *
     */
    function __construct()
    {
        
    }
    
    /**
     * Call this method to travers a document
     *
     * @param DOMElement $node
     */
    function executeNode(DOMElement $node)
    {
        if ($node->nodeName == 'head') {
            $this->head = $node;
            return;
        }
        if ($node->parentNode === $this->head && $node->nodeName == 'script') {
            $this->scriptNodes[] = $node;
        }
        
    }
    


    /**
     * pre init the front controller
     *
     * @param DOMDocument $doc
     */
    function init($doc) {}
    
    /**
     * Post init the front controller
     *
     * @param DOMDocument $doc
     */
    function postInit($doc)
    {
        $jsSrc = '';
        foreach ($this->scriptNodes as $node) {
            if ($node->hasAttribute('src')) {
                $path = Tk_Type_Url::create($node->getAttribute('src'))->getPath();
                $path = Tk_Config::getSitePath() . str_replace(Tk_Config::getHtdocRoot(), '', $path);
                if (!preg_match('\min\.js', basename($path)) && is_file($path)) {
                    $jsSrc .= file_get_contents($path) . "\n";
                }
            } else {
                $jsSrc .= $node->nodeValue . "\n";
            }
            $node->parentNode->removeChild($node);
        }
        $jsMin = Js_Min::minify($jsSrc);
        
        $text = $doc->createTextNode($jsMin);
        $node = $doc->createElement('script');
        $node->appendChild($text);
        $node->setAttribute('type', 'text/javascript');
        $this->head->appendChild($node);
        
        // TODO: Using to much memory with this find a way to ninimise mem usage,
        //  maybe we need to cache it or something...
        
    }
    
    
}
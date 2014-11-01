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
class Com_Web_NodeModifierPath extends Tk_Object implements Com_Web_NodeModifierInterface
{
    
    /**
     * @var string
     */
    private $newPath = '';
    
    /**
     * @var array
     */
    //private $attrSrc = array('src', 'href', 'action', 'background', 'archive');
    private $attrSrc = array('src', 'href', 'action', 'background');
    
    /**
     * @var array
     */
    private $attrJs = array('onmouseover', 'onmouseup', 'onmousedown', 'onmousemove', 'onmouseover', 'onclick');
    
    /**
     * __construct
     *
     */
    function __construct()
    {
        $url = new Tk_Type_Url('/');
        $port = '';
        if ($url->getPort() != 80) {
        	$port = ':' . $url->getPort();
        }
        $this->newPath = $url->getScheme() . '://' . $url->getHost() . $port . Tk_Type_Url::$pathPrefix;
        if (substr($this->newPath, -1) == '/') {
            $this->newPath = substr($this->newPath, 0, -1);
        }
    }
    
    /**
     * Call this method to travers a document
     *
     * @param DOMElement $node
     */
    function executeNode(DOMElement $node)
    {
        if ($this->newPath == '') {
            return;
        }
        if ($node->hasAttribute('rel') && preg_match('/(norel)/', $node->getAttribute('rel'))) {
            return;
        }
        
        // Modify local paths to full path url's
        foreach ($node->attributes as $attr) {
            if (in_array(strtolower($attr->nodeName), $this->attrSrc)) {
                if (preg_match('/^(http|ftp|news|gopher|file|#|javascript|mailto|page)/', $attr->value) || preg_match('/^' . preg_quote($this->newPath, '/') . '/', urldecode($attr->value)) ) {
                    // NOTE: To fix firefox hash bug, where it redirects to itself.
                    if (preg_match('/^#$/', $attr->value)) {
                        $attr->value = 'javascript:;';
                    }
                    break;
                }
                $str = $this->prependPath($attr->value);
                $attr->value = htmlentities($str);
            } elseif (in_array(strtolower($attr->nodeName), $this->attrJs)) {
                $str = $attr->value;
                $str = str_replace("'/", "'" . $this->newPath . '/', $str);
                $attr->value = htmlentities($str);
            }
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
    function postInit($doc) {}
    
    
    /**
     * Prepend the path to a relative link on the page
     *
     * @param string $path
     */
    private function prependPath($path)
    {
        if (substr($path, 0, 1) == '/') {
            $path = $this->newPath . $path;
        } else {
            $path = $this->newPath . '/' . $path;
        }
        return $path;
    }

}
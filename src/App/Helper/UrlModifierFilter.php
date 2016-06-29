<?php
namespace App\Helper;


/**
 * Convert all wiki page://title urls to real urls the browser understands
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class UrlModifierFilter extends \Dom\Modifier\Filter\Iface
{

    /**
     * Execute code on the current Node
     *
     * @param \DOMElement $node
     */
    public function executeNode(\DOMElement $node)
    {
        // Modify local paths to full path url's
        foreach ($node->attributes as $attr) {
            if (in_array(strtolower($attr->nodeName), array('href'))) {
                if (preg_match('/^page:\/\/(.*)/', $attr->value, $regs)) {
                    if (isset($regs[1])) {
                        $url = \Tk\Url::create('/'.$regs[1]);
                        $attr->value = $url->getPath();
                    }
                }
            }
        }
    }

    /**
     * pre init the front controller
     *
     * @param \DOMDocument $doc
     */
    function init($doc)
    {
        // TODO: Implement init() method.
    }
}
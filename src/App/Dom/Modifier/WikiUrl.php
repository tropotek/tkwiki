<?php
namespace App\Dom\Modifier;

use Dom\Mvc\Modifier\FilterInterface;

/**
 * Convert all wiki page://title urls to real urls the browser understands
 */
class WikiUrl extends FilterInterface
{

    public function executeNode(\DOMElement $node): void
    {
        // Modify wiki URLs to actual public URLs
        foreach ($node->attributes as $attr) {
            if (strtolower($attr->nodeName) == 'href') {
                if (preg_match('/^page:\/\/(.*)/', $attr->value, $regs)) {
                    if (isset($regs[1])) {
                        $url = \Tk\Uri::create('/'.$regs[1]);
                        $attr->value = $url->getPath();
                    }
                }
            }
        }
    }

    function init(\DOMDocument $doc) { }
}
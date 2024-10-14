<?php
namespace App\Dom\Modifier;

use Dom\Modifier\ModifierInterface;

class WikiImg extends ModifierInterface
{

    protected array $found = [];

    public function executeNode(\DOMElement $node): void
    {
        if ($node->nodeName != 'img') return;

        $css = $node->getAttribute('class');
        $css = $this->addClass($css, 'wk-image');
        $node->setAttribute('class', $css);
    }

    function init(\DOMDocument $doc): void { }

}
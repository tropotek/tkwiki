<?php
namespace App\Dom\Modifier;

use App\Db\Secret;
use App\Helper\ViewSecret;
use Bs\Traits\SystemTrait;
use Dom\Modifier\FilterInterface;

/**
 * Convert all secret image modules set in the WYSIWYG editor
 */
class Secrets extends FilterInterface
{
    use SystemTrait;

    function init(\DOMDocument $doc) { }

    public function executeNode(\DOMElement $node): void
    {
        if ($node->nodeName != 'img') return;
        if (!$node->hasAttribute('wk-secret')) return;

        $secret = Secret::find((int)$node->getAttribute('wk-secret'));
        if (!$secret) return;

        $renderer = new ViewSecret($secret);
        $template = $renderer->show();
        $newNode = $node->ownerDocument->importNode($template->getDocument()->documentElement, true);
        $node->parentNode->insertBefore($newNode, $node);

        $this->getDomModifier()->removeNode($node);

    }

}
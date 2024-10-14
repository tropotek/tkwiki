<?php
namespace App\Dom\Modifier;

use App\Db\Secret;
use App\Helper\ViewSecret;
use Bs\Traits\SystemTrait;
use Dom\Modifier\ModifierInterface;

/**
 * Convert all secret image modules set in the WYSIWYG editor
 */
class Secrets extends ModifierInterface
{
    use SystemTrait;

    function init(\DOMDocument $doc): void { }

    public function executeNode(\DOMElement $node): void
    {
        if ($node->nodeName != 'img') return;
        if (!$node->hasAttribute('data-secret-hash')) return;

        $secret = Secret::findByHash($node->getAttribute('data-secret-hash'));
        if (!$secret) return;

        $renderer = new ViewSecret($secret);
        $template = $renderer->show();
        $newNode = $node->ownerDocument->importNode($template->getDocument()->documentElement, true);
        $node->parentNode->insertBefore($newNode, $node);

        $this->getDomModifier()->removeNode($node);

    }

}
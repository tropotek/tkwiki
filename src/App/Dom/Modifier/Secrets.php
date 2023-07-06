<?php
namespace App\Dom\Modifier;

use App\Db\Secret;
use App\Db\SecretMap;
use App\Helper\ViewSecret;
use Dom\Mvc\Modifier\FilterInterface;
use Tk\Traits\SystemTrait;

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

        /** @var Secret $secret */
        $secret = SecretMap::create()->find($node->getAttribute('wk-secret'));

        if (!$secret) return;
        if ($secret->canView($this->getFactory()->getAuthUser())) {
            $renderer = new ViewSecret($secret);
            $template = $renderer->show();
            $newNode = $node->ownerDocument->importNode($template->getDocument()->documentElement, true);
            $node->parentNode->insertBefore($newNode, $node);
        }
        $this->getDomModifier()->removeNode($node);

    }

}
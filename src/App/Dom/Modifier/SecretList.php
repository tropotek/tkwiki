<?php
namespace App\Dom\Modifier;

use App\Helper\ViewSecretList;
use App\Db\User;
use Dom\Modifier\FilterInterface;

/**
 * Convert all secret list div modules set in the WYSIWYG editor
 * This will display a list of all secret records the user has permission to see
 */
class SecretList extends FilterInterface
{

    function init(\DOMDocument $doc) { }

    public function executeNode(\DOMElement $node): void
    {
        if ($node->nodeName != 'div') return;
        if (!$node->getAttribute('wk-secret-list')) return;

        /** @var User $user */
        $user = User::find((int)$node->getAttribute('wk-secret-list'));
        if (!$user) return;

        // TODO: I do not think the user ID is needed here....
        $renderer = new ViewSecretList($user);
        $template = $renderer->show();
        $newNode = $node->ownerDocument->importNode($template->getDocument()->documentElement, true);
        $node->parentNode->insertBefore($newNode, $node);
        $this->getDomModifier()->removeNode($node);

    }

}
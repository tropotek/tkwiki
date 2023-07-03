<?php
namespace App\Dom\Modifier;

use App\Db\User;
use App\Db\UserMap;
use App\Helper\ViewSecretList;
use Dom\Mvc\Modifier\FilterInterface;
use Tk\Traits\SystemTrait;

/**
 *
 */
class SecretList extends FilterInterface
{
    use SystemTrait;

    function init(\DOMDocument $doc) { }

    public function executeNode(\DOMElement $node): void
    {
        if ($node->nodeName != 'div') return;
        if ($node->getAttribute('wk-secret-list')) {
            /** @var User $user */
            $user = UserMap::create()->find((int)$node->getAttribute('wk-secret-list'));
            if (!$user) return;

            $renderer = new ViewSecretList($user);
            $template = $renderer->show();
            $newNode = $node->ownerDocument->importNode($template->getDocument()->documentElement, true);
            $node->parentNode->replaceChild($newNode, $node);
        }
    }

}
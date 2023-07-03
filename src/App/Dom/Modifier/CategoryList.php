<?php
namespace App\Dom\Modifier;

use App\Db\User;
use App\Db\UserMap;
use App\Helper\ViewCategoryList;
use App\Helper\ViewSecretList;
use Dom\Mvc\Modifier\FilterInterface;
use Tk\Traits\SystemTrait;

/**
 *
 */
class CategoryList extends FilterInterface
{
    use SystemTrait;

    function init(\DOMDocument $doc) { }

    public function executeNode(\DOMElement $node): void
    {
        if ($node->nodeName != 'div') return;
        if ($node->getAttribute('wk-category-list')) {
            $renderer = new ViewCategoryList(
                $node->getAttribute('wk-category-list'),
                (bool)$node->getAttribute('data-table')
            );
            $template = $renderer->show();
            $newNode = $node->ownerDocument->importNode($template->getDocument()->documentElement, true);
            $node->parentNode->replaceChild($newNode, $node);
        }
    }

}
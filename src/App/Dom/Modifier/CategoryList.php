<?php
namespace App\Dom\Modifier;

use App\Helper\ViewCategoryList;
use Dom\Modifier\FilterInterface;
use Tk\Traits\SystemTrait;

/**
 * Convert all page category liting modules set in the WYSIWYG editor.
 * This will display a list of all pages (with permissions) within a category
 */
class CategoryList extends FilterInterface
{
    use SystemTrait;

    function init(\DOMDocument $doc) { }

    public function executeNode(\DOMElement $node): void
    {
        if ($node->nodeName != 'div') return;
        if (!$node->hasAttribute('wk-category-list')) return;
        $renderer = new ViewCategoryList(
            $node->getAttribute('wk-category-list'),
            (bool)$node->getAttribute('data-table')
        );
        $template = $renderer->show();
        $newNode = $node->ownerDocument->importNode($template->getDocument()->documentElement, true);
        $node->parentNode->insertBefore($newNode, $node);
        $this->getDomModifier()->removeNode($node);
    }

}
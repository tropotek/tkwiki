<?php
namespace App\Controller\Menu;

use App\Db\MenuItem;
use App\Db\MenuItemMap;
use App\Db\Page;
use Dom\Template;
use Tk\Db\Tool;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * An object to manage and display the wiki Page header
 * information and action buttons.
 */
class View extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use SystemTrait;


    public function __construct(Template $template)
    {
        $this->setTemplate($template);
    }


    public function show(): ?Template
    {
        $template = $this->getTemplate();
        if (!$template->getRepeat('dropdown')) return $template;

        // Order in DESC because we are prepending elements to the ul menu
        $items = MenuItemMap::create()->findByParentId(0, Tool::create('order_id DESC'));
        foreach ($items as $item) {
            if ($item->hasChildren() && $item->isType(MenuItem::TYPE_DROPDOWN)) {
                // Normal order here as we are appending to the sub menu ul
                $children = MenuItemMap::create()->findByParentId($item->getId(), Tool::create('order_id'));
                $dropdown = $template->getRepeat('dropdown');
                $this->showItem($dropdown, $item);
                foreach ($children as $child) {
                    if ($child->isType(MenuItem::TYPE_DIVIDER)) {
                        $row = $dropdown->getRepeat('divider');
                        $row->appendRepeat('dropdown-menu');
                        continue;
                    }
                    $row = $dropdown->getRepeat('dropdown-item');
                    $this->showItem($row, $child);
                    $row->appendRepeat('dropdown-menu');
                }
                $dropdown->prependRepeat('navbar');
            } elseif ($item->isType(MenuItem::TYPE_ITEM) || $item->isType(MenuItem::TYPE_DROPDOWN)) {
                $row = $template->getRepeat('nav-item');
                $this->showItem($row, $item);
                $row->prependRepeat('navbar');
            }
        }

        return $template;
    }

    private function showItem(Template $t, MenuItem $item): void
    {
        $user = $this->getFactory()->getAuthUser();
        $page = $item->getPage();
        $t->setText('name', $item->getName());
        if ($page) {
            $t->setAttr('name', 'href', $page->getPageUrl());
            if (!$page->canView($user)) {
                $t->setAttr('name', 'href', Uri::create(Page::getHomeUrl()));
                $t->addCss('name', 'disabled');
            }
        }

    }

}

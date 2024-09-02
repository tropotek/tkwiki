<?php
namespace App\Controller\Menu;

use App\Db\MenuItem;
use App\Db\Page;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Traits\SystemTrait;
use Tk\Uri;
use Tt\Db;

/**
 * An object to manage and display the wiki Page header
 * information and action buttons.
 */
class View extends Renderer implements DisplayInterface
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
        $items = MenuItem::findByParentId(0);
        $items = array_reverse($items);

        foreach ($items as $item) {
            if ($item->hasChildren() && $item->isType(MenuItem::TYPE_DROPDOWN)) {
                // Normal order here as we are appending to the sub menu ul
                $children = MenuItem::findByParentId($item->menuItemId);
                $dropdown = $template->getRepeat('dropdown');
                $this->showItem($dropdown, $item);
                $count = 0;
                foreach ($children as $child) {
                    if ($child->isType(MenuItem::TYPE_DIVIDER)) {
                        $row = $dropdown->getRepeat('divider');
                        $row->appendRepeat('dropdown-menu');
                        continue;
                    }
                    $row = $dropdown->getRepeat('dropdown-item');
                    if ($this->showItem($row, $child)) {
                        $row->appendRepeat('dropdown-menu');
                        $count++;
                    }
                }
                if ($count) {
                    $dropdown->prependRepeat('navbar');
                }
            } elseif ($item->isType(MenuItem::TYPE_ITEM) || $item->isType(MenuItem::TYPE_DROPDOWN)) {
                $row = $template->getRepeat('nav-item');
                if ($this->showItem($row, $item)) {
                    $row->prependRepeat('navbar');
                }
            }
        }

        return $template;
    }

    /**
     * @return bool Returns true if the user can view the page
     */
    private function showItem(Template $t, MenuItem $item): bool
    {
        $user = $this->getFactory()->getAuthUser();
        $page = $item->getPage();
        $t->setText('name', $item->name);
        if ($page) {
            $t->setAttr('name', 'href', $page->getPageUrl());
            if (!($page->canView($user) && $page->published)) {
                $t->setAttr('name', 'href', Uri::create(Page::getHome()));
                $t->addCss('name', 'disabled');
                return false;
            }
        }
        return true;
    }

}

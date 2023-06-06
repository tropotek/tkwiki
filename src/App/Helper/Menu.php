<?php
namespace App\Helper;

use App\Db\MenuItemMap;
use Dom\Template;
use Tk\Db\Tool;
use Tk\Traits\SystemTrait;

/**
 * An object to manage and display the wiki Page header
 * information and action buttons.
 */
class Menu extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use SystemTrait;


    public function __construct(Template $template)
    {
        $this->setTemplate($template);
    }


    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $items = MenuItemMap::create()->findByParentId(0, Tool::create('order_id DESC'));
        foreach ($items as $item) {
            if ($item->hasChildren()) {
                $children = MenuItemMap::create()->findByParentId($item->getId());
                $dropdown = $template->getRepeat('dropdown');
                $dropdown->setText('name', $item->getName());
                foreach ($children as $child) {
                    $row = $dropdown->getRepeat('dropdown-item');
                    $row->setText('name', $child->getName());
                    $row->setAttr('name', 'href', $child->getPage()->getPageUrl());
                    $row->appendRepeat();
                }
                $dropdown->prependRepeat('navbar');
            } else {
                $row = $template->getRepeat('nav-item');
                $row->setText('name', $item->getName());
                $row->setAttr('name', 'href', $item->getPage()->getPageUrl());
                $row->prependRepeat('navbar');
            }
        }


        return $template;
    }

}

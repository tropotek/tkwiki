<?php
namespace App\Table;

use App\Db\Page;
use Bs\Table;
use Tk\Form\Field\Input;
use Tk\Traits\SystemTrait;
use Tt\Table\Cell;

class PageSelect extends Table
{
    use SystemTrait;

    public function init(): static
    {
//        $this->table = new Table('pages-min');
//        $this->filter = new Form($this->table->getId() . '-filters');

        $this->appendCell('title')
            ->addHeaderCss('max-width')
            ->addOnValue(function(Page $page, Cell $cell) {
                return sprintf('<a href="javascript:;" class="wiki-insert"
                    data-page-id="%s" data-page-title="%s" data-page-url="%s" title="Insert a page link">%s</a>',
                    $page->pageId, $page->title, $page->url, $page->title);
            });

        $this->appendCell('category')
            ->addOnValue(function(Page $page, Cell $cell) {
                return sprintf('<a href="javascript:;" class="wiki-cat-list"
                    data-category="%s" title="Insert a category table">%s</a>',
                    $page->category, $page->category);
            });

        $this->appendCell('userId')
            ->addOnValue(function(Page $page, Cell $cell) {
                return $page->getUser()?->getName() ?? '';
            });

        $this->appendCell('permission')
            ->addOnValue(function(Page $page, Cell $cell) {
                return \App\Db\Page::PERM_LIST[$page->permission] ?? '';
            });

        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: id, title, category');

        // init filter fields for actions to access to the filter values
        $this->initForm();

        return $this;
    }

//    public function show(): ?Template
//    {
//        //$renderer = $this->getRenderer();
//        //$this->addCss('text-nowrap');
//        //$renderer->getFooterList()->remove('limit');
//
//        $this->getForm()->addCss('row gy-2 gx-3 align-items-center');
//        //$filterRenderer = Form\Renderer\Dom\Renderer::createInlineRenderer($this->getFilter());
//        //$renderer->getTemplate()->appendTemplate('filters', $filterRenderer->show());
//        //$renderer->getTemplate()->setVisible('filters');
//
//        return parent::show();
//    }
}
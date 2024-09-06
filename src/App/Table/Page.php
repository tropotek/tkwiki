<?php
namespace App\Table;

use Bs\Table;
use Dom\Template;
use Tk\Form\Field\Input;
use Tk\Form\Field\Select;
use Tk\Uri;
use Tt\Db;
use Tt\Table\Action\Delete;
use Tt\Table\Cell;
use Tt\Table\Cell\RowSelect;

class Page extends Table
{

    public function init(): static
    {
        $rowSelect = RowSelect::create('id', 'userId');
        $this->appendCell($rowSelect);

        $this->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnValue(function(\App\Db\Page $page, Cell $cell) {
                return <<<HTML
                    <a class="btn btn-outline-secondary btn-copy-code" href="javascript:;" data-page-id="{$page->pageId}" title="Click to copy wiki link"><i class="fa fa-fw fa-code"></i></a>
                HTML;
            });

        $this->appendCell('title')
            ->addCss('text-nowrap')
            ->addHeaderCss('max-width')
            ->setSortable(true)
            ->addOnValue(function(\App\Db\Page $page, Cell $cell) {
                $url = Uri::create('/edit', ['pageId' => $page->pageId]);
                return sprintf('<a href="%s">%s</a>', $url, $page->title);
            });

        $this->appendCell('category')
            ->addCss('text-nowrap')
            ->setSortable(true);

        $this->appendCell('url')
            ->addCss('text-nowrap')
            ->setSortable(true);

        $this->appendCell('published')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tt\Table\Type\Boolean::onValue');

        $this->appendCell('isOrphaned')
            ->addCss('text-nowrap')
            ->setHeader('Orphan')
            ->setSortable(true)
            ->addOnValue('\Tt\Table\Type\Boolean::onValue');

        $this->appendCell('permission')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Page $page, Cell $cell) {
                return $page->getPermissionLabel();
            });

        $this->appendCell('userId')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Page $page, Cell $cell) {
                return $page->getUser()->getName();
            });

        $this->appendCell('modified')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tt\Table\Type\DateFmt::onValue');

        $this->appendCell('created')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tt\Table\Type\DateFmt::onValue');


        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: name');

        $list = \App\Db\Page::getCategoryList();
        $this->getForm()->appendField(new Select('category', $list))->prependOption('-- Category -- ', '');

        $list = ['-- All --' => '', 'Linked' => 'n', 'Orphaned' => 'y'];
        $this->getForm()->appendField(new Select('isOrphaned', $list));

        // init filter fields for actions to access to the filter values
        $this->initForm();

        // Add Table actions
        $this->appendAction(Delete::create($rowSelect))
            ->addOnDelete(function(Delete $action, array $selected) {
                $homeId = intval($this->getRegistry()->get('wiki.page.home', 1));
                foreach ($selected as $page_id) {
                    if ($page_id == $homeId) continue;
                    Db::delete('page', compact('page_id'));
                }
            });

        return $this;
    }

    public function show(): ?Template
    {
        $template = parent::show();

        $js = <<<JS
jQuery(function($) {

    $('.btn-copy-code').on('click', function () {
        let tr = $(this).closest('tr');
        let url = $('.mUrl', tr).text();
        let title = $('.mTitle a', tr).text();
        let code = `<p>&lt;a href="page://\${url}" title="\${title}"&gt;\${title}&lt;/a&gt;</p>`;
        copyToClipboard($(code).text());
    });

});
JS;
        $template->appendJs($js);
        return $template;
    }

}
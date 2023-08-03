<?php
namespace App\Table;

use App\Db\ContentMap;
use App\Db\PageMap;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Date;
use Tk\Db\Mapper\Result;
use Tk\Traits\SystemTrait;
use Tk\Ui\Link;
use Tk\Uri;
use Tk\Form;
use Tk\Form\Field;
use Tk\FormRenderer;
use Tk\Table;
use Tk\Table\Cell;
use Tk\Table\Action;
use Tk\TableRenderer;

class Content
{
    use SystemTrait;

    protected Table $table;

    protected ?Form $filter = null;

    protected ?\App\Db\Page $page = null;


    public function __construct()
    {
        $this->table = new Table('content');
        $this->filter = new Form($this->table->getId() . '-filters');
    }

    public function doDefault(Request $request, int $pageId)
    {
        $this->page = PageMap::create()->find($request->query->getInt('pageId'));
        if (!$pageId) {
            Alert::addWarning('Invalid page id: ' . $pageId);
            \App\Db\Page::homeUrl()->redirect();
        }
        if ($request->query->has('r')) {
            $this->doRevert($request);
        }


        $this->getTable()->appendCell(new Cell\Text('actions'))
            ->addOnShow(function (Cell\Text $cell, string $html) {
                $cell->addCss('text-nowrap text-center');
                /** @var \App\Db\Content $obj */
                $obj = $cell->getRow()->getData();

                $template = $cell->getTemplate();
                $btn = new Link('Revert');
                $btn->setText('');
                $btn->setIcon('fa fa-fw fa-share');
                $btn->addCss('btn btn-outline-dark');
                $btn->setAttr('data-confirm', 'Are you sure you want to revert the content to revision ' . $obj->getContentId(). '?');
                $btn->setUrl(Uri::create()->set('r', $obj->getContentId()));
                $template->appendTemplate('td', $btn->show());
                $template->appendHtml('td', '&nbsp;');

                $btn = new Link('View');
                $btn->setText('');
                $btn->setIcon('fa fa-fw fa-eye');
                $btn->addCss('btn btn-outline-dark');
                $btn->setUrl(Uri::create('/view')->set('contentId', $obj->getContentId()));
                $template->appendTemplate('td', $btn->show());

                return '';
            });

        $this->getTable()->appendCell(new Cell\Text('revisionId'))
            ->setOrderByName('content_id')
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Content $content */
                $content = $cell->getRow()->getData();
                return $content->getId();
            })
            ->addOnShow(function (Cell\Text $cell, string $html) {
                /** @var \App\Db\Content $content */
                $content = $cell->getRow()->getData();
                if ($this->page->getContent() && $content->getId() == $this->page->getContent()->getId()) {
                    $html = '<b title="Current">' . $html . '</b>';
                }
                return $html;
            });
        $this->getTable()->appendCell(new Cell\Date('created'))->addCss('key')
            ->addOnShow(function (Cell\Date $cell, string $html) {
                /** @var \App\Db\Content $content */
                $content = $cell->getRow()->getData();
                $html = $content->getCreated(Date::FORMAT_LONG_DATETIME);
                $cell->setUrl(Uri::create('/view')->set('contentId', $content->getId()));
                return $html;
            });
        $this->getTable()->appendCell(new Cell\Text('userId'))
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Content $content */
                $content = $cell->getRow()->getData();
                $cell->setValue($content->getUser()->getName());
            });


        // Table filters
        $this->getFilter()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');

        // Load filter values
        $this->getFilter()->setFieldValues($this->getTable()->getTableSession()->get($this->getFilter()->getId(), []));

        $this->getFilter()->appendField(new Form\Action\Submit('Search', function (Form $form, Form\Action\ActionInterface $action) {
            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), $form->getFieldValues());
            Uri::create()->redirect();
        }))->setGroup('');
        $this->getFilter()->appendField(new Form\Action\Submit('Clear', function (Form $form, Form\Action\ActionInterface $action) {
            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), []);
            Uri::create()->redirect();
        }))->setGroup('')->addCss('btn-outline-secondary');

        $this->getFilter()->execute($request->request->all());


        // Table Actions
        if ($this->getConfig()->isDebug()) {
            $this->getTable()->appendAction(new Action\Link('reset', Uri::create()->set(Table::RESET_TABLE, $this->getTable()->getId()), 'fa fa-retweet'))
                ->setLabel('')
                ->setAttr('data-confirm', 'Are you sure you want to reset the Table`s session?')
                ->setAttr('title', 'Reset table filters and order to default.');
        }

    }

    public function execute(Request $request, ?Result $list = null): void
    {
        // Query
        if (!$list) {
            $tool = $this->getTable()->getTool();
            $filter = $this->getFilter()->getFieldValues();
            $list = ContentMap::create()->findFiltered($filter, $tool);
        }
        $this->getTable()->setList($list);

        $this->getTable()->execute($request);
    }

    public function doRevert(Request $request)
    {
        /** @var \App\Db\Content $revision */
        $revision = ContentMap::create()->find($request->query->getInt('r'));
        if (!$revision) {
            Alert::addWarning('Failed to revert to revision ID ' . $request->query->getInt('r'));
            $this->page->getPageUrl()->redirect();
        }
        $content = \App\Db\Content::cloneContent($revision);
        $content->save();

        Alert::addSuccess('Page reverted to version ' . $revision->getContentId() . ' [' . $revision->created->format(\Tk\Date::FORMAT_SHORT_DATETIME) . ']');
        $this->page->getPageUrl()->redirect();
    }

    public function show(): ?Template
    {
        $renderer = new TableRenderer($this->getTable());
        //$renderer->setFooterEnabled(false);
        $this->getTable()->getRow()->addCss('text-nowrap');
        $this->getTable()->addCss('table-hover');

        if ($this->getFilter()) {
            $this->getFilter()->addCss('row gy-2 gx-3 align-items-center');
            $filterRenderer = FormRenderer::createInlineRenderer($this->getFilter());
            $renderer->getTemplate()->appendTemplate('filters', $filterRenderer->show());
            $renderer->getTemplate()->setVisible('filters');
        }

        return $renderer->show();
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getFilter(): ?Form
    {
        return $this->filter;
    }
}
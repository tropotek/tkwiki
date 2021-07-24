<?php
namespace App\Controller\Page;

use Dom\Template;
use Tk\Request;
use App\Controller\Iface;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class History extends Iface
{

    /**
     * @var \App\Db\Page
     */
    protected $wPage = null;

    /**
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        if ($request->has('r')) {
            $this->doRevert($request);
        }

        $this->wPage = \App\Db\PageMap::create()->find($request->get('pageId'));
        if (!$this->wPage) {
            throw new \Tk\HttpException(404, 'Page not found');
        }

        if (!$this->wPage->canEdit($this->getAuthUser())) {
            \Tk\Alert::addWarning('You do not have permission to edit this page.');
            \Tk\Uri::create('/')->redirect();
        }


        $this->table = \Tk\Table::create('historyTable');

        $actions = new \Tk\Table\Cell\Actions();

        $actions->addButton(\Tk\Table\Cell\ActionButton::create('Revert', \Tk\Uri::create('/user/history.html'), 'fa fa-share'))
            ->addOnShow(function ($cell, $obj, $btn) {
                /** @var \App\Db\Content $obj **/
                /** @var \Tk\Table\Cell\ActionButton $btn **/
                $btn->getUrl()->set('r', $obj->getId());
            });
        $actions->addButton(\Tk\Table\Cell\ActionButton::create('Preview', \Tk\Uri::create('/view.html'), 'fa fa-eye'))
            ->addOnShow(function ($cell, $obj, $btn) {
                /** @var \App\Db\Content $obj **/
                /** @var \Tk\Table\Cell\ActionButton $btn **/
                $btn->getUrl()->set('contentId', $obj->getId());
            });

        $this->table->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->appendCell($actions);
        $this->table->appendCell(\Tk\Table\Cell\Date::createDate('created',\Tk\Date::FORMAT_SHORT_DATETIME))->addCss('key')->setUrl(\Tk\Uri::create('/view.html'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('userId'))->setOrderProperty('user_id');
        $this->table->appendCell(new \Tk\Table\Cell\Text('id'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('size'))->setLabel('Bytes')->addOnPropertyValue(function ($cell, $obj, $value) {
            return \Tk\File::bytes2String($value);
        });

        // Actions
        //$this->table->addAction(new \Tk\Table\Action\Csv($this->getConfig()->getDb()));

        $filter = $this->table->getFilterValues();
        $filter['pageId'] = $this->wPage->id;
        $filter['exclude'] = $this->wPage->getContent()->getId();
        $content = \App\Db\ContentMap::create()->findFiltered($filter, $this->table->getTool('a.created DESC'));
        $this->table->setList($content);

    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    protected function doRevert(Request $request)
    {
        /** @var \App\Db\Content $rev */
        $rev = \App\Db\ContentMap::create()->find($request->get('r'));
        if (!$rev) {
            throw new \Tk\Exception('Revert content not found!');
        }
        $content = \App\Db\Content::cloneContent($rev);
        $content->save();

        \Tk\Alert::addSuccess('Page reverted to version ' . $rev->id . ' [' . $rev->created->format(\Tk\Date::FORMAT_SHORT_DATETIME) . ']');
        $content->getPage()->getPageUrl()->redirect();
    }


    /**
     * @return Template
     */
    public function show()
    {
        $template = parent::show();

        $header = new \App\Helper\PageHeader($this->wPage, $this->wPage->getContent(), $this->getAuthUser());
        $template->insertTemplate('header', $header->show());

        $ren =  \Tk\Table\Renderer\Dom\Table::create($this->table);
        $template->replaceTemplate('table', $ren->show());

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="wiki-page-history">

  <div var="header" class="wiki-header"></div>
  <div var="table" class="wiki-history-table"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}

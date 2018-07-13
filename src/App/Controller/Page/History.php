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


    // TODO: We need to remove the current content record from the list as it cannot
    // TODO:  be reverted to itself.


    /**
     * @param Request $request
     * @throws \Tk\Exception
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

        if (!$this->getConfig()->getAcl()->canEdit($this->wPage)) {
            \Tk\Alert::addWarning('You do not have permission to edit this page.');
            \Tk\Uri::create('/')->redirect();
        }
        
        
        $this->table = \Tk\Table::create('historyTable');

        $this->table->addCell(new ActionCell('actions'));
        $this->table->addCell(new \Tk\Table\Cell\Text('id'));
        $this->table->addCell(new DateCell('created'))->addCss('key')->setUrl(\Tk\Uri::create('/view.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('userId'))->setOrderProperty('user_id');
        $this->table->addCell(new BytesCell('size'))->setLabel('Bytes');
        

        // Actions
        //$this->table->addAction(new \Tk\Table\Action\Csv($this->getConfig()->getDb()));

        $filter = $this->table->getFilterValues();
        $filter['pageId'] = $this->wPage->id;
        $users = \App\Db\ContentMap::create()->findFiltered($filter, $this->table->makeDbTool('a.created DESC'));
        $this->table->setList($users);

    }

    /**
     * @param Request $request
     * @throws \Tk\Exception
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
        
        \Tk\Alert::addSuccess('Page reverted to version ' . $rev->id . ' [' . $rev->created->format(\Tk\Date::SHORT_DATETIME) . ']');
        $content->getPage()->getPageUrl()->redirect();
    }


    /**
     * @return Template
     */
    public function show()
    {
        $template = parent::show();
        
        $header = new \App\Helper\PageHeader($this->wPage, $this->wPage->getContent(), $this->getUser());
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

class DateCell extends \Tk\Table\Cell\Text
{


    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering attached,
     * unless the rendering code is part of the value as it will be called for
     * outputting to other files like XML or CSV.
     *
     *
     * @param \App\Db\Content $obj
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($obj, $property)
    {
        $date =  $obj->$property;
        if (!$date instanceof \DateTime) return $date;
        return $date->format(\Tk\Date::FORMAT_SHORT_DATETIME) . ' - ' . \Tk\Date::toRelativeString($date);
    }
}

class BytesCell extends \Tk\Table\Cell\Text
{


    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering attached,
     * unless the rendering code is part of the value as it will be called for
     * outputting to other files like XML or CSV.
     *
     *
     * @param \App\Db\Content $obj
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($obj, $property)
    {
        return \Tk\File::bytes2String($obj->$property);
    }
}

class ActionCell extends \Tk\Table\Cell\Iface
{

    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
        $this->setOrderProperty('');
    }

    /**
     *
     * @return string
     */
    public function getCellHeader()
    {
        $str = $this->getLabel();
        $url = $this->getOrderUrl();
        if ($url) {
            $str = sprintf('<a href="%s" class="noblock" title="Click to order by: %s">%s</a>', htmlentities($url->toString()), $this->getOrderProperty(), $this->getLabel());
        }
        return $str;
    }

    /**
     * @param mixed $obj
     * @param null $rowIdx
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $html = array();

        $html[] = $this->makeButton(\Tk\Uri::create('/history.html')->set('r', $obj->id), 'glyphicon glyphicon-share', 'Revert', 'fid-revert-btn', 'btn btn-default btn-xs wiki-revert-trigger');
        $html[] = $this->makeButton(\Tk\Uri::create('/view.html')->set('contentId', $obj->id), 'glyphicon glyphicon-eye-open', 'Preview', 'fid-preview-btn');
        
        return implode(' ', $html);
    }
    
    protected function makeButton($url, $icon, $title = '', $id = '', $css= 'btn btn-default btn-xs')
    {
        return sprintf('<a href="%s" class="%s" title="%s"><i class="%s"></i></a>', $url, $css, $title, $icon);
    }
}


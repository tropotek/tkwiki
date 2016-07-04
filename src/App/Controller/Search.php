<?php
namespace App\Controller;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * Class Contact
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Search extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;

    protected $terms = '';

    /**
     * @var \Tk\Db\Map\ArrayObject
     */
    protected $list = null;
    
    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Search Results');
    }

    /**
     * doDefault
     *
     * @param Request $request
     * @return \App\Page\PublicPage
     */
    public function doDefault(Request $request)
    {
        $this->config = \Tk\Config::getInstance();
        $this->terms = $request->get('search-terms');
        $tool = \Tk\Db\Tool::create();
        
        if (preg_match('/user:([0-9]+)/', $this->terms, $regs)) {
            $user = \App\Db\User::getMapper()->find($regs[1]);
            // TODO: Test this is correct for public private etc pages...
            if ($user) {
                if ($this->getUser()->getAccess()->isAdmin()) {
                    $this->list = \App\Db\Page::getMapper()->findUserPages($user->id, [], $tool);
                } else if ($this->getUser()->getAccess()->isModerator()) {
                    $this->list = \App\Db\Page::getMapper()->findUserPages($user->id, [\App\Db\Page::PERMISSION_PROTECTED, \App\Db\Page::PERMISSION_PUBLIC], $tool);
                } else {
                    $this->list = \App\Db\Page::getMapper()->findUserPages($user->id, [\App\Db\Page::PERMISSION_PUBLIC], $tool);
                }
            }
        } else {
            // TODO
            // TODO: Need to create a real search function
            // TODO
            $filter = ['keywords' => $this->terms];
            $this->list = \App\Db\Page::getMapper()->findFiltered($filter, $tool);
        }
        
        return $this->show();
    }

    /**
     * show()
     *
     * @return \App\Page\PublicPage
     */
    public function show()
    {
        $template = $this->getTemplate();
        $searchForm = $this->getPage()->getTemplate()->getForm('NavSearch');
        if ($searchForm) {
            $searchForm->getFormElement('search-terms')->setValue($this->terms);
        }
        $template->insertText('terms', '"'.$this->terms.'"');
        $template->insertText('found', $this->list->getFoundRows());
        
        /** @var \App\Db\Page $page */
        foreach($this->list as $page) {
            $rpt = $template->getRepeat('row');
            $rpt->insertText('title', $page->title);
            $rpt->setAttr('title', 'title', $page->title);
            $rpt->setAttr('title', 'href', $page->getUrl());
            
            $description = $page->getContent()->description;
            if (!$description)
                $description = substr(strip_tags(html_entity_decode($page->getContent()->html)), 0, 256);
            $rpt->insertText('description', $description);

            $rpt->insertText('date', $page->getContent()->created->format(\Tk\Date::MED_DATE));
            $rpt->insertText('time', $page->getContent()->created->format('H:i'));
            if (trim($page->getContent()->keywords)) {
                $rpt->insertText('keywords', $page->getContent()->keywords);
                $rpt->setChoice('keywords');
            }
            
            $rpt->appendRepeat();
        }

        return $this->getPage()->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="wiki-search">

  <hgroup class="mb20">
    <h2 class="lead"><strong class="text-danger" var="found">0</strong> results were found for the search for <strong class="text-danger" var="terms"></strong></h2>								
  </hgroup>
  <article class="search-result row" repeat="row">
    <div class="col-xs-12 col-sm-12 col-md-2">
      <ul class="meta-search">
        <li><i class="glyphicon glyphicon-calendar"></i> <span var="date" title="Last Modified">02/15/2014</span></li>
        <li><i class="glyphicon glyphicon-time"></i> <span var="time" title="Last Modified">4:28 pm</span></li>
        <li><i class="glyphicon glyphicon-tags" choice="keywords"></i> <span var="keywords"></span></li>
      </ul>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-10 excerpet">
      <h3><a href="#" title="" var="title">Voluptatem, exercitationem, suscipit, distinctio</a></h3>
      <p var="description">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatem, exercitationem, suscipit, distinctio, 
        qui sapiente aspernatur molestiae non corporis magni sit sequi iusto debitis delectus doloremque.
      </p>
    </div>
    <span class="clearfix borda"></span>
  </article>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}
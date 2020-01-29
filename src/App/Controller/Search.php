<?php
namespace App\Controller;

use Tk\Request;
use Tk\Form;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Search extends Iface
{
    const SID = 'search.terms';

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var string
     */
    protected $terms = '';

    /**
     * @var \Tk\Db\Map\ArrayObject
     */
    protected $list = array();

    /**
     * @var \Bs\Db\User
     */
    protected $user = null;


    /**
     * doDefault
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Search Results');

        if ($request->has('search-terms')) {
            $this->terms = $request->get('search-terms');
            $this->getConfig()->getSession()->set(self::SID, $this->terms);
            \Tk\Uri::create()->remove('search-terms')->redirect();
        }
        if ($this->getConfig()->getSession()->has(self::SID)) {
            $this->terms = $this->getConfig()->getSession()->get(self::SID);
        }
        $tool = \Tk\Db\Tool::create();
        if (preg_match('/user:([0-9a-f]{32})/i', $this->terms, $regs)) {
            $this->user = $this->getConfig()->getUserMapper()->findByHash($regs[1]);
            $this->terms = '';
            // TODO: Test this is correct for public private etc pages...
            if ($this->user) {
                if ($this->getAuthUser() && $this->getConfig()->getAcl()->isAdmin()) {
                    $this->list = \App\Db\PageMap::create()->findUserPages($this->user->id, array(), $tool);
                } else if ($this->getAuthUser() && $this->getConfig()->getAcl()->isModerator()) {
                    $this->list = \App\Db\PageMap::create()->findUserPages($this->user->id, array(\App\Db\Page::PERMISSION_PROTECTED, \App\Db\Page::PERMISSION_PUBLIC), $tool);
                } else {
                    $this->list = \App\Db\PageMap::create()->findUserPages($this->user->id, array(\App\Db\Page::PERMISSION_PUBLIC), $tool);
                }
            }
        } else {


            // TODO
            // TODO: Need to create a real search function to search the page content
            // TODO
            if ($this->terms) {
                $filter = array('keywords' => $this->terms, 'type' => \App\Db\Page::TYPE_PAGE);
                $this->list = \App\Db\PageMap::create()->findFiltered($filter, $tool);
            }
            // TODO
            // TODO: Need to create a real search function
            // TODO

        }

    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        $searchForm = $this->getPage()->getTemplate()->getForm('NavSearch');
        if ($searchForm) {
            $searchForm->getFormElement('search-terms')->setValue($this->terms);
        }

        $access = \App\Auth\Acl::create($this->getAuthUser());
        $i = 0;
        /** @var \App\Db\Page $page */
        foreach($this->list as $page) {
            if (!$access->canView($page)) continue;

            $rpt = $template->getRepeat('row');
            $rpt->insertText('title', $page->title);
            $rpt->setAttr('title', 'title', $page->title);
            $rpt->setAttr('title', 'href', $page->getPageUrl());

            $rpt->insertText('description', 'No Content.');
            $rpt->insertText('date', $page->created->format(\Tk\Date::FORMAT_MED_DATE));
            $rpt->insertText('time', $page->created->format('H:i'));

            if ($page->getContent()) {
                $description = $page->getContent()->description;
                // This is a security risk as is can show sensitive data from the content, do not do this...
                if (!$description)
                    $description = trim(substr(strip_tags(html_entity_decode($page->getContent()->html)), 0, 256));

                $rpt->insertHtml('description', htmlentities($description));
                $rpt->insertText('date', $page->getContent()->created->format(\Tk\Date::FORMAT_MED_DATE));
                $rpt->insertText('time', $page->getContent()->created->format('H:i'));
                if (trim($page->getContent()->keywords)) {
                    $rpt->insertText('keywords', $page->getContent()->keywords);
                    $rpt->setVisible('keywords');
                }
            }

            $rpt->appendRepeat();
            $i++;
        }

        $terms = '"'.$this->terms.'"';
        if ($this->user) {
            $terms = '"User: '.$this->user->name.'"';
        }
        $template->insertText('terms', $terms);

        $template->insertText('found', $i);

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
<div class="wiki-search">

  <div class="search-head">
    <h2 class="lead"><strong class="text-danger" var="found">0</strong> results were found for the search for <strong class="text-danger" var="terms"></strong></h2>
  </div>

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
      <p var="description" class="search-description">
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

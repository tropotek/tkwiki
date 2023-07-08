<?php
namespace App\Controller\Page;

use App\Db\Page;
use App\Db\PageMap;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;

/**
 *
 *
 * TODO: We need to implement the Table DIV renderer and use
 *       that to render the results.
 *
 */
class Search extends PageController
{
    const SID = 'search.terms';


    protected ?Result $list = null;

    protected string $terms = '';


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Search Results');
        $this->getCrumbs()->reset();
    }

    public function doDefault(Request $request)
    {

        if ($request->request->has('search')) {
            $this->terms = $request->request->get('search');
            $this->getSession()->set(self::SID, $this->terms);
            \Tk\Uri::create()->remove('search')->redirect();
        }
        $this->terms = $this->getConfig()->getSession()->get(self::SID, '');

        $filter = [
            'fullSearch' => $this->terms,
        ];
        $user = $this->getFactory()->getAuthUser();
        if ($user?->isAdmin()) {
            ; // Search all
        } elseif ($user?->isStaff()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_USER, Page::PERM_STAFF];
            $filter['author'] = $user->getUserId();
        } elseif ($user?->isMember()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_USER];
        } else {
            $filter['permission'] = [Page::PERM_PUBLIC];
        }

        $this->list = PageMap::create()->findFiltered($filter, Tool::create('modified DESC'));

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        /** @var \App\Db\Page $page */
        foreach($this->list as $page) {
            if (!$page->canView($this->getAuthUser())) continue;

            $rpt = $template->getRepeat('row');
            $rpt->setText('title', $page->getTitle());
            $rpt->setAttr('title', 'title', $page->getTitle());
            $rpt->setAttr('title', 'href', $page->getPageUrl());

            $rpt->setAttr('link', 'href', $page->getPageUrl());
            $rpt->setText('link', $page->getPageUrl());

            $rpt->setText('description', 'No Content.');
            $rpt->setText('date', $page->getCreated()->format(\Tk\Date::FORMAT_MED_DATE));
            $rpt->setText('time', $page->getCreated()->format('H:i'));

            if ($page->getContent()) {
                $description = $page->getContent()->getDescription();
                // This is a security risk as is can show sensitive data from the content, do not do this...
                if (!$description) {
                    $description = trim(substr(strip_tags(html_entity_decode($page->getContent()->getHtml())), 0, 256));
                }

                $rpt->insertHtml('description', htmlentities($description));
                $rpt->setText('author', $page->getUser()->getName());
                $rpt->setText('date', $page->getContent()->getCreated()->format(\Tk\Date::FORMAT_MED_DATE));
                $rpt->setText('time', $page->getContent()->getCreated()->format('H:i'));
                if (trim($page->getContent()->getKeywords())) {
                    $rpt->setText('keywords', $page->getContent()->getKeywords());
                    $rpt->setVisible('keywords');
                }
            }

            $rpt->appendRepeat();
        }

        $terms = '"'.$this->terms.'"';
        $template->setText('terms', $terms);
        $template->setText('found', $this->list->countAll());

        $css = <<<CSS
.wiki-search .search-result h4 {
    margin-bottom: 0;
    color: #1E0FBE;
}
.wiki-search .search-result h4 a {
    text-decoration: none;
}
.wiki-search .search-result .search-link {
    color: #006621;
    text-decoration: none;
}
.wiki-search .search-result p {
    font-size: 14px;
    margin-top: 5px;
}
.wiki-search ul {

}
.wiki-search ul li {
  font-size: 12px;
  color: #666;
}
.wiki-search .hr-line-dashed {
    border-top: 1px dashed #E7EAEC;
    color: #ffffff;
    background-color: #ffffff;
    height: 1px;
    margin: 20px 0;
}

CSS;
        $template->appendCss($css);

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <h2 var="title">Search Results</h2>
  <div class="wiki-search" var="content">
    <div class="search-head">
      <h5 class="">
        <strong class="text-danger" var="found">0</strong>
        results were found for the search for
        <strong class="text-danger" var="terms"></strong>
      </h5>
    </div>
    <div class="hr-line-dashed"></div>
    <div repeat="row">
      <div class="search-result">
        <h4><a href="#" var="title"></a></h4>
        <a href="#" class="search-link" var="link"></a>
        <p var="description"></p>
        <ul class="list-inline">
          <li class="list-inline-item" title="Author"><i class="fa fa-fw fa-user"></i> <span var="author"></span></li>
          <li class="list-inline-item" title="Modified Date"><i class="fa fa-fw fa-calendar"></i> <span var="date"></span></li>
          <li class="list-inline-item" title="Modified Time"><i class="fa fa-fw fa-clock"></i> <span var="time"></span></li>
          <li class="list-inline-item" title="Tags" choice="keywords"><i class="fa fa-fw fa-tags"></i> <span var="keywords"></span></li>
        </ul>
      </div>
      <div class="hr-line-dashed"></div>
    </div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}
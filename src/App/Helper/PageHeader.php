<?php
namespace App\Helper;


use Dom\Renderer\Renderer;
use Dom\Template;

/**
 * An object to manage and display the wiki Page header
 * information and action buttons.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PageHeader extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{

    /**
     * @var \App\Db\Page
     */
    protected $wPage = null;

    /**
     * @var \App\Db\Content
     */
    protected $wContent = null;

    /**
     * Logged in user
     * @var \Bs\Db\User
     */
    protected $user = null;


    /**
     * Header constructor.
     *
     * @param \App\Db\Page $wPage
     * @param \App\Db\Content $wContent
     * @param \Bs\Db\User $user
     */
    public function __construct($wPage, $wContent, $user)
    {
        $this->wPage = $wPage;
        $this->wContent = $wContent;
        $this->user = $user;
    }


    /**
     * Execute the renderer.
     * Return an object that your framework can interpret and display.
     *
     * @return Template|Renderer
     * @throws \Exception
     */
    public function show()
    {
        $template = $this->getTemplate();


        // Page Title
        $title = str_replace('_', ' ', \Tk\Uri::create()->basename()) ;
        if ($this->wPage) {
            $title = $this->wPage->title;
        }


        if (!\Tk\Db\Data::create()->get('site.page.header.title.hide') || $this->user) {
            $template->appendHtml('title', $title);
            $template->setVisible('showTitle');
        }


        // Throw an info style page if no page exists and public user
        if (!$this->wPage && !$this->user) {
            $template->setVisible('noCreated');
            return $template;
        }

        if (\Tk\Db\Data::create()->get('site.page.header.hide') && !$this->user) {
            return $template;
        }

        $list = [];
        if ($this->wPage->getId())
            $list = \App\Db\ContentMap::create()->findFiltered(['pageId' => $this->wPage->getId()]);

        if ($this->isEdit()) {
            $template->setVisible('edit');
            if ($this->wPage->type == \App\Db\Page::TYPE_PAGE) {
                $template->setVisible('canView');
            }
            if ($this->wPage->getId() && count($list)) {
                $template->setVisible('historyBtn');
            }
        } else if ($this->isHistory()) {
            $template->setVisible('history');
            $title .= ' (History)';
        } else {
            if (\App\Config::getInstance()->getRequest()->has('contentId')) {
                $template->setVisible('viewRevision');
                $title .= ' <small>(Revision ' . $this->wContent->getId() . ')</small>';
                $template->addCss('content', 'revision');
                if ($this->wPage->canEdit($this->user)) {
                    $template->setVisible('revert');
                }
            } else {
                $template->setVisible('view');
            }
        }

        if ($this->user) {
            if ($this->wPage->canEdit($this->user)) {
                $url = \Tk\Uri::create('/user/edit.html')->set('pageId', $this->wPage->getId());
                $template->setAttr('edit', 'href', $url);
                $template->setVisible('canEdit');
            }
            if ($this->wPage->canDelete($this->user)) {
                $url = \Tk\Uri::create('/user/edit.html')->set('pageId', $this->wPage->getId())->set('del', $this->wPage->getId());
                $template->setAttr('delete', 'href', $url);
                $template->setVisible('canDelete');
            }

            if ($this->wContent) {
                $url = \Tk\Uri::create()->set('pdf', 'pdf');
                $template->setAttr('pdf', 'href', $url);
                $template->setAttr('pdf', 'target', '_blank');
                $template->setVisible('pdfBtn');
            }

            $url = $this->wPage->getPageUrl();
            if ($this->wPage->type == \App\Db\Page::TYPE_NAV || !$this->wPage->getId()) {
                $url = \Tk\Uri::create('/');
            }
            $template->setAttr('cancel', 'href', $url);
        }

        $url = \Tk\Uri::create('/user/history.html')->set('pageId', $this->wPage->getId());
        $template->setAttr('history', 'href', $url);

        if ($this->wContent) {
            $url = \Tk\Uri::create('/user/history.html')->set('r', $this->wContent->getId());
            $template->setAttr('revert', 'href', $url);

            $template->insertText('contentId', $this->wContent->getId());
        }

        $url = \Tk\Uri::create($this->wPage->url);
        $template->setAttr('view', 'href', $url);

        $template->insertText('permission', ucfirst($this->wPage->getPermissionLabel()));
        $template->addCss('permission', $this->wPage->getPermissionLabel());



        // TODO: Implement show() method.
        $content = $this->wPage->getContent();

        // modified
        if ($content) {
            //$template->insertText('modified', $content->modified->format(\Tk\Date::LONG_DATETIME));
            $template->insertText('modified', \Tk\Date::toRelativeString($content->modified));
            $template->setVisible('modified');
        }

        // contributers
        $contentList = \App\Db\ContentMap::create()->findContributors($this->wPage->getId());
        $html = array();
        /** @var \stdClass $c */
        foreach($contentList as $i => $c) {
            /** @var \Bs\Db\User $user */
            $user = \Bs\Db\UserMap::create()->find($c->user_id);
            if (!$user) continue;
            $url = \Tk\Uri::create('/search.html')->set('search-terms', 'user:'.$user->getHash());
            $class = array();
            $title = \Tk\Date::toRelativeString(\Tk\Date::create($c->created));
            if ($this->wPage->getUser()->getId() == $user->getId()) {
                $class[] = 'author';
                $title = 'Contributed: ' . $title;
            }
            $html[] = sprintf('<a href="%s" class="%s" title="%s">%s</a>', $url, implode(' ', $class), $title, $user->getName());
        }
        if (count($html)) {
            $template->insertHtml('contrib', implode(', ', $html));
            $template->setVisible('contrib');
        }

        $template->setVisible('showHeadInfo');

        return $template;
    }

    /**
     * Is the page url a view url or an edit url
     *
     * @return bool
     */
    public function isEdit()
    {
        if (\Tk\Uri::create()->basename() == 'edit.html') {
            return true;
        }
        return false;
    }

    /**
     * Is the page url a view url or an edit url
     *
     * @return bool
     */
    public function isHistory()
    {
        if (\Tk\Uri::create()->basename() == 'history.html') {
            return true;
        }
        return false;
    }

    /**
     * @return \App\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div var="content">
  <div class="clearfix wiki-header">
    <div class="head-title" choice="showTitle">
      <h1 var="title"></h1>
    </div>
    <div class="row" choice="showHeadInfo">
    <div class="col-xs-6">
      <p class="wiki-meta contrib" choice="contrib"><strong>Contributers:</strong> <span var="contrib"></span></p>
      <p class="wiki-meta modified" choice="modified"><strong>Modified:</strong> <span var="modified"></span></p>
    </div>
    <div class="col-xs-6 text-right edit" choice="edit">
      <p class="wiki-meta">
        <a href="#" title="Delete The Page" class="btn btn-danger btn-xs wiki-delete-trigger" var="delete" choice="canDelete"><i class="fa fa-remove"></i> Delete</a>
      </p>
      <p class="wiki-meta">
        <a href="#" title="Save The Page" class="btn btn-primary btn-xs wiki-save-trigger" var="save" choice="canEdit"><i class="fa fa-save"></i> Save</a>
        <a href="#" title="View The Page" class="btn btn-default btn-xs" var="view" choice="canView"><i class="fa fa-eye"></i> View</a>
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="historyBtn"><i class="fa fa-clock-o"></i> History</a>
        <!--  a href="#" title="Delete The Page" class="btn btn-danger btn-xs wiki-delete-trigger" var="delete" choice="canDelete"><i class="fa fa-remove"></i> Delete</a -->
        <a href="#" title="Cancel Edit Page" class="btn btn-default btn-xs" var="cancel"><i class="fa fa-ban"></i> Cancel</a>
      </p>
    </div>
    <div class="col-xs-6 text-right" choice="view">
      <p class="wiki-meta view">
        <a href="#" title="Edit The Page" class="btn btn-default btn-xs" var="edit" choice="canEdit"><i class="fa fa-pencil"></i> Edit</a>
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="canEdit"><i class="fa fa-clock-o"></i> History</a>
        <a href="#" title="Download PDF" class="btn btn-default btn-xs" var="pdf" choice="pdfBtn"><i class="fa fa-file-pdf-o"></i> Download</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    <div class="col-xs-6 text-right" choice="history">
      <p class="wiki-meta view">
        <a href="#" title="Edit The Page" class="btn btn-default btn-xs" var="edit" choice="canEdit"><i class="fa fa-pencil"></i> Edit</a>
        <a href="#" title="View The Page" class="btn btn-default btn-xs" var="view"><i class="fa fa-eye"></i> View</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    <div class="col-xs-6 text-right" choice="viewRevision">
      <p class="wiki-meta view">
        <a href="#" title="Page Revision History" class="btn btn-danger btn-xs wiki-revert-trigger" var="revert" choice="revert"><i class="fa fa-share"></i> Revert</a>
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="canEdit"><i class="fa fa-clock-o"></i> History</a>
        <a href="#" title="Download PDF" class="btn btn-default btn-xs" var="pdf" choice="pdfBtn"><i class="fa fa-file-pdf-o"></i> Download</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    </div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}

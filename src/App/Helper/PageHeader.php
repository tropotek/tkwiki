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
     * @var \App\Db\User
     */
    protected $user = null;


    /**
     * Header constructor.
     *
     * @param \App\Db\Page $wPage
     * @param \App\Db\Content $wContent
     * @param \App\Db\User $user
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
     */
    public function show()
    {
        $template = $this->getTemplate();
        $title = $this->wPage->title;
        
        if ($this->isEdit()) {
            $template->setChoice('edit');
            if ($this->wPage->type == \App\Db\Page::TYPE_PAGE) {
                $template->setChoice('canView');
            }
        } else if ($this->isHistory()) {
            $template->setChoice('history');
            $title .= ' (History)';
        } else {
            if (\App\Factory::getRequest()->has('contentId')) {
                $template->setChoice('viewRevision');
                $title .= ' <small>(Revision '.$this->wContent->id.')</small>';
                $template->addClass('content', 'revision');
                if ($this->user->getAcl()->canEdit($this->wPage)) {
                    $template->setChoice('revert');
                }
            } else {
                $template->setChoice('view');
            }
        }
        
        // title
        $template->appendHtml('title', $title);
        
        if ($this->user) {
            if ($this->user->getAcl()->canEdit($this->wPage)) {
                $url = \Tk\Uri::create('/edit.html')->set('pageId', $this->wPage->id);
                $template->setAttr('edit', 'href', $url);
                $template->setChoice('canEdit');
            }
            if ($this->user->getAcl()->canDelete($this->wPage)) {
                $url = \Tk\Uri::create('/edit.html')->set('pageId', $this->wPage->id)->set('del', $this->wPage->id);
                $template->setAttr('delete', 'href', $url);
                $template->setChoice('canDelete');
            }
            
            $url = $this->wPage->getUrl();
            if ($this->wPage->type == \App\Db\Page::TYPE_NAV || !$this->wPage->id) {
                $url = \Tk\Uri::create('/');
            }
            $template->setAttr('cancel', 'href', $url);
        }

        $url = \Tk\Uri::create('/history.html')->set('pageId', $this->wPage->id);
        $template->setAttr('history', 'href', $url);

        if ($this->wContent) {
            $url = \Tk\Uri::create('/history.html')->set('r', $this->wContent->id);
            $template->setAttr('revert', 'href', $url);
            
            $template->insertText('contentId', $this->wContent->id);
        }

        $url = \Tk\Uri::create($this->wPage->url);
        $template->setAttr('view', 'href', $url);
        
        $template->insertText('permission', ucfirst($this->wPage->getPermissionLabel()));
        $template->addClass('permission', $this->wPage->getPermissionLabel());
        
        
        
        // TODO: Implement show() method.
        $content = $this->wPage->getContent();
        
        // modified
        if ($content) {
            //$template->insertText('modified', $content->modified->format(\Tk\Date::LONG_DATETIME));
            $template->insertText('modified', \Tk\Date::toRelativeString($content->modified));
            $template->setChoice('modified');
        }
        
        // contributers
        $contentList = \App\Db\ContentMap::create()->findContributors($this->wPage->id);
        $html = array();
        /** @var \stdClass $c */
        foreach($contentList as $i => $c) {
            /** @var \App\Db\User $user */
            $user = \App\Db\UserMap::create()->find($c->user_id);
            if (!$user) continue;
            $url = \Tk\Uri::create('/search.html')->set('search-terms', 'user:'.$user->hash);
            $class = array();
            $title = \Tk\Date::toRelativeString(\Tk\Date::create($c->created));
            if ($this->wPage->getUser()->id = $user->id) {
                $class[] = 'author';
                $title = 'Contributed: ' . $title;
            }
            $html[] = sprintf('<a href="%s" class="%s" title="%s">%s</a>', $url, implode(' ', $class), $title, $user->name);
        }
        if (count($html)) {
            $template->insertHtml('contrib', implode(', ', $html));
            $template->setChoice('contrib');
        }
        
        
        
        return $template;
    }

    /**
     * Is the page url a view url or an edit url
     *
     * @return bool
     */
    public function isEdit()
    {
        if (\Tk\Uri::create()->getBasename() == 'edit.html') {
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
        if (\Tk\Uri::create()->getBasename() == 'history.html') {
            return true;
        }
        return false;
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
  <div class="row clearfix wiki-header">
    <div class="col-md-12">
      <h1 var="title"></h1>
    </div>
    <div class="col-md-6">
      <p class="wiki-meta contrib" choice="contrib"><strong>Contributers:</strong> <span var="contrib"></span></p>
      <p class="wiki-meta modified" choice="modified"><strong>Modified:</strong> <span var="modified"></span></p>
    </div>
    <div class="col-md-6 text-right edit" choice="edit">
      <p class="wiki-meta">
        <a href="#" title="Delete The Page" class="btn btn-danger btn-xs wiki-delete-trigger" var="delete" choice="canDelete"><i class="glyphicon glyphicon-remove"></i> Delete</a>
      </p>
      <p class="wiki-meta">
        <a href="#" title="Save The Page" class="btn btn-primary btn-xs wiki-save-trigger" var="save" choice="canEdit"><i class="glyphicon glyphicon-save"></i> Save</a>
        <a href="#" title="View The Page" class="btn btn-default btn-xs" var="view" choice="canView"><i class="glyphicon glyphicon-eye-open"></i> View</a>
        <!--  a href="#" title="Delete The Page" class="btn btn-danger btn-xs wiki-delete-trigger" var="delete" choice="canDelete"><i class="glyphicon glyphicon-remove"></i> Delete</a -->
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="cancel"><i class="glyphicon glyphicon-ban-circle"></i> Cancel</a>
      </p>
    </div>
    <div class="col-md-6 text-right" choice="view">
      <p class="wiki-meta view">
        &nbsp;
        <a href="#" title="Edit The Page" class="btn btn-default btn-xs" var="edit" choice="canEdit"><i class="glyphicon glyphicon-pencil"></i> Edit</a>  
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="canEdit"><i class="glyphicon glyphicon-time"></i> History</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    <div class="col-md-6 text-right" choice="history">
      <p class="wiki-meta view">  
        <a href="#" title="Edit The Page" class="btn btn-default btn-xs" var="edit" choice="canEdit"><i class="glyphicon glyphicon-pencil"></i> Edit</a>  
        <a href="#" title="View The Page" class="btn btn-default btn-xs" var="view"><i class="glyphicon glyphicon-eye-open"></i> View</a>  
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    <div class="col-md-6 text-right" choice="viewRevision">
      <p class="wiki-meta view">
        <a href="#" title="Page Revision History" class="btn btn-danger btn-xs wiki-revert-trigger" var="revert" choice="revert"><i class="glyphicon glyphicon-share"></i> Revert</a>
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="canEdit"><i class="glyphicon glyphicon-time"></i> History</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
  </div>
  <hr class="no-top-margin"/>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
    
    
}
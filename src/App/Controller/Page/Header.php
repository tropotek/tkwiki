<?php
namespace App\Controller\Page;


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
class Header extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    /**
     * @var \App\Db\Page
     */
    protected $wPage = null;

    /**
     * Logged in user
     * @var \App\Db\User
     */
    protected $user = null;


    /**
     * Header constructor.
     *
     * @param \App\Db\Page $wPage
     * @param \App\Db\User $user
     */
    public function __construct($wPage, $user)
    {
        $this->wPage = $wPage;
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
        
        // TODO: Implement show() method.
        $content = $this->wPage->getContent();
        
        if ($this->isView()) {
            $template->setChoice('view');
        } else {
            $template->setChoice('edit');
        }
        
        if ($this->user) {
            if ($this->user->getAccess()->canEdit($this->wPage)) {
                $url = \Tk\Uri::create('/edit.html')->set('pageId', $this->wPage->id);
                $template->setAttr('edit', 'href', $url);
                $template->setChoice('canEdit');
            }
            if ($this->user->getAccess()->canDelete($this->wPage)) {
                $url = \Tk\Uri::create('/edit.html')->set('pageId', $this->wPage->id)->set('del', $this->wPage->id);
                $template->setAttr('delete', 'href', $url);
                $template->setChoice('canDelete');
            }
            $url = \Tk\Uri::create($this->wPage->url);
            $template->setAttr('cancel', 'href', $url);
        }

        $url = \Tk\Uri::create('/history.html')->set('pageId', $this->wPage->id);
        $template->setAttr('history', 'href', $url);

        $url = \Tk\Uri::create($this->wPage->url);
        $template->setAttr('view', 'href', $url);
        
        
        
        // title
        $template->appendHtml('title', $this->wPage->title);

        // modified
        //$template->insertText('modified', $content->modified->format(\Tk\Date::LONG_DATETIME));
        $template->insertText('modified', \Tk\Date::toRelativeString($content->modified));
        
        // contributers
        $contentList = \App\Db\Content::getMapper()->findByPageId($this->wPage->id, \Tk\Db\Tool::create('modified'));
        $html = [];
        /** @var \App\Db\Content $c */
        foreach($contentList as $i => $c) {
            $user = $c->getUser();
            if (!$user) continue;
            $url = \Tk\Uri::create('/search.html')->set('mode', 'user:'.$user->id);
            $class = [];
            //$title = $c->modified->format(\Tk\Date::LONG_DATETIME);
            $title = \Tk\Date::toRelativeString($c->modified);
            if ($this->wPage->getUser()->id = $c->getUser()->id) {
                $class[] = 'author';
                $title = 'Contributed: ' . $title;
            }
            $html[] = sprintf('<a href="%s" class="%s" title="%s">%s</a>', $url, implode(' ', $class), $title, $user->name);
            
        }
        $template->insertHtml('contrib', implode(', ', $html));
        
        
        return $template;
    }

    /**
     * Is the page url a view url or an edit url
     *  
     * @return bool
     */
    public function isView()
    {
        if (\Tk\Uri::create()->getBasename() != 'edit.html') {
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
      <p class="wiki-meta"><strong>Contributers:</strong> <span var="contrib"><a href="#" title="Author" class="author">User</a>, <a href="#" title="Last Contributed: Thursday, 19 May 2016 07:22 AM">Administrator</a></span></p>
      <p class="wiki-meta"><strong>Modified:</strong> <span var="modified">Thursday, 19 May 2016 07:22 AM</span></p>
    </div>
    <div class="col-md-6 text-right" choice="edit">
      <p class="wiki-meta">
        <!-- a href="#" title="Save The Page" class="btn btn-primary btn-xs wiki-save-trigger" var="save" choice="canEdit"><i class="glyphicon glyphicon-save"></i> Save</a -->
        <a href="#" title="View The Page" class="btn btn-default btn-xs" var="view"><i class="glyphicon glyphicon-eye-open"></i> View</a>
        <a href="#" title="Delete The Page" class="btn btn-danger btn-xs wiki-delete-trigger" var="delete" choice="canDelete"><i class="glyphicon glyphicon-remove"></i> Delete</a>
        <!-- a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="cancel"><i class="glyphicon glyphicon-ban-circle"></i> Cancel</a -->
      </p>
    </div>
    <div class="col-md-6 text-right" choice="view">
      <p class="wiki-meta">  
        <a href="#" title="Edit The Page" class="btn btn-default btn-xs" var="edit" choice="canEdit"><i class="glyphicon glyphicon-pencil"></i> Edit</a>  
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history"><i class="glyphicon glyphicon-time"></i> History</a>
      </p>
    </div>
  </div>
  <hr class="no-top-margin"/>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
    
    
}
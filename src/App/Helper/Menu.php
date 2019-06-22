<?php
namespace App\Helper;


/**
 * An object to manage and display the wiki Page header
 * information and action buttons. 
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Menu extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{

    /**
     * Logged in user
     * @var \Bs\Db\User
     */
    protected $user = null;

    /**
     * @var null
     */
    protected $list = array();

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher = null;


    /**
     * constructor.
     *
     * @param \Bs\Db\User $user
     * @throws \Exception
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->init();
        $this->dispatcher = $this->getConfig()->getEventDispatcher();
    }

    /**
     * init
     * @throws \Exception
     */
    public function init()
    {
        $list = \App\Db\PageMap::create()->findNavPages(\Tk\Db\Tool::create());
        /** @var \App\Db\Page $page */
        foreach($list as $page) {
            if ($page->permission == \App\Db\Page::PERMISSION_PUBLIC) {
                $this->list[] = $page;
                continue;
            }
            if ($this->user && $this->getConfig()->getAcl()->canView($page)) {
                $this->list[] = $page;
                continue;
            }
        }
        
    }

    /**
     * Execute the renderer.
     * Return an object that your framework can interpret and display.
     *
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = $this->getTemplate();
        
        if ($this->user && $this->getConfig()->getAcl()->canCreate()) {
            $template->setVisible('canCreate');
            $url = \Tk\Uri::create('/edit.html')->set('type', \App\Db\Page::TYPE_NAV);
            $template->setAttr('create', 'href', $url);
        }
        
        /** @var \App\Db\Page $page */
        foreach($this->list as $page) {
            if (!\App\Auth\Acl::create($this->user)->canView($page)) return;
            $row = $template->getRepeat('row');
            $row->insertText('title', $page->title);

            $content = $page->getContent();
            $event = new \App\Event\ContentEvent($content);
            $this->dispatcher->dispatch(\App\WikiEvents::WIKI_CONTENT_VIEW, $event);
            
            $row->insertHtml('html', $content->html);
            
            if ($this->user && $this->getConfig()->getAcl()->canEdit($page)) {
                $url = \Tk\Uri::create('/user/edit.html')->set('pageId', $page->id);
                $row->setAttr('edit', 'href', $url);
                $row->setVisible('edit');
            }
            $row->appendRepeat();
        }

        return $template;
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
<ul class="nav navbar-nav">
  <li class="dropdown mega-dropdown" repeat="row">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span var="title"></span> <span class="caret"></span></a>
    <ul class="dropdown-menu mega-dropdown-menu">
      <li class="col-sm-12">
        <div class="wiki-menu-edit pull-right" choice="edit">
          <a href="#" class="btn btn-primary btn-sm wiki-menu-edit-btn" var="edit"><i class="fa fa-pencil"></i> Edit</a>
        </div>
        <div class="wiki-menu-content" var="html"></div>
      </li>
    </ul>
  </li>
  <li class="wiki-menu-create" choice="canCreate"><a href="#" class="navbar-toggle" title="New Menu Tab" var="create"><span class="fa fa-plus"></span></a></li>
</ul>
HTML;
        return \Dom\Loader::load($xhtml);
    }
    
    
}
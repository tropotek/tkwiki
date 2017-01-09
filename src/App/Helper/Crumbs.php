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
 * @TODO This is slow as hell, need to optimize it when we have time.
 */
class Crumbs extends \Dom\Renderer\Renderer implements \Serializable, \Dom\Renderer\DisplayInterface
{
    const SID = 'wiki.crumbs';

    /**
     * @var Crumbs
     */
    static public $instance = null;
    
    /**
     * @var null
     */
    protected $list = array();

    /**
     * @var int
     */
    protected $max = 4;
    
    
    /**
     * constructor.
     *
     */
    private function __construct()
    {
    }
    
    public function serialize()
    {
        return serialize(array('list' => $this->list));
    }

    public function unserialize($data)
    {
        $arr = unserialize($data);
        $this->list = $arr['list'];
    }
    
    /**
     * Get the crumbs instance from the session
     *
     * @param \Tk\Uri $requestUri
     * @param \Tk\Session $session
     * @return Crumbs
     */
    static public function getInstance($requestUri = null, $session = null)
    {
        if (!$session) $session = \App\Factory::getSession();

        if (!self::$instance) {
            self::$instance = new static();
            if ($session->has(self::SID)) {
                self::$instance = $session->get(self::SID);
            }
            $session->set(self::SID, self::$instance);
        }
        if (self::$instance && $requestUri) {
            self::$instance->addCrumb($requestUri);
        }
        return self::$instance;
    }

    /**
     * 
     * @param \Tk\Uri $url
     */
    public function addCrumb($url)
    {
        if (!$url) return;
        $page = \App\Db\PageMap::create()->findByUrl(trim($url->getRelativePath(), '/'));
        if ($url->getRelativePath() == '/' || trim($url->getRelativePath()) == \App\Db\Page::getHomeUrl()) {
            $page = \App\Db\PageMap::create()->findByUrl(\App\Db\Page::getHomeUrl());
        }
        if (!$page) return;
        $this->trim($url);
        $this->list[$page->title] = $page->getUrl();
        if (count($this->list) > $this->max) {
            array_shift($this->list);
        }
    }

    /**
     * Remove any duplicate crumbs
     *
     * @param \Tk\Uri $url
     * @return array
     */
    public function trim($url) 
    {
        $arr = array();
        if (!$url) return $arr;
        $i = 0;
        /** @var \Tk\Uri $u */
        foreach($this->list as $k => $u) {
            if ($i > $this->max) continue;
            if ($url->getPath() == $u->getPath()) continue;
            $arr[$k] = $u;
            $i++;
        }
        $this->list = $arr;
        return $arr;
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
        /** @var \Tk\Uri $crumb */
        $i = 0;
        foreach($this->list as $title => $crumb) {
            if ($i < count($this->list)-1 ) {
                $row = $template->getRepeat('crumb');
                $row->insertText('url', $title);
                $row->setAttr('url', 'href', $crumb);
                $row->appendRepeat();
            } else {
                $template->insertText('active', $title);
            }
            $i++;
        }
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
<ol class="breadcrumb">
  <!-- li><a href="/">Home</a></li -->
  <li repeat="crumb"><a href="#" var="url"></a></li>
  <li class="active" var="active"></li>
</ol>
HTML;
        return \Dom\Loader::load($xhtml);
    }
    
    
}
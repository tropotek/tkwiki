<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Render the Rss list to the output
 *
 * @package Com
 */
class Com_Xml_RssRenderer extends Dom_Renderer
{
    
    /**
     * @var array
     */
    protected $list = null;
    
    /**
     * @var string
     */
    protected $title = '';
    
    /**
     * @var string
     */
    protected $description = '';
    
    /**
     * @var Tk_Type_Url
     */
    protected $link = '';
    
    /**
     * __construct
     *
     * @param array $list
     * @param string $title
     * @param string $description
     * @param Tk_Type_Url $link
     */
    function __construct($list, $title = '', $description = '', $link = null)
    {
        $this->list = $list;
        $this->title = $title;
        $this->description = $description;
        $this->link = $link;
        if ($this->link == null) {
            $this->link = new Tk_Type_Url('/index.html');
        }
        if ($this->list == null) {
            $this->list = array();
        }
    }
    
    /**
     * __makeTemplate
     *
     * @return Dom_Template
     */
    function __makeTemplate()
    {
        $xml = sprintf('<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title var="title"></title>
    <link var="link">http://www.tropotek.com/</link>
    <atom:link var="atomLink" href="#" rel="self" type="application/rss+xml" />
    <description var="description"></description>
    <lastBuildDate var="now"></lastBuildDate>
    <language>en-us</language>
      
    <item repeat="item">
      <title var="title"></title>
      <link var="link"></link>
      <guid var="link"></guid>
      <pubDate var="created"></pubDate>
      <description var="description"></description>
      <category repeat="category" var="category"></category>
    </item>
    
  </channel>
</rss>');
        
        return Dom_Template::load($xml);
    }
    
    function setTitle($str)
    {
        $this->title = $str;
    }
    
    function setDescription($str)
    {
        $this->description = $str;
    }
    
    function setLink(Tk_Type_Url $url)
    {
        $this->link = $url;
    }
    
    /**
     * show
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $template->insertText('title', $this->title);
        $template->insertText('description', $this->description);
        $template->insertText('link', $this->link->toString());
        $url = clone $this->link;
        $url->set('rss', 'rss');
        $template->setAttr('atomLink', 'href', $url->toString());
        $date = Tk_Type_Date::createDate();
        /* @var $item Com_Xml_RssInterface */
        foreach ($this->list as $item) {
            $itemRepeat = $template->getRepeat('item');
            $url = new Tk_Type_Url('/');
            $itemRepeat->insertText('title', $item->getRssTitle());
            $description = str_replace('src="/', 'src="' . $url->toString(), $item->getRssDescr());
            $description = str_replace('href="/', 'href="' . $url->toString(), $description);
            $itemRepeat->insertText('description', $description);
            $itemRepeat->insertText('link', $item->getRssLink()->toString());
            if (method_exists($item, 'getCreated')) {
                $itemRepeat->insertText('created', $item->getCreated()->toString('r'));
            } else {
                $itemRepeat->insertText('created', $date->toString('r'));
            }
            $itemRepeat->appendRepeat();
        }
        if (count($this->list) > 0) {
            $item = current($this->list);
            if (method_exists($item, 'getCreated')) {
                $template->insertText('now', $item->getCreated()->toString('r'));
            } else {
                $template->insertText('now', $date->toString('r'));
            }
        }
        header('Content-type: text/xml');
        echo $template->toString();
        exit();
    }

}
<?php
namespace App\Helper;

use App\Db\Content;
use App\Db\Page;

/**
 * Class ContentFormatter
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class HtmlFormatter
{
    /**
     * @var boolean
     */
    protected $isView = true;
    
    /**
     * @var string
     */
    protected $html = null;
    
    /**
     * @var \DOMDocument
     */
    protected $doc = null;


    /**
     * __construct
     *
     * @param string $html
     * @param bool $isView Set this to false when parsing in edit mode
     * @throws \Tk\Exception
     */
    public function __construct($html, $isView = true)
    {
        $this->isView = $isView;
        $this->html = $this->parse($html);
    }

    /**
     * @param $html
     * @return string
     * @throws \Tk\Exception
     */
    protected function parse($html)
    {
        if (!$html) return $html;
        // Tidy HTML if available
        $html = '<div>' . $html . '</div>';
        $html = $this->cleanHtml($html);
        $this->doc = $this->parseDomDocument($html);
        $this->parseLinks($this->doc);
    }

    /**
     * return the formatted DOM HTML 
     * 
     * @return string
     */
    public function getHtml()
    {
        return trim(substr($this->doc->saveXML($this->doc->documentElement), 5, -6));
    }

    /**
     * 
     * @return \DOMDocument
     */
    public function getDocument() 
    {
        return $this->doc;
    }

    
    /**
     * Try to clean the wiki page and make it as XHTML compliant as possible.
     *
     * @param string $html
     * @return string
     */
    protected function cleanHtml($html)
    {
        $html = \Tk\Str::numericEntities($html);
        if (class_exists('tidy')) {
            $config = array(
                'numeric-entities' => true,
                'output-xhtml' => true,
                'tab-size' => 2,
                'indent-spaces' => 2,
                'wrap' => 200,
                'drop-empty-paras' => false,
                'drop-proprietary-attributes' => false,
                'fix-backslash' => true,
                'fix-bad-comments' => true,
                'logical-emphasis' => true,
                'vertical-space' => true,
                'char-encoding' => 'utf8',
                'force-output' => true,
                'add-xml-space' => true
            );
            $tidy = new \tidy();
            $html = $tidy->repairString($html, $config);
            // Remove head and foot of xhtml output
            $html = substr($html, stripos($html, '<body>')+6, - (strlen($html) - strripos($html, '</body>')));
        }
        return $html;
    }

    /**
     * getParsedText
     *
     * @param string $html
     * @return \DOMDocument
     * @throws \Tk\Exception
     */
    protected function parseDomDocument($html)
    {
        $doc = new \DOMDocument();
        if (!$doc->loadXML($html)) {
            throw new \Tk\Exception('Cannot format page content. Contact Administrator');
        }
        return $doc;
    }
    
    /**
     * Parse the links and add wiki classes:
     *  o dk-wikiPage - Standard wiki page link
     *  o dk-newWikiPage - A page that is yet to be created
     *  o dk-externalLink - An anchor to an external url
     *
     * @param \DOMDocument $doc
     * @return \DOMDocument
     */
    protected function parseLinks($doc)
    {
        $nodeList = $doc->getElementsByTagName('a');
        /** @var \DOMElement $node */
        foreach ($nodeList as $node) {
            $regs = array();

            // TODO: See the TinyMce event NodeChange() in the tkWiki.js
            //  we need to to this in the app not on the client.... 
            //$('script', ed.getDoc()).attr('data-jsl-static', 'data-jsl-static');



            if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                
                $page = \App\Db\PageMap::create()->findByUrl($regs[1]);
                if ($this->isView) {
                    $url = new \Tk\Uri('/' . $regs[1]);
                    $node->setAttribute('href', $url->getRelativePath());
                }
                
                if ($page) {
                    $css = '';
                    if ($this->isView) {
                        if (\App\Auth\Acl::create(\App\Factory::getConfig()->getUser())->canView($page)) {
                            $css = ' wiki-canView';
                        } else {
                            $css = ' wiki-notView disabled';
                        }
                    }
                    $node->setAttribute('class', $this->addClass($node->getAttribute('class'), 'wiki-page').$css);
                    $node->setAttribute('class', $this->removeClass($node->getAttribute('class'), 'wiki-page-new'));
                } else {
                    $node->setAttribute('class', $this->addClass($node->getAttribute('class'), 'wiki-page-new'));
                    $node->setAttribute('class', $this->removeClass($node->getAttribute('class'), 'wiki-page'));
                }
            } else if ($this->isView && preg_match('/^http|https|ftp|telnet|gopher|news/i', $node->getAttribute('href'), $regs)) {
                //TODO: should this be a config option to open external links in new window???
                $url = new \Tk\Uri($node->getAttribute('href'));
                if (strtolower(str_replace('www.', '', $url->getHost())) != strtolower(str_replace('www.', '',$_SERVER['HTTP_HOST'])) ) {
                    $node->setAttribute('class', $this->addClass($node->getAttribute('class'), 'wiki-link-external'));
                    $node->setAttribute('target', '_blank');
                }
            }
        }
        return $doc;
    }

    /**
     * addClass
     * 
     * @param $classString
     * @param $class
     * @return string
     */
    protected function addClass($classString, $class)
    {
        $arr = explode(' ', $classString);
        $arr = array_flip($arr);
        $arr[$class] = $class;
        $arr = array_flip($arr);
        return trim(implode(' ', $arr));
    }

    /**
     * removeClass
     * 
     * @param $classString
     * @param $class
     * @return string
     */
    protected function removeClass($classString, $class)
    {
        $arr = explode(' ', $classString);
        $arr = array_flip($arr);
        unset($arr[$class]);
        $arr = array_flip($arr);
        return trim(implode(' ', $arr));
    }
    
    
}
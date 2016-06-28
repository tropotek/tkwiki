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
     */
    public function __construct($html)
    {
        $this->html = $html;
        // Tidy HTML if available
        $html = $this->cleanHtml('<div class="wiki-dom-content">' . $this->html . '</div>');
        $this->doc = $this->parseDomDocument($html);
    }

    /**
     * return the formatted DOM HTML 
     * 
     * @return string
     */
    public function getFormattedHtml()
    {
        return $this->doc->saveXML();
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
                'drop-font-tags' => true,
                'drop-empty-paras' => false,
                'drop-proprietary-attributes' => true,
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
     */
    protected function parseDomDocument($html)
    {
        $doc = new \DOMDocument();
        if (!$doc->loadXML($html)) {
            $doc->loadXML('<div role="alert" class="alert alert-danger"> <strong>Oh snap!</strong> Error Parsing Wiki Page!</div>');
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
     * @todo This needs to be verified after the edit page is working.
     */
    protected function parseLinks($doc)
    {
        $nodeList = $doc->getElementsByTagName('a');
        /** @var \DOMElement $node */
        foreach ($nodeList as $node) {
            $regs = array();
            if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                
                $url = new \Tk\Uri('/' . $regs[1]);
                $page = \App\Db\Page::getMapper()->findByUrl($regs[1]);
                
                if ($page) {
                    $node->setAttribute('class', 'wiki-page');
                } else {
                    $node->setAttribute('class', 'wiki-new-page');
                }
                $node->setAttribute('href', $url->toString());
            } else if (preg_match('/^http|https|ftp|telnet|gopher|news/i', $node->getAttribute('href'), $regs)) {
                $url = new \Tk\Uri($node->getAttribute('href'));
                if ($url->getHost() != $_SERVER['HTTP_HOST']) {
                    $node->setAttribute('class', 'wiki-link-external');
                    $node->setAttribute('target', '_blank');
                }
            }
        }
        return $doc;
    }
}
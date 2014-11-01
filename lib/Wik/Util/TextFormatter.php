<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 */

/**
 *
 *
 * @package Util
 */
class Wik_Util_TextFormatter extends Tk_Object
{
    
    /**
     * @var Wik_Db_Text
     */
    protected $text = null;
    
    /**
     * @var DOMDocument
     */
    protected $doc = null;
    
    
    /**
     * __construct
     *
     * @param Wik_Db_Text
     */
    function __construct(Wik_Db_Text $text)
    {
        $this->text = $text;
        
        // Tidy text if available
        $html = $this->text->getText();
        $html = '<div class="dk-wikiContent">' . $html . '</div>';
        $html = $this->cleanHtml($html);
        
        // Create Dom Document
        $this->doc = $this->parseDomDocument($html);
        
        // Parse all link tags
        $this->doc = $this->parseLinks($this->doc);
        
        // Parse Contents box
        $this->doc = $this->parseContents($this->doc);
        
    }
    
    /**
     * Create a formatter object
     *
     * @return Wik_Util_TextFormatter
     */
    static function create(Wik_Db_Text $text)
    {
        return new self($text);
    }
    
    /**
     * Try to clean the wiki page and make it as XHTML compliant as possible.
     *
     * @param string $html
     * @return string
     */
    protected function cleanHtml($html)
    {
        $html = numericEntities($html);
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
            $tidy = new tidy();
            $html = $tidy->repairString($html, $config);
            // Remove head and foot of xhtml output
            $html = substr($html, stripos($html, '<body>')+6, - (strlen($html) - strripos($html, '</body>')));
        }
        
        return $html;
    }
    
    /**
     * Parse out all the h1-h6 tags and create a contents box
     *
     * @param DOMDocument $doc
     * @return DOMDocument
     * @TODO: Create function
     */
    protected function parseContents($doc)
    {
        return $doc;
    }
    
    /**
     * Parse the links and add wiki classes:
     *  o dk-wikiPage - Standard wiki page link
     *  o dk-newWikiPage - A page that is yet to be created
     *  o dk-externalLink - An anchor to an external url
     *
     * @param DOMDocument $doc
     * @return DOMDocument
     */
    protected function parseLinks($doc)
    {
        $nodeList = $doc->getElementsByTagName('a');
        foreach ($nodeList as $node) {
            $regs = array();
            if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                $url = new Tk_Type_Url('/page/' . $regs[1]);
                $page = Wik_Db_PageLoader::findByName($regs[1]);
                if ($page) {
                    $node->setAttribute('class', 'dk-wikiPage');
                } else {
                    $node->setAttribute('class', 'dk-newWikiPage');
                }
                $node->setAttribute('href', $url->toString());
            } else if (preg_match('/^http|https|ftp|telnet|gopher|news/i', $node->getAttribute('href'), $regs)) {
                $url = new Tk_Type_Url($node->getAttribute('href'));
                if ($url->getHost() != $_SERVER['HTTP_HOST']) {
                    $node->setAttribute('class', 'dk-externalLink');
                    $node->setAttribute('target', '_blank');
                }
            }
        }
        return $doc;
    }
    
    /**
     * getParsedText
     *
     * @param string $html
     * @return DOMDocument
     */
    protected function parseDomDocument($html)
    {
        $doc = new DOMDocument();
        if (!$doc->loadXML($html)) {
            $doc->loadXML('<div class="dk-wikiContentError"><p>Error Parsing Wiki Page!</p></div>');
        }
        return $doc;
    }
    
    
    

    /**
     * Get the text's dom document
     *
     * @return DOMDocument
     */
    function getDomDocument()
    {
       return $this->doc;
    }
    
    /**
     * Get the formatted HTML string
     *
     * @return string
     */
    function getHtmlText()
    {
        return $this->doc->saveHTML();
    }
    
    /**
     * Get the original non-formatted wiki text
     *
     * @return string
     */
    function getText()
    {
        return $this->text->getText();
    }
    
    
    
}
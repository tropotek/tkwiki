<?php
namespace App\Helper;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class HtmlFormatter
{
    /**
     * @var boolean
     */
    protected $viewMode = true;

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
     * @param bool $viewMode Set this to false when editing a page.
     * @throws \Exception
     */
    public function __construct($html, $viewMode = true)
    {
        $this->setViewMode($viewMode);
        $this->html = $this->parse($html);
    }

    /**
     * @param $html
     * @return string
     * @throws \Exception
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
        $html = $this->doc->saveXML($this->doc->documentElement);
        $html = trim(str_replace(array('<html><body>', '</body></html>'), '', $html));
        $html = trim(substr($html, 5, -6));

        return $html;
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

        // TODO: Tidy is disabled until we can figure out a way for tinymce and tidy to worktogether.
        // TODO:   - IE having major issues with the ifame end tag.....
        // TODO:   - All form input tags and any scripts cause it to produce non XHTML compliant markup
        // TODO: For now the MCE editor seems to format the markup fine so lets just use that.
        if (false) {
        //if (class_exists('tidy')) {
            $config = array(
                //'output-xml' => true,
                'numeric-entities' => true,                      // This option specifies if Tidy should output entities other than the built-in HTML entities (&amp;, &lt;, &gt; and &quot;) in the numeric rather than the named entity form.
                'drop-empty-paras' => false,
                //'hide-endtags' => false,                         // This option specifies if Tidy should omit optional end-tags when generating the pretty printed markup. This option is ignored if you are outputting to XML.
                //'new-empty-tags' => 'iframe,i,span,div',       // This option specifies new empty inline tags. This option takes a space or comma separated list of tag names.
                                                                 // Unless you declare new tags, Tidy will refuse to generate a tidied file if the input includes previously unknown tags.
                                                                 // Remember to also declare empty tags as either inline or blocklevel. This option is ignored in XML mode.

                'fix-backslash' => true,            // This option specifies if Tidy should replace backslash characters "\" in URLs by forward slashes "/"
                'tab-size' => 2,
                'indent-spaces' => 2,
                'wrap' => 200,
                'logical-emphasis' => true,         // This option specifies if Tidy should replace any occurrence of <I> by <EM> and any occurrence of <B> by <STRONG>
                'vertical-space' => true,           // This option specifies if Tidy should add some empty lines for readability.
                'char-encoding' => 'utf-8',
                'force-output' => true,
                'add-xml-space' => true            // This option specifies if Tidy should add xml:space="preserve" to elements such as <PRE>, <STYLE>
            );
            $tidy = new \tidy();

            $html = $tidy->repairString($html, $config);
            // Remove head and foot of xhtml output
            $html = trim(substr($html, stripos($html, '<body>')+6, - (strlen($html) - strripos($html, '</body>'))));
        }
        return $html;
    }

    /**
     * @param string $html
     * @return \DOMDocument
     * @throws \Tk\Exception
     */
    protected function parseDomDocument($html)
    {
        $doc = new \DOMDocument('1.0', 'utf-8');
        libxml_use_internal_errors(true);
        if (!$doc->loadXML($html)) {
            $str = '';
            foreach (libxml_get_errors() as $error) {
                $str .= sprintf("\n[%s:%s] %s", $error->line, $error->column, trim($error->message));
            }
            libxml_clear_errors();
            $str .= "\n\n" . \Tk\Str::lineNumbers($html) . "\n";
            $e = new \Tk\Exception('Error Parsing DOM Template', 0, null, $str);
            throw $e;
        }
        return $doc;
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
     * Parse the links and add wiki classes:
     *  o dk-wikiPage - Standard wiki page link
     *  o dk-newWikiPage - A page that is yet to be created
     *  o dk-externalLink - An anchor to an external url
     *
     * @param \DOMDocument $doc
     * @return \DOMDocument
     * @throws \Exception
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
                if ($this->isViewMode()) {
                    $url = new \Tk\Uri('/' . $regs[1]);
                    $node->setAttribute('href', $url->getRelativePath());
                }

                if ($page) {
                    $css = '';
                    if ($this->isViewMode()) {
                        if (\App\Auth\Acl::create(\App\Config::getInstance()->getAuthUser())->canView($page)) {
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
            } else if ($this->isViewMode() && preg_match('/^http|https|ftp|telnet|gopher|news/i', $node->getAttribute('href'), $regs)) {
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

    /**
     * @return bool
     */
    public function isViewMode()
    {
        return $this->viewMode;
    }

    /**
     * @param bool $viewMode
     * @return HtmlFormatter
     */
    public function setViewMode(bool $viewMode)
    {
        $this->viewMode = $viewMode;
        return $this;
    }

    /**
     * @return \DOMDocument
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * @param \DOMDocument $doc
     * @return HtmlFormatter
     */
    public function setDoc(\DOMDocument $doc)
    {
        $this->doc = $doc;
        return $this;
    }


}

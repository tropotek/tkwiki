<?php
namespace App\Helper;

use Tk\Traits\SystemTrait;

class HtmlFormatter
{
    use SystemTrait;

    protected bool $isView = true;

    protected string $html = '';

    protected ?\DOMDocument $doc = null;


    public function __construct(string $html, bool $isView = true)
    {
        $this->isView = $isView;
        $this->html = $this->parse($html);
    }

    protected function parse(string $html): string
    {
        if (!$html) return $html;
        // Tidy HTML if available
        $html = '<div>' . $html . '</div>';
        //$html = $this->cleanHtml($html);
        $this->doc = $this->parseDomDocument($html);
        $this->parseLinks($this->getDocument());
        return $html;
    }

    /**
     * return the formatted DOM HTML
     */
    public function getHtml(): string
    {
        //if (!$this->doc) return '';

        $html = $this->getDocument()->saveHTML($this->getDocument()->documentElement);
        //$html = trim(str_replace(array('<html><body>', '</body></html>'), '', $html));
        //$html = trim(substr($html, 5, -6));
vd($html);
        return $html;
    }

    protected function parseDomDocument(string $html): \DOMDocument
    {
        $doc = new \DOMDocument('1.0', 'utf-8');
        libxml_use_internal_errors(true);
        if (!$doc->loadHTML($html)) {
            $str = '';
            foreach (libxml_get_errors() as $error) {
                $str .= sprintf("\n[%s:%s] %s", $error->line, $error->column, trim($error->message));
            }
            libxml_clear_errors();
            $str .= "\n\n" . \Tk\Str::lineNumbers($html) . "\n";
            throw new \Tk\Exception('Error Parsing DOM Template', 0, null, $str);
        }
        return $doc;
    }

    public function getDocument(): ?\DOMDocument
    {
        return $this->doc;
    }

    /**
     * Parse the links and add wiki classes
     */
    protected function parseLinks(\DOMDocument $doc): \DOMDocument
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
                        if ($page->canView($this->getFactory()->getAuthUser())) {
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

    protected function addClass(string $classString, string $class): string
    {
        $arr = explode(' ', $classString);
        $arr = array_flip($arr);
        $arr[$class] = $class;
        $arr = array_flip($arr);
        return trim(implode(' ', $arr));
    }

    protected function removeClass(string $classString, string $class): string
    {
        $arr = explode(' ', $classString);
        $arr = array_flip($arr);
        unset($arr[$class]);
        $arr = array_flip($arr);
        return trim(implode(' ', $arr));
    }

}

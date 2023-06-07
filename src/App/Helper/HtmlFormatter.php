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
        $html = mb_convert_encoding($html, 'UTF-8');
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
        $html = $this->getDocument()->saveHTML($this->getDocument()->documentElement);
        $html = trim(str_replace(['<html><body><div>', '</div></body></html>'], '', $html));
        $html = htmlspecialchars_decode(htmlentities($html, ENT_COMPAT, 'utf-8', false));
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
            $css = $node->getAttribute('class');
            $href = $node->getAttribute('href');
            if (preg_match('/^page:\/\/(.+)/i', $href, $regs)) {
                $page = \App\Db\PageMap::create()->findByUrl($regs[1]);
                if ($this->isView) {
                    $url = new \Tk\Uri('/' . $regs[1]);
                    $node->setAttribute('href', $url->getRelativePath());
                }

                if ($page) {
                    $css = $this->addClass($css, 'wk-page');
                    $css = $this->removeClass($css, 'wk-page-new');
                    if ($this->isView) {
                        if (!$page->canView($this->getFactory()->getAuthUser())) {
                            $css = $this->addClass($css, 'wk-page-disable');
                            $node->setAttribute('title', 'Invalid Permission');
                        }
                    }
                } else {
                    $css = $this->addClass($css, 'wk-page-new');
                    $css = $this->removeClass($css, 'wk-page');
                }
            } else if ($this->isView && preg_match('/^http|https|ftp|telnet|gopher|news/i', $href, $regs)) {
                $url = new \Tk\Uri($node->getAttribute('href'));
                if (strtolower(str_replace('www.', '', $url->getHost())) != strtolower(str_replace('www.', '',$_SERVER['HTTP_HOST'])) ) {
                    $css = $this->addClass($css, 'wk-link-external');
                    $node->setAttribute('target', '_blank');
                }
            }
            $node->setAttribute('class', $css);
        }
        return $doc;
    }

    protected function addClass(string $classString, string $class): string
    {
        $arr = explode(' ', trim($classString));
        $arr = array_flip($arr);
        $arr[$class] = $class;
        $arr = array_flip($arr);
        return trim(implode(' ', $arr));
    }

    protected function removeClass(string $classString, string $class): string
    {
        $arr = explode(' ', trim($classString));
        $arr = array_flip($arr);
        unset($arr[$class]);
        $arr = array_flip($arr);
        return trim(implode(' ', $arr));
    }

}

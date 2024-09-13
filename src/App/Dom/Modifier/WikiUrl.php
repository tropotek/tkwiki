<?php
namespace App\Dom\Modifier;

use App\Db\Page;
use Bs\Traits\SystemTrait;
use Dom\Modifier\FilterInterface;

/**
 * This modifier changes link nodes:
 *  - Map all wiki page://title URI's to URI's that link to the wiki view page (eg: /{baseUrl}/title)
 *  - Update classes for external links
 *
 */
class WikiUrl extends FilterInterface
{
    use SystemTrait;

    public function executeNode(\DOMElement $node): void
    {
        // Modify wiki URLs
        if ($node->nodeName != 'a') return;

        $user = $this->getAuthUser();
        $css = $node->getAttribute('class');
        $href = $node->getAttribute('href');

        if (preg_match('/^page:\/\/(.+)/i', $href, $regs)) {
            $page = Page::findByUrl($regs[1]);
            $url = new \Tk\Uri('/' . $regs[1]);
            $node->setAttribute('href', $url->getPath());

            if ($page) {
                $css = $this->addClass($css, 'wk-page');
                $css = $this->removeClass($css, 'wk-page-new');
                if (!($page->canView($user) && $page->published)) {
                    $css = $this->addClass($css, 'wk-page-disable');
                    $node->setAttribute('title', 'Invalid Permission');
                    $node->setAttribute('href', '#');
                }
            } else {
                $css = $this->addClass($css, 'wk-page-new');
                if (!$user) {
                    $css = $this->addClass($css, 'wk-page-disable');
                }
                $css = $this->removeClass($css, 'wk-page');
            }
        } else if (preg_match('/^http|https|ftp|telnet|gopher|news/i', $href, $regs)) {
            $url = new \Tk\Uri($node->getAttribute('href'));
            if (strtolower(str_replace('www.', '', $url->getHost())) != strtolower(str_replace('www.', '', $_SERVER['HTTP_HOST'])) ) {
                $css = $this->addClass($css, 'wk-link-external');
                $node->setAttribute('target', '_blank');
            }
        }
        $node->setAttribute('class', $css);
    }

    function init(\DOMDocument $doc) { }
}
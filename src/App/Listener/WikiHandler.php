<?php

namespace App\Listener;

use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class WikiHandler implements Subscriber
{
    
    /**
     * @param \App\Event\ContentEvent $event
     */
    public function contentPreRender(\App\Event\ContentEvent $event)
    {
        $content = $event->getContent();
        try {
            $formatter = new \App\Helper\HtmlFormatter($content->html);
            $event->set('htmlFormatter', $formatter);
            // Format the content html
            $content->html = $formatter->getHtml();
        } catch (\Exception $e) {
            \Tk\Log::error($e->__toString());
            $content->html = '<div role="alert" class="alert alert-danger"><strong>Error:</strong> '.$e->getMessage().'</div>';
        }
    }
    
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \App\WikiEvents::WIKI_CONTENT_VIEW => array('contentPreRender', 10)
        );
    }

}
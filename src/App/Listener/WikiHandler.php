<?php

namespace App\Listener;

use Psr\Log\LoggerInterface;
use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\ControllerResultEvent;
use Tk\Event\ControllerEvent;
use Tk\Kernel\KernelEvents;

/**
 * Class ShutdownHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class WikiHandler implements SubscriberInterface
{

    
    /**
     *
     * @param \App\Event\ContentEvent $event
     */
    public function contentPreRender(\App\Event\ContentEvent $event)
    {
        vd('------------- '.\App\Events::WIKI_CONTENT_VIEW.' -------------');
        
        $content = $event->getContent();
        $formatter = new \App\Helper\HtmlFormatter($content->html);
        $event->set('htmlFormatter', $formatter);
        // Format the content html
        $content->html = $formatter->getFormattedHtml();
        
    }


    /**
     * getSubscribedEvents
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \App\Events::WIKI_CONTENT_VIEW => ['contentPreRender', 10]
        );
    }

}
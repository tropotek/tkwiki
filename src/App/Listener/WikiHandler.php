<?php
namespace App\Listener;

use App\Event\ContentEvent;
use App\Helper\HtmlFormatter;
use App\WikiEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tk\Log;

class WikiHandler implements EventSubscriberInterface
{

    public function onContentView(ContentEvent $event): void
    {
        $content = $event->getContent();
        try {
            if (trim($content->getHtml())) {
                $formatter = new HtmlFormatter($content->getHtml());
                // Format the content html
                $content->setHtml($formatter->getHtml());
            }
        } catch (\Exception $e) {
            Log::error($e->__toString());
            $content->setHtml('<div role="alert" class="alert alert-danger"><strong>Error:</strong> '.$e->getMessage().'</div>');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            WikiEvents::WIKI_CONTENT_VIEW => array('onContentView', 10)
        );
    }

}

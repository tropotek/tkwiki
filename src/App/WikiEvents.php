<?php
namespace App;

class WikiEvents
{

    /**
     * This event is fired before the content->html is inserted into 
     * page template for rendering.
     * use this to modify the content object before rendering
     * 
     * @event \App\Event\ContentEvent
     */
    const WIKI_CONTENT_VIEW = 'wiki.content.view';

}
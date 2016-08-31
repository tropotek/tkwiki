<?php
namespace App;

/**
 * Class Events
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class WikiEvents
{


    /**
     * This event is fired before the content->html is inserted into 
     * page template for rendering.
     * 
     * use this to modify the content object before rendering
     * 
     * @event \App\Event\ContentEvent
     * @var string
     */
    const WIKI_CONTENT_VIEW = 'wiki.content.view';
    
    
    
    
    

}
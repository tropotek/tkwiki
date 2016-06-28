<?php
namespace App\Event;

use Tk\Event\ControllerEvent;
use Tk\Request;
use App\Db\Content;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class ContentEvent extends ControllerEvent
{
    /**
     * @var Content
     */
    private $content = null;


    /**
     * __construct
     *
     * @param Content $content
     * @param mixed $controller
     * @param Request $request
     */
    public function __construct($content, $controller, $request)
    {
        parent::__construct($controller, $request);
        $this->content = $content;
    }

    /**
     * 
     * 
     * @return \DOMDocument
     */
    public function getContentDoc()
    {
        return $this->doc;
    }

    /**
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }
}
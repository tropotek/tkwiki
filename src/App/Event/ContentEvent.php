<?php
namespace App\Event;

use Tk\EventDispatcher\Event;
use Tk\Request;
use App\Db\Content;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class ContentEvent extends Event
{
    /**
     * @var Content
     */
    private $content = null;


    /**
     * __construct
     * @param Content $content
     */
    public function __construct($content)
    {
        parent::__construct();
        $this->content = $content;
    }

    /**
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }
}
<?php
namespace App\Event;

use App\Db\Content;
use Symfony\Contracts\EventDispatcher\Event;

class ContentEvent extends Event
{
    private Content $content;


    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}
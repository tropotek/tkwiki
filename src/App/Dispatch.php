<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Dispatch extends \Bs\Dispatch
{


    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $dispatcher = $this->getDispatcher();

        $dispatcher->addSubscriber(new \App\Listener\WikiHandler());

    }

}
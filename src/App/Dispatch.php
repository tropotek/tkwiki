<?php
namespace App;

use App\Listener\RequestHandler;
use App\Listener\WikiHandler;
use Bs\Listener\CrumbsHandler;

class Dispatch extends \Bs\Dispatch
{

    /**
     * Any Common listeners that are used in both HTTPS or CLI requests
     */
    protected function commonInit()
    {
        parent::commonInit();


    }

    /**
     * Called this when executing http requests
     */
    protected function httpInit()
    {
        parent::httpInit();

        $this->getDispatcher()->addSubscriber(new WikiHandler());
        $this->getDispatcher()->addSubscriber(new RequestHandler());
        $this->getDispatcher()->addSubscriber(new CrumbsHandler());

    }

    /**
     * Called this when executing Console/CLI requests
     */
    protected function cliInit()
    {
        parent::cliInit();


    }

}
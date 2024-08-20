<?php
namespace App;


class Dispatch extends \Bs\Dispatch
{

    /**
     * Any Common listeners that are used in both HTTPS or CLI requests
     */
    protected function commonInit()
    {
        parent::commonInit();
        vd('DELETE THIS FILE IF NOT USED');
    }

    /**
     * Called this when executing http requests
     */
    protected function httpInit()
    {
        parent::httpInit();
    }

    /**
     * Called this when executing Console/CLI requests
     */
    protected function cliInit()
    {
        parent::cliInit();
    }

}
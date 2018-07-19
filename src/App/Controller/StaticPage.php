<?php
namespace App\Controller;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StaticPage extends Iface
{



    public function __construct()
    {
        $this->setPageTitle('');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('');
        //$page = new \App\Page\Page($this, $request->getAttribute('_staticPath'));
        vd($request->getAttributes());

        $page = new \App\Page\Page();
        
        return $page;
    }

}
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


    /**
     * @param Request $request
     * @return mixed
     */
    public function doDefault(Request $request)
    {
        //$page = new \App\Page\Page($this, $request->getAttribute('_staticPath'));
        vd($request->getAttributes());
        $page = new \App\Page\Page();
        
        return $page;
    }

}
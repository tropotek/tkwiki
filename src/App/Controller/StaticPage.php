<?php
namespace App\Controller;

use Tk\Request;
/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StaticPage extends Iface
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function doDefault(Request $request)
    {
        $page = new \App\Page\PublicPage($this, $request->getAttribute('_staticPath'));
        
        return $page;
    }

}
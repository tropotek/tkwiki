<?php
namespace App\Controller\Page;

use Tk\Request;
use App\Controller\Iface;

/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class View extends Iface
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Wiki Page....');
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doDefault(Request $request, $name)
    {
        $this->setPageTitle($name);
        // TODO: 
        //throw new \Tk\Exception('This page should neot need a controller...its a wiki DOPE!!!');
        

        return $this->showDefault($request);
    }


    /**
     * Note: no longer a dependacy on show() allows for many show methods for many 
     * controller methods (EG: doAction/showAction, doSubmit/showSubmit) in one Controller object
     * 
     * @param Request $request
     * @return \App\Page\PublicPage
     */
    public function showDefault(Request $request)
    {
        $template = $this->getTemplate();
        
        return $this->getPage()->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>
  <p>This is a new wiki page</p>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}
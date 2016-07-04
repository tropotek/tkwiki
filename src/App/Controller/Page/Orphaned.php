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
class Orphaned extends Iface
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
     * @return \App\Page\Iface
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        
        
        
        
        return $this->show($request);
    }


    /**
     * Note: no longer a dependency on show() allows for many show methods for many 
     * controller methods (EG: doAction/showAction, doSubmit/showSubmit) in one Controller object
     * 
     * @param Request $request
     * @return \App\Page\PublicPage
     * @todo Look at implementing a cache for page views.
     */
    public function show(Request $request)
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
<div class="wiki-orphaned">
  
  <div var="table"></div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}
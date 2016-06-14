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
class Index extends Iface
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Home');
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doDefault(Request $request)
    {
        // TODO: 
        
        
        
        
        

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
        $tplFile = $this->getTemplatePath().'/xtpl/index.xtpl';
        return \Dom\Loader::loadFile($tplFile);
    }

}
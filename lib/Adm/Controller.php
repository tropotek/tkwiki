<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The admin front controller.
 * Must be used in conjunction with the Com lib.
 *
 * @package Adm
 */
class Adm_Controller implements Tk_Util_ControllerInterface
{
    /**
     * @var Com_Web_ComponentController
     */
    protected $comCon = null;
    
    
    
    /**
     * __construct
     *
     * @param Com_Web_ComponentController $comCon
     */
    function __construct(Com_Web_ComponentController $comCon)
    {
        $this->comCon = $comCon;
    }
    
    /**
     * This method called before the execute() method
     *
     */
    function init()
    {
        
    }
    
    /**
     * Execute the controller
     *
     */
    function execute()
    {
        $adminPath = Tk_Type_Url::createUrl(Com_Config::getInstance()->getAdminPath())->toString();
        if (strpos(Tk_Request::getInstance()->getRequestUri()->toString(), $adminPath) === false) {
            return;
        }
        
        $template = $this->comCon->getPageComponent()->getTemplate();
        Js_Ui_Jquery::create($template)->show();
        
        if (is_file(Com_Config::getTemplatePath().'/css/adminstyle.css')) {
            $template->appendCssUrl(Tk_Type_Url::create('/css/adminstyle.css'));
        }
        
        
        $template->setAttr('_adminHelp', 'href', Tk_Type_Url::create('/admin/help.html')->set('u', Tk_Request::requestUri()->reset()->toUriString()) );
        
        Js_Ui_JqBlockUi::create($template)->show();
        
        
        $url = Tk_Type_Url::createUrl('/lib/Js/Util.js');
        $template->appendJsUrl($url);
        
        $url = Tk_Type_Url::createUrl('/lib/Adm/js/adm.js');
        $template->appendJsUrl($url);
        
        $url = Tk_Type_Url::createUrl('/lib/Adm/css/adm.css');
        $template->appendCssUrl($url);
        
        $url = Tk_Type_Url::createUrl('/lib/Adm/css/print.css');
        $template->appendCssUrl($url, 'print');
        
        // Session keepalive
        $url = Tk_Type_Url::create('/index.html')->toString();
            $js = <<<JS
$(document).ready(function() {
  // Ping server when in admin to avoid session timeout
  window.setInterval(function() {
    $.get('$url');
  }, 3600000);
});
JS;
        $template->appendJs($js);
        
        $template->appendMetaTag('author', 'Michael Mifsud - info@tropotek.com.au');
        $template->appendMetaTag('developer', 'Tropotek Development');
        $template->appendMetaTag('copyright', '(c)' . date('Y') . ' tropotek.com.au');
        
    }

    /**
     * Do all post initalisation operations here
     * This method called after the execute method
     *
     */
    function postInit()
    {
        
    }
    
}
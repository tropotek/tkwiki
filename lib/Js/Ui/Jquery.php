<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Jquery Date selector plugin
 * 
 * @package Ui
 */
class Js_Ui_Jquery extends Dom_Renderer
{
    
    protected $jquery = '/lib/Js/jquery/jquery-1.6.1.min.js';
    
    protected $jqueryUi = '/lib/Js/jquery/jquery-ui-1.8.16.custom.min.js';
    
    protected $jqueryUiCss = '/lib/Js/jquery/themes/redmond/jquery-ui-1.8.13.custom.css';
    
    /**
     *
     * @param Dom_Template $template 
     */
    function __construct($template)
    {
        $this->setTemplate($template);
    }
    
    /**
     *
     * @param Dom_Template $template
     * @return Js_Ui_Jquery
     */
    static function create($template)
    {
        $o = new self($template);
        return $o;
    }
    
    /**
     * Set the relitive path to the jquery js source
     *
     * @param type $path 
     */
    function setJquery($path)
    {
        $this->jquery = $path;
    }
    
    /**
     * Set the relitive path to the jquery UI js source
     *
     * @param type $path 
     */
    function setJqueryUi($path)
    {
        $this->jqueryUi = $path;
    }
    
    /**
     * Set the relitive path to the jquery UI CSS source
     *
     * @param type $path 
     */
    function setJqueryUiCss($path)
    {
        $this->jqueryUiCss = $path;
    }
    
    
    
    /**
     * show
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $template->appendJsUrl(Tk_Type_Url::createUrl($this->jquery));
        $template->appendJsUrl(Tk_Type_Url::createUrl($this->jqueryUi));
        $template->appendCssUrl(Tk_Type_Url::createUrl($this->jqueryUiCss));
        
    }
}

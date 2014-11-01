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
class Js_Ui_JqDate extends Dom_Renderer
{
    
    protected $jSelector = '';
    
    function __construct($jSelector)
    {
        $this->jSelector = $jSelector;
    }
    
    function show()
    {
        $template = $this->getTemplate();
        
        
        $js = sprintf("
$(document).ready(function() {
    $('%s').datepicker({ dateFormat: 'dd/mm/yy' });
});", $this->jSelector);
        $template->appendJs($js);
    
    }
}
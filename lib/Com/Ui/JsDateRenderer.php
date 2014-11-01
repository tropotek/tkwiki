<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Jquery Date selector plugin
 *
 * Note: you must have the jquery and jquery-ui installed for this to work
 * It is considdered standard on all Com sites
 *
 * @package Com
 * @deprecated Use the Js/Ui/JqDate.php object in the JdkLib package
 */
class Com_Ui_JsDateRenderer extends Dom_Renderer
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
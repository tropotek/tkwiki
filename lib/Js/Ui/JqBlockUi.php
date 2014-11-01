<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A Jquery UI blocker with animated gif images.
 * NOTE: use $(this).unbind('click'); in an onclick event to cancel the blocker and any other attached events
 * 
 * Example:
 * <code>
 *   Js_Ui_JqBlockUi::create($template, 'input[type="submit"], input[type="image"], a')->show();
 * </code>
 * 
 * @package Ui
 */
class Js_Ui_JqBlockUi extends Dom_Renderer
{
    
    private $selector = '';
    private $notSelector = 'a[target], a[href^="#"], a[href^="javascript:"]';
    
    
    /**
     * 
     *
     * @param Dom_Template $template 
     */
    function __construct($template)
    {
        $this->setTemplate($template);
    }
    
    /**
     * create
     * 
     * @param Dom_Template $template
     * @return Js_Ui_Jquery
     */
    static function create($template, $selector = 'input[type=submit], input[type=image], .submit a, a, .uiblocker')
    {
        $o = new self($template);
        $o->selector = $selector;
        return $o;
    }
    
    function setNotSelector($selector)
    {
        $this->notSelector = $selector;
    }
    
    /**
     * show
     */
    function show()
    {
        $template = $this->getTemplate();
        $img = Tk_Type_Url::create('/lib/Js/jquery/plugins/BlockImg.gif')->toString();
        $url = Tk_Type_Url::create('/lib/Js/jquery/plugins/BlockBg.png'); 
        $bg = $url->toString();
        $template->appendJsUrl( Tk_Type_Url::create('/lib/Js/jquery/plugins/jquery.blockUI.js'));
        $js = <<<JS
var blockImg1 = new Image();
var blockImg2 = new Image();
blockImg1.src = '$img';
blockImg2.src = '$bg';

function uOn() {
  $.blockUI({ 
    message: '<div><p style="line-height: 45px;text-align: center;font-size: 16px;"><img src="$img" style="vertical-align: middle;" /> &#160; Please Wait... Loading</p></div>', 
    overlayCSS: { backgroundColor: '#333', opacity: 0.3 },
    css: { border: '1px solid #CCC', textAlign: 'center', width: '321px', height: '55px', background: 'transparent url(\'$bg\') 0px 0px no-repeat'}
  });
  setTimeout($.unblockUI, 20000); // unblock after 10sec
}
function uOff() {
  $.unblockUI;
}
$(document).ready(function() {
  $('{$this->selector}').not('{$this->notSelector}').click( function () { uOn(); } );
});
JS;
        $template->appendJs($js);
    }
}
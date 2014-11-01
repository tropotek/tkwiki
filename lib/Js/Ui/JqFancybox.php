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
class Js_Ui_JqFancybox extends Dom_Renderer
{
    /**
     * Selector
     * @var type
     */
    //protected $selector = ".iframe, .lightbox[href$='jpg'], .lightbox[href$='jpeg'], .lightbox[href$='gif'], .lightbox[href$='png'], .lightbox[href$='swf']";
    protected $selector = ".iframe, .lightbox";


    protected $options = "";

    /**
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
    static function create($template, $selector = '', $opts = '')
    {
        $o = new self($template);
        if($opts) {
            $o->options = ','.$opts;
        }
        if ($selector)
            $o->selector = $selector;
        return $o;
    }

    function getSelector()
    {
        if ($this->selector) {
            return $this->selector;
        }
        return '';
    }

    /**
     * show
     */
    function show()
    {
        $template = $this->getTemplate();
        $template->appendJsUrl(Tk_Type_Url::create('/lib/Js/jquery/plugins/fancybox/jquery.fancybox-1.3.4.pack.js'));
        $template->appendCssUrl(Tk_Type_Url::create('/lib/Js/jquery/plugins/fancybox/jquery.fancybox-1.3.4.css'));

        $js = <<<JS
$(document).ready(function() {

  $("{$this->getSelector()}").unbind('click').fancybox({
      'transitionIn'  :   'elastic',
      'transitionOut' :   'elastic',
      'speedIn'       :   600,
      'speedOut'      :   200,
      'overlayShow'   :   true,
      'hideOnOverlayClick' : true,
      'hideOnContentClick' : true
      {$this->options}
  });
});
JS;
        $template->appendJs($js);
    }
}
<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * To see the available parameter documentation
 * @see http://omnipotent.net/jquery.sparkline/
 * 
 * Common Options
 * 
 *  o type - line (default), bar, tristate, discrete, bullet, pie or box
 *  o width - Width of the chart - Defaults to 'auto' - May be any valid css width - 1.5em, 
 *            20px, etc (using a number without a unit specifier won't do what you want) - 
 *            This option does nothing for bar and tristate chars (see barWidth)
 *  o height - Height of the chart - Defaults to 'auto' (line height of the containing tag)
 *  o lineColor - Used by line and discrete charts
 *  o fillColor - Set to false to disable fill.
 *  o chartRangeMin - Specify the minimum value to use for the range of the chart - Defaults to the minimum value supplied
 *  o chartRangeMax - Specify the maximum value to use for the range of the chart - Defaults to the maximum value supplied
 *  o composite - If true then don't erase any existing chart attached to the tag, 
 *                but draw another chart over the top - Note that width and height are ignored if an existing chart is detected.
 *  o enableTagOptions - If true then options can be specified as attributes on each tag to be transformed into 
 *                       a sparkline, as well as passed to the sparkline() function. See also tagOptionPrefix
 *  o tagOptionPrefix - String that each option passed as an attribute on a tag must begin with. Defaults to 'spark'
 *  o tagValuesAttribute - The name of the tag attribute to fetch values from, if present - Defaults to 'values'
 * 
 * 
 * @package Ui
 * @TODO: Need to create an option to add sparks with data in the tag
 */
class Js_Ui_JqSpark extends Dom_Renderer
{
    
    private $sparks = array();
    
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
     * @return Js_Ui_JqSpark
     */
    static function create($template)
    {
        $o = new self($template);
        return $o;
    }
    
    /**
     * Add a sparkline to be rendered.
     *
     * @param string $selector - A jQuery selecor value (eg: .class)
     * @param array $values
     * @param array $params
     */
    function addSpark($selector, $values = array(), $params = array())
    {
        $this->sparks[$selector] = array();
        $this->sparks[$selector]['values'] = $values;
        $this->sparks[$selector]['params'] = $params;
    }
    
    /**
     * Show() Render all the sparklines to the template
     *
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $url = Tk_Type_Url::createUrl('/lib/Js/jquery/plugins/jquery.sparkline.min.js');
        $template->appendJsUrl($url);
        
        $js = "$(document).ready(function() {
";
        foreach ($this->sparks as $k => $spark) {
            $values = '';
            if (count($spark['values'])) {
                $values = '[' . implode(',', $spark['values']) . '] ';
            }
            $params = '';
            foreach ($spark['params'] as $key => $v) {
                $v = is_string($v) ? "'$v'" : $v;
                $params .= $key . ': ' . $v . ', ';
            }
            if ($params != null) {
                $params = ', {' . substr($params, 0, -2) . '}';
            }
            $js .= "\n$('$k').sparkline($values $params);";
        }
        
        $js .= "\n});";
        
        $template->appendJs($js);
    }
}
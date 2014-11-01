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
 * @package Com
 * @deprecated Use the Js/Ui/JqSpark.php object in the JdkLib package
 */
class Com_Ui_Sparklines extends Dom_Renderer
{

    private $sparks = array();

    /**
     * __construct
     *
     */
    function __construct()
    {
    }

    /**
     * Add a sparkline to be rendered.
     *
     * @param string $selector - A jQuery selecor value (eg: .class)
     * @param array $values
     * @param array $params
     */
    function addSpark($selector, $values, $params = array())
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

        $jsUrl = new Tk_Type_Url('/lib/Jdk/jquery-min.js');
        $template->appendHeadElement('script', array('type' => 'text/javascript', 'src' => $jsUrl->toString()));

        $jsUrl = new Tk_Type_Url('/lib/Jdk/plugins/jquery.sparkline.min.js');
        $template->appendHeadElement('script', array('type' => 'text/javascript', 'src' => $jsUrl->toString()));

        $js = "$(document).ready(function() {
";

        foreach ($this->sparks as $k => $spark) {
            $values = implode(',', $spark['values']);
            $params = '';
            //$spark['params'];
            foreach ($spark['params'] as $key => $v) {
                $v = is_string($v) ? "'$v'" : $v;
                $params .= $key . ': ' . $v . ', ';
            }
            if ($params != null) {
                $params = ', {' . substr($params, 0, -2) . '}';
            }
            $js .= "\n$('$k').sparkline([$values] $params);";
        }

        $js .= "\n});";

        $template->appendHeadElement('script', array('type' => 'text/javascript'), $js);
    }

}
?>
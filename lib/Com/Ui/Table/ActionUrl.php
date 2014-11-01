<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Render an array of Dk objects to a table
 *
 *
 * @package Com
 */
class Com_Ui_Table_ActionUrl extends Com_Ui_Table_ActionInterface
{
    
    /**
     * @var Tk_Type_Url
     */
    protected $url = null;
    
    /**
     * @var string
     */
    protected $text = '';
    
    /**
     * @var string
     */
    protected $class = 'group';
    
    /**
     * @var string
     */
    protected $confirm = '';
    
    /**
     * Create the object instance
     *
     * @param Tk_Type_Url $url
     * @param string $text
     * @param string $cssClass
     * @param string $confirm
     * @see css files for more class options
     */
    function __construct($url, $text, $cssClass = '', $confirm = '')
    {
        $this->url = $url;
        $this->text = $text;
        $this->class = $cssClass;
        $this->confirm = $confirm;
    }
    
    /**
     * Get the action HTML to insert into the Table
     *
     * @return string
     */
    function getHtml()
    {
        $js = '';
        if ($this->confirm) {
            $js = sprintf("return confirm('%s');", $this->confirm);
        }
        return sprintf('<a class="%s" href="%s" onclick="%s" title="%s">%s</a>', $this->class, htmlentities($this->url->toString()), $js, $this->text, $this->text);
    }

}
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
class Com_Ui_Table_Action extends Com_Ui_Table_ActionInterface
{
    
    /**
     * @var string
     */
    protected $event = '';
    
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
     * @param string $event
     * @param string $text
     * @param string $class
     * @param string $confirm
     * @see css files for more class options
     */
    function __construct($event, $text, $class = 'group', $confirm = '')
    {
        $this->event = $event;
        $this->text = $text;
        $this->class = $class;
        $this->confirm = $confirm;
    }
    
    /**
     * Get the action HTML to insert into the Table
     *
     * @return string
     */
    function getHtml()
    {
        $js = sprintf('submitForm(document.getElementById(\'%s\'), \'%s\');', 'Table_' . $this->getId(), $this->event);
        if ($this->confirm) {
            $js = sprintf("if(confirm('%s')) {%s}", $this->confirm, $js);
        }
        
        return sprintf('<a class="%s" href="javascript:;" onclick="%s" title="%s">%s</a>', $this->class, $js, $this->text, $this->text);
    }

}
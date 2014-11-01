<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This is a base url action object, useful for adding link only actions to a Table.
 *
 *
 * @package Table
 */
class Table_Action_Url extends Table_Action
{
    
    protected $msg = '';

    /**
     * Create a delete action
     *
     * @param string $label
     * @param Tk_Type_Url $url
     * @param string $class
     * @return Table_Action_Delete
     */
    static function create($label, $url, $class = '', $confirmMsg = '')
    {
        $obj = new self('none', $url);
        if ($class) {
            $obj->addClass($class);
        }
        $obj->setLabel($label);
        $obj->msg = $confirmMsg;
        return $obj;
    }
    
    /**
     * (non-PHPdoc)
     * @see Table_Action::execute()
     */
    function execute($list) { }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return Dom_template You can also return HTML string
     */
    function getHtml($list)
    {
        $onclick = '';
        if ($this->msg) {
            $onclick = "onclick=\"return confirm('{$this->msg}');\"";
        }
        
        
        return sprintf('<a class="%s" href="%s" title="%s" %s>%s</a>', $this->getClassString(), htmlentities($this->url->toString()), $this->notes, $onclick, $this->label);
    }
    
}
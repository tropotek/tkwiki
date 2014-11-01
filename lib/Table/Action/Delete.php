<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * For this action to work the object must contain a delete() method
 *
 *
 * @package Table
 */
class Table_Action_Delete extends Table_Action
{

    protected $confirmMsg = 'Are you sure you want to delete the selected records.';

    /**
     * Create a delete action
     *
     * @return Table_Action_Delete
     */
    static function create()
    {
        $obj = new self('delete', Tk_Request::requestUri());
        $obj->addClass('i16-delete');
        $obj->setLabel('Delete Selected');
        return $obj;
    }

    /**
     * setConfirm
     *
     * @param string $str
     * @return Table_Action_Delete
     */
    function setConfirm($str)
    {
        $this->confirmMsg = $str;
        return $this;
    }



    /**
     * (non-PHPdoc)
     * @see Table_Action::execute()
     */
    function execute($list)
    {
        $selected = Tk_Request::get($this->getEventKey(Table_Cell_Checkbox::CB_NAME));
        vd($selected);
        if (count($selected)) {
            foreach ($list as $obj) {
                if (!$obj instanceof Tk_Db_Object) {
                    continue;
                }
                if (in_array($obj->getId(), $selected)) {
                    $obj->delete();
                }
            }
        }

        $url = Tk_Request::requestUri();
        $url->redirect();
    }

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
        $js = sprintf('submitForm(document.getElementById(\'%s\'), \'%s\');', $this->getTable()->getForm()->getId(), $this->getEventKey($this->event));
        $js = sprintf("if(confirm('%s')) { %s }else { $(this).unbind('click'); }", $this->confirmMsg, $js);
        //if (confirm(\'Are you sure you want to delete the attached file?\')) { return true; } else {$(this).unbind(\'click\'); return false; };
        return sprintf('<a class="%s" href="javascript:;" onclick="%s" title="%s" onmousedown="$(window).unbind(\'beforeunload\');">%s</a>', $this->getClassString(), $js, $this->notes, $this->label);
    }


}

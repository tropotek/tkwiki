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
class Com_Ui_Table_DeleteAction extends Com_Ui_Table_Action
{
    
    function doProcess()
    {
        if (Tk_Request::getInstance()->exists($this->getEventKey('delete'))) {
            $this->doDelete();
        }
    }
    
    function doDelete()
    {
        $selected = Tk_Request::getInstance()->getParameterValues($this->getEventKey('cb'));
        if (count($selected)) {
            foreach ($this->table->getList() as $obj) {
                if (!$obj instanceof Tk_Db_Object) {
                    continue;
                }
                if (!method_exists($obj, 'getDeletable') || $obj->getDeletable()) {
                    if (in_array($obj->getId(), $selected)) {
                        $obj->delete();
                    }
                }
            }
        }
        
        $url = Tk_Request::getInstance()->getRequestUri();
        $url->delete($this->getEventKey('delete'));
        $url->delete($this->getEventKey('cb'));
        $url->redirect();
    }
}

<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Render an array of Dk objects to a table
 * This cell uses the event keys:
 *  o doOrder   - This is the order direction 'up' or 'down'
 *  o doOrderId - This is the object id to move
 *
 * @package Com
 */
class Com_Ui_Table_OrderByCell extends Com_Ui_Table_Cell
{
    
    protected $imageDir = '/lib/Com/Admin/images/icons/16';
    
    /**
     * Get the object pos in the array by its id
     *
     * @param array $array
     * @param integer $id
     * @return integer
     */
    function getObjectPos($id, $array)
    {
        foreach ($array as $i => $obj) {
            if ($obj->getId() == $id) {
                return $i;
            }
        }
        return -1;
    }
    /**
     * Get the object pos in the array by its id
     *
     * @param integer $id
     * @return integer
     */
    function getObject($id)
    {
        foreach ($this->getTable()->getList() as $obj) {
            if ($obj->getId() == $id) {
                return $obj;
            }
        }
    }
    
    function doProcess()
    {
        
        if (Tk_Request::getInstance()->exists($this->getEventKey('doOrder')) && Tk_Request::getInstance()->exists($this->getEventKey('doOrderId'))) {
            $id = intval(Tk_Request::getInstance()->getParameter($this->getEventKey('doOrderId')));
            $order = Tk_Request::getInstance()->getParameter($this->getEventKey('doOrder'));
            $parentId = Tk_Request::getInstance()->getParameter($this->getEventKey('doOrderParentId'));
            
            $parent = $this->getObject($parentId);
            $list = $this->getTable()->getList();
            if ($parent && $this->getTable() instanceof Com_Ui_Table_Tree) {
                $list = $parent->getChildren($this->getTable()->getList()->getDbTool());
            }
            $pos = $this->getObjectPos($id, $list);
            
            $swapObj = null;
            if ($pos >= 0) {
                $obj = $list[$pos];
                
                if ($order == 'up' && $pos - 1 >= 0) {
                    $swapObj = $list[$pos - 1];
                } else if ($order == 'dn' && $pos + 1 < $list->count()) {
                    $swapObj = $list[$pos + 1];
                }
                if ($obj != null && $swapObj != null) {
                    $mapper = $obj->getDbMapper();
                    $mapper->orderSwap($obj, $swapObj);
                }
            }
            
            $url = Tk_Request::getInstance()->getRequestUri();
            $url->delete($this->getEventKey('doOrder'));
            $url->delete($this->getEventKey('doOrderId'));
            $url->redirect();
        }
    
    }
    
    /**
     * Get the table data from an object if available
     *
     * @param Dk_Db_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        $parentId = 0;
        if ($this->getTable() instanceof Com_Ui_Table_Tree) {
            $parent = $obj->getParent();
            if ($parent) {
                $parentId = $parent->getId();
            }
        }
        
        $urlUp = Tk_Request::getInstance()->getRequestUri();
        $urlUp->set($this->getEventKey('doOrder'), 'up');
        $urlUp->set($this->getEventKey('doOrderId'), $obj->getId());
        
        $urlDn = Tk_Request::getInstance()->getRequestUri();
        $urlDn->set($this->getEventKey('doOrder'), 'dn');
        $urlDn->set($this->getEventKey('doOrderId'), $obj->getId());
        if ($parentId) {
            $urlUp->set($this->getEventKey('doOrderParentId'), $parentId);
            $urlDn->set($this->getEventKey('doOrderParentId'), $parentId);
        }
        
        return sprintf('<a href="%s" title="Move Order Up" rel="nofollow"><img src="%s/order_up.png" /></a> <a href="%s" title="Move Order Down" rel="nofollow"><img src="%s/order_down.png" /></a>', htmlentities($urlUp->toString()), $this->imageDir, htmlentities($urlDn->toString()), $this->imageDir);
    }

}
?>
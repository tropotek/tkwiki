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
class Com_Ui_Table_Tree extends Com_Ui_Table_Base
{
    
    private $idx = 0;
    
    /**
     * Render the table data rows
     *
     * @param Dom_Template $template
     * @param array $list
     */
    function showTd(Dom_Template $template, $list, $nest = 0)
    {
        /* @var $obj Com_Ui_Table_TreeInterface */
        foreach ($list as $obj) {
            $repeatRow = $template->getRepeat('tr');
            
            $rowClassArr = $this->insertRow($obj, $repeatRow, $nest);
            
            $rowClass = $repeatRow->getAttr('tr', 'class');
            if (count($rowClassArr) > 0) {
                $rowClass .= implode(' ',  $rowClassArr);
            }
            if ($nest >= 1) {
                $rowClass .= ' nest';
            }
            
            if (($this->idx) % 2) {
                $repeatRow->setAttr('tr', 'class', 'odd ' . $rowClass);
            } else {
                $repeatRow->setAttr('tr', 'class', 'even ' . $rowClass);
            }
            $this->idx++;
            $repeatRow->appendRepeat();
            
            $children = $obj->getChildren($this->list->getDbTool());
            if ($children) {
                $this->showTd($template, $children, $nest + 1);
            }
        }
    }
    
    /**
     * Insert an object's cells into a row
     *
     */
    protected function insertRow($obj, $template, $nest = 0)
    {
        $rowClassArr = array();
        /* @var $cell Com_Ui_Table_Cell */
        foreach ($this->cells as $cell) {
            $repeat = $template->getRepeat('td');
            
            $class = $repeat->getAttr('td', 'class');
            
            $data = $cell->getTableData($obj);
            if ($data === null) {
                $data = '&nbsp;';
            }
            $repeat->replaceHTML('td', $data);
            
            if ($cell->getProperty()) {
                $class .= ' m' . ucfirst($cell->getProperty());
            }
            if ($cell->isKey()) {
                $class .= ' key';
            }
            $class .= ' ' . $cell->getAlign();
            $repeat->setAttr('td', 'class', $class);
            
            $rowClassArr = array_merge($rowClassArr, $cell->getRowClassList());
            $repeat->appendRepeat();
        }
        return $rowClassArr;
    }

}
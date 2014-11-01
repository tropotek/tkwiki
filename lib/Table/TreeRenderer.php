<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic form renderer
 *
 *
 *
 *
 * @package Table
 */
class Table_TreeRenderer extends Table_Renderer
{
    private $idx = 0;

    
    /**
     * Create a new form with a new form renderer
     *
     * @param Table $table
     * @param ArrayIterator
     * @return Table_Renderer
     */
    static function create($table, $list)
    {
        $obj = new self($table, $list);
        return $obj;
    }
        
    /**
     * Render the table data rows
     *
     * @param array $list
     * @param integer $nest
     */
    function showTd($list, $nest = 0)
    {
        $template = $this->getTemplate();
        
        /* @var $obj Tk_Object */
        foreach ($list as $obj) {
            $repeatRow = $template->getRepeat('tr');
            
            $rowClassArr = $this->insertRow($obj, $repeatRow);
            
            $rowClass = 'r_' . $this->idx . ' ' . $repeatRow->getAttr('tr', 'class') . ' ';
            if (count($rowClassArr) > 0) {
                $rowClass .= implode(' ', $rowClassArr);
            }
            if ($nest >= 1) {
                $rowClass .= ' nest';
            }
            $rowClass = trim($rowClass);
            
            if ($this->idx % 2) {
                $repeatRow->setAttr('tr', 'class', 'odd ' . $rowClass);
            } else {
                $repeatRow->setAttr('tr', 'class', 'even ' . $rowClass);
            }
            $this->idx++;
            $repeatRow->appendRepeat();
            
            $children = $obj->getChildren($this->list->getDbTool());
            if ($children) {
                $this->showTd($children, $nest + 1);
            }
        }
    }
    
    /**
     * Insert an object's cells into a row
     *
     * @param Tk_Object $obj
     * @param Dom_Template $template The row repeat template
     * @return array
     */
    protected function insertRow($obj, $template)
    {
        $rowClassArr = array();
        /* @var $cell Table_Cell */
        foreach ($this->table->getCellList() as $i => $cell) {
            $repeat = $template->getRepeat('td');
            
            $class = '';
            $class .= 'm' . ucfirst($cell->getProperty()) . ' ';
            if (count($cell->getClassList())) {
                $class = implode(' ', $cell->getClassList()) . ' ';
            }
            if ($cell->isKey()) {
                $class .= 'key ';
            }
            //$class .= $cell->getAlign() . ' ';
            $class = trim($repeat->getAttr('td', 'class') . ' ' . $class);
            $repeat->setAttr('td', 'class', $class);
            $repeat->setAttr('td', 'title', $cell->getLabel());
            $rowClassArr = array_merge($rowClassArr, $cell->getRowClassList());
            
            $data = $cell->getTd($obj);
            if ($data === null) {
                $data = '&#160;';
            }
            if ($data instanceof Dom_Template) {
                $repeat->insertTemplate('td', $data);
            } else {
                $repeat->insertHTML('td', $data);
            }
            $repeat->appendRepeat();
        }
        return $rowClassArr;
    }
    
}
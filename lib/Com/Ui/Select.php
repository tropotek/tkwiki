<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Populate a select element with an objects details
 *
 * @package Com
 * @deprecated use Form Object
 */
class Com_Ui_Select extends Com_Web_Renderer
{
    /**
     * @var Dom_FormSelect
     */
    private $select = null;
    
    /**
     * @var mixed
     * One of Tk_Loader_Collection, array() or Iterator interface
     */
    private $list = null;
    
    
    /**
     * __construct
     *
     * @param Dom_FormSelect $select
     * @param mixed $list
     */
    function __construct(Dom_FormSelect $select, $list)
    {
        $this->select = $select;
        $this->list = $list;
        $this->setTemplate($select->getTemplate());
    }
    
    /**
     * Render
     *
     */
    function show()
    {
        foreach ($this->list as $k => $o) {
            if ($o instanceof Com_Ui_SelectInterface) {
                $this->select->appendOption($o->getSelectText(), $o->getSelectValue());
            } else if (is_object($o) && method_exists($o, 'toString')) {
                $this->select->appendOption($o->toString(), $o->getId());
            } else if (is_object($o) && method_exists($o, '__toString')) {
                $this->select->appendOption($o->__toString(), $k);
            } else {
                $this->select->appendOption($o, $k);
            }
        }
    }
}
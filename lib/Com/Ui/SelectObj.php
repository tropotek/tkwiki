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
 * @deprecated use Form
 */
final class Com_Ui_SelectObj extends Com_Web_Renderer
{
    /**
     * @var Dom_FormSelect
     */
    private $select = null;
    
    /**
     * @var Tk_Loader_Collection
     */
    private $list = null;
    
    /**
     * __construct
     *
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
        /* @var $obj Com_Ui_SelectObjInterface */
        foreach ($this->list as $obj) {
            if (!$obj instanceof Com_Ui_SelectObjInterface) {
                continue;
            }
            $this->select->appendOption($obj->getSelectText(), $obj->getSelectValue());
        }
    }
}
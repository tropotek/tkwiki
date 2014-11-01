<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Populate a select element with the values from an array
 *
 * The array: array(1 => 'Item 1', 2 => 'Item 2');<br/>
 * The result: <select><option value="1">Item 1</option><option value="2">Item 2</option></select>
 *
 * @package Com
 * @deprecated use Form
 */
final class Com_Ui_SelectArray extends Com_Web_Renderer
{
    /**
     * @var Dom_FormSelect
     */
    private $select = null;
    
    /**
     * @var array
     */
    private $array = array();
    
    /**
     * __construct
     *
     * @param Dom_FormSelect $select
     * @param array $array
     */
    function __construct(Dom_FormSelect $select, $array)
    {
        $this->select = $select;
        $this->array = $array;
        $this->setTemplate($select->getTemplate());
    }
    
    /**
     * Render
     *
     */
    function show()
    {
        foreach ($this->array as $k => $v) {
            $this->select->appendOption($v, $k);
        }
    }

}
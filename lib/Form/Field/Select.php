<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A form single option select box.
 * Only one option can be selected.
 *
 * The select box is sent an array of options in the following format:
 * <code>
 *   $options = array(
 *     array('name1', 'value 1'),
 *     array('name2', 'value 2'),
 *     ...
 *   );
 * </code>
 *
 * Optionaly an array of the following format can be used:
 * <code>
 *   $options = array('value1' => 'name1', 'value2' => 'name2', ...);
 * </code>
 *
 * @package Form
 */
class Form_Field_Select extends Form_Field
{
    /**
     * @var array
     */
    protected $optgroups = array();
    
    /**
     * @var array
     */
    protected $options = array();
    
    /**
     * @var array
     */
    protected $prependOptions = array();
    

    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param array $options
     * @param Form_Type $type
     * @return Form_Field_Select
     */
    static function create($name, $options = null, $type = null)
    {
        $obj = new self($name, $type);
        $obj->setOptions($options);
        $obj->subFieldValues[$obj->name] = array();
        return $obj;
    }
    

    /**
     * Append an option to the slect element
     *
     * @param string $name
     * @param string $value
     */
    function prependOption($name, $value)
    {
        $this->prependOptions[] = array($name, $value);
        return $this;
    }
    /**
     * Append an option to the slect element
     *
     * @param string $name
     * @param string $value
     */
    function appendOption($name, $value, $optgroup = '')
    {
        if ($optgroup) {
            if (!isset($this->optgroups[$optgroup])) {
                $this->optgroups[$optgroup] = array();
            }
            $this->optgroups[$optgroup][] = array($name, $value);
        } else {
            $this->options[] = array($name, $value);
        }
        return $this;
    }
    
    /**
     * Set the options array
     * The option array is in the format of array(array('name' => 'value'), array('name', 'value'),  etc...);
     * this format allows for duplicate name and values
     *
     * @param array $list
     */
    function setOptions($options, $optgroup = '')
    {
        if ($options instanceof Tk_Loader_Collection || $options instanceof Tk_Db_Array) {
            foreach ($options as $o) {
                if ($o->getSelectValue() instanceof Tk_Loader_Collection || $o->getSelectValue() instanceof Tk_Db_Array) {
                    $this->setOptions($o->getSelectValue(), $o->getSelectText());
                } else {
                    $this->appendOption($o->getSelectText(), $o->getSelectValue(), $optgroup);
                }
            }
        } else if (is_array($options)) {
            if (is_array(current($options)) && !$optgroup) { // array(array('name', 'val'), array('name2' => 'val2'), .....)
                $this->options = $options;
            } else {  // array(1 => 'name', 'val' => 'name2', ...)
                foreach ($options as $k => $v) {
                    if (is_array($v)) {
                        $this->setOptions($v, $k);
                    } else {
                        $this->appendOption($v, $k, $optgroup);
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * Clear the options array
     *
     */
    function clearOptions()
    {
        $this->options = array();
        return $this;
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        $this->showDefault($t);
        $this->showElement($t);
        
        foreach ($this->prependOptions as $arr) {
            if (!is_array($arr)) {
                continue;
            }
            $row = $t->getRepeat('option');
            $row->setAttr('option', 'value', $arr[1]);
            $row->insertText('option', trim($arr[0]));
            if ($this->subFieldValues[$this->name] == $arr[1]) {
                $row->setAttr('option', 'selected', 'selected');
            }
            $row->appendRepeat();
        }
        
        foreach ($this->options as $arr) {
            if (!is_array($arr)) {
                continue;
            }
            $row = $t->getRepeat('option');
            $row->setAttr('option', 'value', $arr[1]);
            $row->insertText('option', trim($arr[0]));
            if ($this->subFieldValues[$this->name] == $arr[1]) {
                $row->setAttr('option', 'selected', 'selected');
            }
            $row->appendRepeat();
        }
        
        foreach ($this->optgroups as $groupName => $arr) {
            if (!is_array($arr)) {
                continue;
            }
            $group = $t->getRepeat('optgroup');
            $group->setAttr('optgroup', 'label', $groupName);
            foreach ($arr as $arr2) {
                if (!is_array($arr2)) {
                    continue;
                }
                $row2 = $group->getRepeat('option');
                $row2->setAttr('option', 'value', $arr2[1]);
                $row2->insertText('option', '  ' . trim($arr2[0]));
                if ($this->subFieldValues[$this->name] == $arr2[1]) {
                    $row2->setAttr('option', 'selected', 'selected');
                }
                $row2->appendRepeat();
            }
            $group->appendRepeat();
        }
    }
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<div class="field" var="block">
  <p class="error" var="error" choice="error"></p>
  <label for="fid-code" var="label"></label>
  <select name="" id="" var="element">
    <option value="" repeat="option" var="option"></option>
    <optgroup label="" repeat="optgroup" var="optgroup"><option value="" repeat="option" var="option"></option></optgroup>
  </select>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
}
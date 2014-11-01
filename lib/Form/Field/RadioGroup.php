<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A form radio group field.
 * The radio group is sent an array of options in the following format:
 * <code>
 *   $options = array(
 *     array('name1', 'value 1'),
 *     array('name2', 'value 2')
 *   );
 * </code>
 *
 *
 * @package Form
 */
class Form_Field_RadioGroup extends Form_Field_Select
{

    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param array $options
     * @param Form_Type $type
     * @return Form_Field_RadioGroup
     */
    static function create($name, $options = array(), $type = null)
    {
        $obj = new self($name, $type);
        if (is_array($options)) {
            $obj->options = $options;
        }
        $obj->subFieldValues[$obj->name] = array();
        return $obj;
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        $this->showDefault($t);
        
        foreach ($this->options as $i => $arr) {
            if (!is_array($arr)) {
                continue;
            }
            $row = $t->getRepeat('row');
            $row->addClass('row', $arr[1]);
            $this->showElement($row);
            $row->setAttr('element', 'name', $this->name);
            $row->setAttr('element', 'id', $this->getElementId().'_'.$i);
            $row->setAttr('element', 'value', $arr[1]);
            $row->setAttr('label', 'for', $this->getElementId().'_'.$i);
            $row->insertText('label', strip_tags(trim($arr[0])) );
            if ($this->subFieldValues[$this->name] == $arr[1]) {
                $row->setAttr('element', 'checked', 'checked');
                $row->addClass('row', 'checked');
                $t->setAttr('label', 'for', $this->getElementId().'_'.$i);
            }
            $row->appendRepeat();
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
  <ul class="admGroupBox">
    <li repeat="row" var="row"><input type="radio" class="inputRadio" var="element" /> <label for="" var="label"></label></li>
  </ul>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}
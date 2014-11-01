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
 *     array('name2', 'value 2')
 *   );
 * </code>
 *
 *
 * @package Form
 */
class Form_Field_DualSelect extends Form_Field
{
    /**
     * @var array
     */
    protected $options = array();
    
    /**
     * @var array
     */
    protected $selected = array();
    
    /**
     * @var array
     */
    protected $prependOptions = array();
    
    /**
     * @var array
     */
    protected $prependSelected = array();
    

    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param array $options
     * @param Form_Type $type
     * @return Form_Field_Select
     */
    static function create($name, $options = null, $selected = null, $type = null)
    {
        if (!$type) {
            $type = Form_Type_Array::create();
        }
        $obj = new self($name, $type);
        
        if ($options instanceof Tk_Loader_Collection || $options instanceof Tk_Db_Array) {
            foreach ($options as $o) {
                $obj->options[] = array($o->getSelectText(), $o->getSelectValue());
            }
        } else if (is_array($options) && count($options)) {
            $obj->options = $options;
        }
        
        if ($selected instanceof Tk_Loader_Collection || $selected instanceof Tk_Db_Array) {
            foreach ($selected as $o) {
                $obj->selected[] = array($o->getSelectText(), $o->getSelectValue());
            }
        } else if (is_array($selected) && count($selected)) {
            $obj->selected = $selected;
        }
        
        $obj->subFieldValues[$obj->name] = array();
        return $obj;
    }
    
    
    
    /**
     * A test to see if an option exists in the selected list
     *
     * @param array $option
     * @return boolean
     */
    protected function optionSelected($option)
    {
        foreach ($this->selected as $arr) {
            if ($arr[0] == $option[0] && $arr[1] == $option[1]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Render the default attributes of an element
     * @param Dom_Template $t
     */
    function showElement($t)
    {
        if (!$t->keyExists('var', 'element')) {
            return;
        }
        if (!$this->enabled) {
            $t->setAttr('element', 'disabled', 'disabled');
            $t->setAttr('el2', 'disabled', 'disabled');
        }
        if ($this->readonly) {
            $t->setAttr('element', 'readonly', 'readonly');
            $t->setAttr('el2', 'readonly', 'readonly');
        }
        if (!$this->autocomplete) {
            $t->setAttr('element', 'autocomplete', 'off');
            $t->setAttr('el2', 'autocomplete', 'off');
        }
        if ($this->accessKey) {
            $t->setAttr('element', 'accesskey', $this->accessKey);
            $t->setAttr('el2', 'accesskey', $this->accessKey);
        }
        if ($this->tabindex > 0) {
            $t->setAttr('element', 'tabindex', $this->tabindex);
            $t->setAttr('el2', 'tabindex', $this->tabindex);
        }
        if ($this->width > 0 && !isset($this->styleList['width'])) {
            $this->addStyle('width', $this->width . 'px');
        }
        if ($this->height > 0 && !isset($this->styleList['height'])) {
            $this->addStyle('height', $this->height . 'px');
        }
        foreach ($this->attrList as $attr => $js) {
            $t->setAttr('element', $attr, $js);
            $t->setAttr('el2', $attr, $js);
        }
        $styleStr = '';
        foreach ($this->styleList as $style => $val) {
            $styleStr .= $style . ': ' . $val . '; ';
        }
        if ($styleStr) {
            $t->setAttr('element', 'style', $styleStr);
            $t->setAttr('el2', 'style', $styleStr);
        }
        
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        $this->showDefault($t);
    
        foreach ($this->options as $arr) {
            if (!is_array($arr) || $this->optionSelected($arr)) {
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
        
        foreach ($this->selected as $arr) {
            if (!is_array($arr)) {
                continue;
            }
            $row = $t->getRepeat('selected');
            $row->setAttr('selected', 'value', $arr[1]);
            $row->insertText('selected', trim($arr[0]));
            if ($this->subFieldValues[$this->name] == $arr[1]) {
                $row->setAttr('selected', 'selected', 'selected');
            }
            $row->appendRepeat();
        }
        
        $css = <<<CSS
.dualBox {

}
.dualBox .button {
  width: 20px;
  margin: 4px 10px;
  padding: 0;
  border: 1px solid #999;
  background-color: #CCC;
}
.dualBox .button:hover {
  cursor: pointer;
  background-color: #999;
}
.dualBox .listBox {
  vertical-align: top;
  display: inline-block;
}
.dualBox .listBox strong {
  display: inline-block;
  padding: 5px 10px;
}
.dualBox .listBox select {
  height: 150px;
  width: 250px;
}
.dualBox .buttonPanel {
  display: inline-block;
  padding: 30px 0px 0px 0px;
}
CSS;
        $t->appendCss($css);
        
        
        
//        $t->setAttr('label', 'for', $this->getElementId());
//        $t->insertText('label', $this->getLabel() );
        $t->setAttr('element', 'name', $this->name . '[]');
        $t->setAttr('element', 'id', $this->getElementId());
        
        $name = $this->getName();
        $js = <<<JS
$(document).ready(function() {
  
  var form = $('.dualBox.$name .selected select').get(0).form;
  $(form).submit(function (e) {
      $('.dualBox.$name .selected select option').each(function(i, selected) {
          $(selected).attr('selected', 'selected');
      });
  });
  
  $('.dualBox.$name .add').click( function (e) {
      $('.dualBox.$name .options select :selected').each(function(i, selected) {
          $(selected).detach();
          $('.dualBox.$name .selected select').append(selected);
      });
      cleanSelect();
  });
  
  $('.dualBox.$name .addall').click( function (e) {
      $('.dualBox.$name .options select option').each(function(i, selected) {
          $(selected).detach();
          $('.dualBox.$name .selected select').append(selected);
      });
      cleanSelect();
  });
  
  $('.dualBox.$name .removeall').click( function (e) {
      $('.dualBox.$name .selected select option').each(function(i, selected) {
          $(selected).detach();
          $('.dualBox.$name .options select').append(selected);
      });
      cleanSelect();
  });
  
  $('.dualBox.$name .remove').click( function (e) {
      $('.dualBox.$name .selected select :selected').each(function(i, selected) {
          $(selected).detach();
          $('.dualBox.$name .options select').append(selected);
      });
      cleanSelect();
  });
  
  
  /**
   * De-Select all option from the sleect box's
   */
  function cleanSelect()
  {
      $('.dualBox.$name .options select :selected, .dualBox.$name .selected select :selected').each(function(i, selected) {
          $(selected).removeAttr('selected');
      });
  }
  
});
JS;
        $t->appendJs($js);
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
  
  <div class="dualBox %s">
    
    <div class="listBox options">
      <strong>Options</strong><br/>
      <select multiple="multiple" var="el2"><option repeat="option" var="option"></option></select>
    </div>
    
    <div class="buttonPanel">
      <div class="button add ui-state-default ui-corner-all">
       <span class="ui-icon ui-icon-arrowthick-1-e" title="Add Selected"></span>
      </div>
      <div class="button addall ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-arrowthickstop-1-e" title="Add All"></span>
      </div>
      <div class="button removeall ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-arrowthickstop-1-w" title="Remove All"></span>
      </div>
      <div class="button remove ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-arrowthick-1-w" title="Remove Selected"></span>
      </div>
    </div>
    
    <div class="listBox selected">
      <strong>Selected</strong><br/>
      <select multiple="multiple" var="element"><option repeat="selected" var="selected"></option></select>
    </div>
  </div>
  
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>', $this->getName());
        
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
}
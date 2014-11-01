<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic table Cell
 *
 *
 * @package Table
 */
class Table_Cell_Checkbox extends Table_Cell
{
    const CB_NAME = 'cb';
    
    
    /**
     * Create a new cell
     *
     * @return Table_Cell_Checkbox
     */
    static function create()
    {
        $obj = new self(self::CB_NAME, '', '');
        return $obj;
    }
    
    /**
     * Get the table data
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getTd($obj)
    {
        $str = '<input type="checkbox" name="' . $this->getEventKey(self::CB_NAME) . '[]" value="' . $obj->getId() . '" />';
        return $str;
    }
    
    /**
     * Get the table header data
     *
     * @param Tk_Object $obj
     * @return string
     */
    function getTh()
    {
        $str = '<span><input type="checkbox" name="' . $this->getEventKey(self::CB_NAME) . '" id="fid-' . $this->getEventKey(self::CB_NAME) . '" onchange="checkAll(this);"/></span>';
        $template = Dom_Template::load($str);
        $tdclass = 'm' . ucfirst(self::CB_NAME);
        $js = <<<JS
// Enable a tr onclick event to toggle check box
$(document).ready(function() {
  $('.Table td.$tdclass input:checkbox').click(function(e){
	if ($(this).attr('checked')) {
	  $(this).removeAttr('checked');
	} else {
	  $(this).attr('checked', 'checked');
	}
  });
  $('.Table td.$tdclass input:checkbox').parents('tr').click(function (e) {
    if ($(this).find('td.$tdclass input:checkbox').attr('checked')) {
      $(this).find('td.$tdclass input:checkbox').removeAttr('checked');
    } else {
      $(this).find('td.$tdclass input:checkbox').attr('checked', 'checked');
    }
  });
});

// Table_Cell_Checkbox
function checkAll(checkbox) {
	var form = checkbox.form;
	var fieldName = arguments[1] ? arguments[1] : checkbox.name;
	for (i = 0; i < form.elements.length; i++) {
		if ((form.elements[i].type == "checkbox") && (form.elements[i].name.indexOf(fieldName) > -1)) {
			if (!(form.elements[i].value == "DISABLED" || form.elements[i].disabled)) {
				form.elements[i].checked = checkbox.checked;
			}
		}
	}
	return true;
}
JS;
        $template->appendJs($js);
        return $template;
    }
    
}

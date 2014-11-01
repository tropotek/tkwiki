<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Autocomplete Field
 * To Use this field the Com_Web_AjaxController must be installed
 *
 * Once an Ajax object is created pass that to the field on creation:
 *
 * <code>
 *   $form->addField(Form_Field_Autocomplete::create('city', 'Lst_Ajax_CityAutocomplete'));
 * </code>
 *
 * The returned list from the Ajax object is a JSON object in the form of:
 * <code>
 *  var projects = [
 *    {
 *      value: 'jquery',
 *      label: 'jQuery'
 *    },
 *    {
 *      value: 'jquery-ui',
 *      label: 'jQuery UI'
 *    }
 *  ];
 * </code>
 *
 * So you must use somthing like the following in the Ajax object:
 *
 * <code>
 *   echo json_encode($out);
 * </code>
 *
 *
 *
 *
 * @package Form
 */
class Form_Field_Autocombo extends Form_Field
{
    
	protected $ajaxClass = '';
	
	protected $minLength = 1;
	
	
    /**
     * Create an instance of this object
     *
     * @param string $name
     *
     * @return Form_Field_Text
     */
    static function create($name, $ajaxClass)
    {
        $obj = new self($name);
        $obj->ajaxClass = $ajaxClass;
        $obj->setAutocomplete(false);
        return $obj;
    }

    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        parent::show($t);
        
        $t->setAttr('mask', 'name', $this->getName() . '-mask');
        $t->setAttr('mask', 'id', $this->getElementId() . '-mask');
        
        $mask = $this->getElementId() . '-mask';
        $el = $this->getElementId();
        
        $this->showMask($t);
        $this->showJs($t);
        
    }

    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function showJs($t)
    {
        $mask = $this->getElementId() . '-mask';
        $el = $this->getElementId();
        $css = <<<CSS
.ui-button-icon-only .ui-button-text, .ui-button-icons-only .ui-button-text {
  padding: 2px 0px;
}
form div input[type="text"].ui-widget-content {
	padding-top: 3px;
	padding-bottom: 3px;
}
.ui-autocomplete {
  max-height: 300px;
  overflow: auto;
}
CSS;
		$t->appendCss($css);
        
        
        $url = Tk_Type_Url::create('/ajax/'.$this->ajaxClass)->toString();
        $js = <<<JS

(function( $ ) {
	$.widget( "ui.combobox_{$this->getName()}", {
		_create: function() {
			var self = this, input = $(this.element);
			
			input.autocomplete({
					delay: 0,
					minLength: 0,
					source: '{$url}',
					select: function( event, ui ) {
						//console.log(ui.item.value);
				        input.val(ui.item.value);
				        $('#$el').val(ui.item.value);
				        return false;
					},
					change: function( event, ui ) {
						$('#$el').val(input.val());
					}
		    }).addClass( "ui-widget ui-widget-content ui-corner-left" );

			input.data( "autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "</a>" )
					.appendTo( ul );
			};

			this.button = $( "<button type='button'>&nbsp;</button>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Show All Items" )
				.insertAfter( input )
				.button({
					icons: {
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass( "ui-corner-all" )
				.addClass( "ui-corner-right ui-button-icon" )
				.click(function() {
					// close if already visible
					if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
						input.autocomplete( "close" );
						return;
					}

					// work around a bug (likely same cause as #5265)
					$( this ).blur();

					// pass empty string as value to search for, displaying all results
					input.autocomplete("search", "");
					input.focus();
				});
		},

		destroy: function() {
			//this.input.remove();
			this.button.remove();
			//this.element.show();
			$.Widget.prototype.destroy.call( this );
		}
	});
})( jQuery );
JS;
        $t->appendJs($js);


        $js = <<<JS
$(function() {
	$( "#$mask" ).combobox_{$this->getName()}( );
});
JS;
        $t->appendJs($js);
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function showMask($t = null)
    {
        if (!$this->enabled) {
            $t->setAttr('mask', 'disabled', 'disabled');
        }
        if ($this->readonly) {
            $t->setAttr('mask', 'readonly', 'readonly');
        }
        if (!$this->autocomplete) {
            $t->setAttr('mask', 'autocomplete', 'off');
        }
        if ($this->accessKey) {
            $t->setAttr('mask', 'accesskey', $this->accessKey);
        }
        if ($this->tabindex > 0) {
            $t->setAttr('mask', 'tabindex', $this->tabindex);
        }
        if ($this->width > 0 && !isset($this->styleList['width'])) {
            $this->addStyle('width', $this->width . 'px');
        }
        if ($this->height > 0 && !isset($this->styleList['height'])) {
            $this->addStyle('height', $this->height . 'px');
        }
        foreach ($this->attrList as $attr => $js) {
            $t->setAttr('mask', $attr, $js);
        }
        $styleStr = '';
        foreach ($this->styleList as $style => $val) {
            $styleStr .= $style . ': ' . $val . '; ';
        }
        if ($styleStr) {
            $t->setAttr('mask', 'style', $styleStr);
        }
        
        // Mask Element
        $t->setAttr('mask', 'name', $this->name . '-mask');
        $t->setAttr('mask', 'id', $this->getElementId() . '-mask');
        if ($this->maxlength > 0) {
            $t->setAttr('mask', 'maxlength', $this->maxlength);
        }
        if ($this->value !== null) {
            $t->setAttr('mask', 'value', $this->getSubFieldValue($this->name));
        }
        
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
        foreach ($this->attrList as $attr => $js) {
            $t->setAttr('element', $attr, $js);
        }
        $styleStr = '';
        
        $t->setAttr('element', 'name', $this->name);
        $t->setAttr('element', 'id', $this->getElementId());
        $t->setAttr('element', 'value', $this->getSubFieldValue($this->name));
        
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
  <input type="hidden" name="" id="" var="element" />
  <input type="text" name="" id="" class="inputText" var="mask" />
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
}
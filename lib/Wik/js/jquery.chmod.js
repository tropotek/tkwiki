/*
 * Jquery CHMOD calculator
 * 
 * Based on Jeroen's Chmod Calculator: Jeroen Vermeulen of Alphamega Hosting 
 * Visit: http://www.javascriptkit.com for this script and more
 * 
 * Jquery Adaption: Michael Mifsud of Troptoek Development
 * Visit: http://www.phpdruid.com/ 
 * 
 * 
 * TODO: Need a better way to handle the close event,for example when we click on the page outside the box close.
 * 
 * 
 *  $('#textElement').chmod();
 *  
 *  
 */
( function($) {
	var opts = null;
    
    // plugin definition
    $.fn.chmod = function(options) {
        // build main options before element iteration
        opts = $.extend( {}, $.fn.chmod.defaults, options);
        // Create the hidden layer
        $.fn.chmod.createLayer(opts);
        $('#_chmodForm input[type=checkbox]').click(function (e) {
        	update();
        });
        // iterate and reformat each matched element
        return this.each( function() {
            $this = $(this);
            // build element specific options
            var o = $.meta ? $.extend( {}, opts, $this.data()) : opts;
            $this.click(function (e) {
                init(e, this);
            });
        });
        hide();
        return this;
    };
    
    // plugin defaults
    $.fn.chmod.defaults = {
        groups : ['owner', 'group', 'other'],
        access : ['execute', 'write', 'read'],
        onInit : null,
        onClose: null,
        onUpdate : null
    };
    
    
    function init(e, el)
    {
    	$('.chmodBlock').get(0).chmodEl = el; 
        $('.chmodBlock #chmodClose').click(function (e) {
            close();
        });
        octalchange(el);
        $('.chmodBlock').css('top', e.pageY+10).css('left', e.pageX-150).focus().show();
    }
    
    function close()
    {
    	 $('.chmodBlock').hide();
    }
    
    function update()
    {
    	var el = $('.chmodBlock').get(0).chmodEl;
        calcChmod(el);
    }
    
    
    
    function calcChmod(el)
    {
		var form = document.getElementById('_chmodForm');
		var groups = ['owner', 'group', 'other'];
		var access = opts.access;
		var totals = new Array("","","");
		var syms = new Array("","","");
        
		for (var i=0; i<groups.length; i++)
		{
		    var group = groups[i];
			var field4 = group + "4";
			var field2 = group + "2";
			var field1 = group + "1";
			//var total = "t_" + group;
			var symbolic = "sym_" + group;
			var number = 0;
			var sym_string = "";
		
			if (form[field4].checked == true) { number += 4; }
			if (form[field2].checked == true) { number += 2; }
			if (form[field1].checked == true) { number += 1; }
		
			if (form[field4].checked == true) {
				sym_string += access[2][0];
			} else {
				sym_string += "-";
			}
			if (form[field2].checked == true) {
				sym_string += access[1][0];
			} else {
				sym_string += "-";
			}
			if (form[field1].checked == true) {
				sym_string += access[0][0];
			} else {
				sym_string += "-";
			}
			
			totals[i] = totals[i]+number;
			syms[i] =  syms[i]+sym_string;
        };
        
        $('#_chmodForm .chmodStr td').text('-'+syms[0] + syms[1] + syms[2]);
        $(el).val(totals[0] + totals[1] + totals[2]);
    }
    
    function octalchange(el) 
    {
    	var form = document.getElementById('_chmodForm');
    	var val = el.value;
    	var ownerbin = parseInt(val.charAt(0)).toString(2);
    	while (ownerbin.length<3) { ownerbin="0"+ownerbin; };
    	var groupbin = parseInt(val.charAt(1)).toString(2);
    	while (groupbin.length<3) { groupbin="0"+groupbin; };
    	var otherbin = parseInt(val.charAt(2)).toString(2);
    	while (otherbin.length<3) { otherbin="0"+otherbin; };
    	form.owner4.checked = parseInt(ownerbin.charAt(0)); 
    	form.owner2.checked = parseInt(ownerbin.charAt(1));
    	form.owner1.checked = parseInt(ownerbin.charAt(2));
    	form.group4.checked = parseInt(groupbin.charAt(0)); 
    	form.group2.checked = parseInt(groupbin.charAt(1));
    	form.group1.checked = parseInt(groupbin.charAt(2));
    	form.other4.checked = parseInt(otherbin.charAt(0)); 
    	form.other2.checked = parseInt(otherbin.charAt(1));
    	form.other1.checked = parseInt(otherbin.charAt(2));
    	calcChmod(el);
    }
    
    
    /**
     * create the chmod layer block at the end of the body block
     */
    $.fn.chmod.createLayer = function(opts) 
    {
 		var groups = opts.groups;
 		var access = opts.access;
        var html = '<div class="chmodBlock clearfix" style="position: absolute;display: none;background: #FFF;border: 1px outset #CCC;padding: 5px;z-index: 999;">' +
'                      <form id="_chmodForm">' +
'                      <table class="datatable" border="0" cellspacing="0" cellpadding="0">' +
'                        <tbody>' +
'                          <tr class="chmodStr">' +
'                            <td colspan="4" align="center" style="font-family: mono;">-r--r--r--</td>' +
'                          </tr>' +
'                          <tr class="top">' +
'                            <th>&#160;</th>' +
'                            <th>'+ groups[0] +'</th>' +
'                            <th>'+ groups[1] +'</th>' +
'                            <th>'+ groups[2] +'</th>' +
'                          </tr>' +
'                          <tr>' +
'                            <th>'+ access[2] +'</th>' +
'                            <td class="col0"><input type="checkbox" name="owner4" value="4" /></td>' +
'                            <td class="col1"><input type="checkbox" name="group4" value="4" /></td>' +
'                            <td class="col0"><input type="checkbox" name="other4" value="4" /></td>' +
'                          </tr>' +
'                          <tr>' +
'                            <th>'+ access[1] +'</th>' +
'                            <td class="col0"><input type="checkbox" name="owner2" value="2" /></td>' +
'                            <td class="col1"><input type="checkbox" name="group2" value="2" /></td>' +
'                            <td class="col0"><input type="checkbox" name="other2" value="2" /></td>' +
'                          </tr>' +
'                          <tr>' +
'                            <th>'+ access[0] +'</th>' +
'                            <td class="col0"><input type="checkbox" name="owner1" value="1" /></td>' +
'                            <td class="col1"><input type="checkbox" name="group1" value="1" /></td>' +
'                            <td class="col0"><input type="checkbox" name="other1" value="1" /></td>' +
'                          </tr>' +
'                        </tbody>' +
'                      </table>' +
'                    </form>' +
'                   <p style="text-align: right;padding: 0; margin: 0;"><a href="javascript:;" id="chmodClose">Close</a></p>' +
'                  </div>';
        
        $('body').append(html);
        
    };
    
})(jQuery);
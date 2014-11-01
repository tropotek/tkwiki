/*
 * Admin Common JS functions.
 * 
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @copyright Tropotek 2008
 * @requires jQuery 1.2+
 */


$(function(){
    $('.f-date').datepicker({ dateFormat: 'dd/mm/yy' }).css('width', '80px');
});

/**
 * This function registers a help message for selected elements
 * 
 * @param String select The element(s) that jQuery should apply this message to.
 * @param String msg
 */
function setStatusText(select, msg) {
	$(select).mouseover( 
        function() {$('#helpStatusBar').html(msg);
    }).mouseout( 
        function() { $('#helpStatusBar').html(''); 
    });
}


/**
 * Reister a box with a cookie so we can save the state
 * 
 * @param string id
 */
function registerBox(id) {

	var trigger = $("#trigger_" + id);
	var box = $("#" + id);

	trigger.click( function() {
		if (box.is(":hidden")) {
			box.slideDown("slow");
			$.cookie('state_' + id, 'expanded');
		} else {
			box.slideUp("slow");
			$.cookie('state_' + id, 'collapsed');
		}
		return false;
	});
	// Set current State
	var state = $.cookie('state_' + id);
	if (state == 'collapsed') {
		box.hide();
	}
}

/**
 * Select all checkbox's with the fieldName as its name.
 * 
 * @param checkbox -
 *            A checkbox form element, the one used to select all checkbox's
 * @param fieldName -
 *            (optional) Default checkbox.name.substr(1)
 */
function selectAllCheckbox(checkbox) {
	var form = checkbox.form;
	var fieldName = arguments[1] ? arguments[1] : checkbox.name;
	for (i = 0; i < form.elements.length; i++) {
		if ((form.elements[i].type == "checkbox")
				&& (form.elements[i].name.indexOf(fieldName) > -1)) {
			if (!(form.elements[i].value == "DISABLED" || form.elements[i].disabled)) {
				form.elements[i].checked = checkbox.checked;
			}
		}
	}
	return true;
}

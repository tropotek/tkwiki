/**
 * Util.js
 * 
 * Copyright: (c) 2008 troptoek.com
 * Author: Michael Mifsud
 */


/**
 * Select all checkboxs with the fieldName as its name.
 * 
 * @param DOMElement checkbox A checkbox form element, the one used to select all checkbox's
 * @param string fieldName (Optional) Default checkbox.name
 * @deprecated New Table objects do not need this, used for Com_Ui_Table_Base only
 */
function selectAllCheckbox(checkbox) 
{
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


/**
 * Trim whitespace from the start and end of a string
 * 
 * @param string str
 * @return string
 */
function trim(str)
{
  if (str.trim) {
    return str.trim();
  }
  return str.replace(/(^\s*)|(\s*$)/g, "");
}


/**
 * This script counts and limits a textarea content.
 * 
 * @param string textid
 * @param integer limit
 * @param string infoDivId
 */
function limitChars(textId, limit, infodivId)
{
    var text = $('#'+textId).val();
    if(text.length > limit) {
        $('#' + infodivId).html('You cannot type more than '+limit+' characters!');
        $('#'+textId).val(text.substr(0,limit));
        return false;
    } else {
        $('#' + infodivId).html('You have '+ (limit - text.length) +' characters left.');
        return true;
    }
}

/**
 * Submit a form with an event attached so php scripts can fire the event.
 * 
 * @param formElement form
 * @param string action
 * @param string value (optional) If not supplied, action is used.
 */
function submitForm(form, action)
{
    var value = arguments[2] ? arguments[2] : action;
    if (!form) {
        return;
    }
    // Add the action event to a hidden field and value
    var node = document.createElement('input');
    node.setAttribute('type', 'hidden');
    node.setAttribute('name', action);
    node.setAttribute('value', value);
    form.appendChild(node);
    form.submit();
}

/**
 * Get the file extension
 * 
 * @return string
 */
function getFileExtension(file)
{
    var pos = file.lastIndexOf('.');
    if (pos > -1) {
        return file.substring(pos+1);
    }
    return '';
}

/**
 * get basename of a path or URL
 * 
 * @return string
 */
function basename(path)
{
    var pos = path.lastIndexOf('/');
    if (pos > -1) {
        return path.substring(pos+1);
    }
    pos = path.lastIndexOf('\\');
    if (pos > -1) {
        return path.substring(pos+1);
    }
    return path;
}

/**
 * get base directory of a path or URL
 * 
 * @return string
 */
function dirname(path)
{
    var pos = path.lastIndexOf('/');
    if (pos > -1) {
        return path.substring(0, pos);
    }
    var pos = path.lastIndexOf('\\');
    if (pos > -1) {
        return path.substring(0, pos);
    }
    return path;
}

/**
 * An in array function to emulate php's in_array function
 * 
 * @param mixed needle
 * @param array haystack
 * @return boolean
 */
function in_array(needle, haystack)
{
    for (var i = 0; i < haystack.length; i++) {
        if (haystack[i] == needle) {
            return true;
        }
    }
    return false;
}

/**
 * Popup window.
 * 
 * @param string url - The url to show in the popup window
 * @param integer width - (optional) The width in pixels
 * @param integer height - (optional) The height in pixels
 * @param string scrollbars - (optional) 'yes'/'no' values
 * @param string targetName - (optional) Changes the target name of the window, use this for sub popups.
 * @param string modal - (optional) 'yes'/'no' values, yes give the popup a modal dialog box effect.
 * @return - The new opend window object
 * @deprecated
 */
var __popupWin = null;
function popup(url)
{
    vd('It\'s 2010 and browsers do not like popups anymore! Consider using a more compatible method.');
    var width = arguments[1] ? arguments[1] : 455;
    var height = arguments[2] ? arguments[2] : 500;
    var scrollbars = arguments[3] ? arguments[3] : 'yes';
    var resizable = arguments[4] ? arguments[4] : 'yes';
    var targetName = arguments[5] ? arguments[5] : 'info_' + Math.floor(Math.random() * 1000);
    var modal = arguments[6] ? arguments[6] : 'no';

    var LeftPosition = 0;
    var TopPosition = 0;

    if (__popupWin != null && !__popupWin.closed) {
        __popupWin.close();
    }
    try {
        if (window.opener) {
            LeftPosition = (window.opener.innerWidth - width) / 2;
            TopPosition = ((window.opener.innerHeight - height) / 2) + 60;
        } else if (window.innerWidth) {
            LeftPosition = (window.innerWidth - width) / 2;
            TopPosition = ((window.innerHeight - height) / 2) + 60;
        } else {
            LeftPosition = (parseInt(window.screen.width) - width) / 2;
            TopPosition = ((parseInt(window.screen.height) - height) / 2) + 60;
        }
    } catch (e) {
    }
    __popupWin = window.open(url, targetName, 'scrollbars=' + scrollbars
            + ',width=' + width + ',height=' + height + ',resizable='
            + resizable + ',left=' + LeftPosition + ',top=' + TopPosition
            + ',modal=' + modal);
    if (window.focus) {
        __popupWin.focus();
    }
    return __popupWin;
}

/**
 * A vd (var_dump) type wrapper for javascript
 * 
 * @param mixed object
 */
function vd(object)
{
    if (window.console && window.console.log)
        window.console.log(object);
    //else
        //alert(object);
}


/**
 * Returns get parameters.
 * If the desired param does not exist, null will be returned
 * 
 * @example value = $.getURLParam('paramName');'
 * @see http://www.mathias-bank.de
 * @deprecated Use the Url.js object
 */ 
jQuery.extend({
getURLParam: function(strParamName){
	  var strReturn = "";
	  var strHref = window.location.href;
	  var bFound=false;
	  
	  var cmpstring = strParamName + "=";
	  var cmplen = cmpstring.length;

	  if ( strHref.indexOf("?") > -1 ){
	    var strQueryString = strHref.substr(strHref.indexOf("?")+1);
	    var aQueryString = strQueryString.split("&");
	    for ( var iParam = 0; iParam < aQueryString.length; iParam++ ){
	      if (aQueryString[iParam].substr(0,cmplen)==cmpstring){
	        var aParam = aQueryString[iParam].split("=");
	        strReturn = aParam[1];
	        bFound=true;
	        break;
	      }
	    }
	  }
	  if (bFound==false) return null;
	  return strReturn;
	}
});

/**
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
jQuery.extend({
cookie: function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options = $.extend({}, options); // clone object since it's unexpected behavior if the expired property were changed
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // NOTE Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
}
});



/**
 * Reister a box with a cookie so we can save the state
 * 
 * @param string id
 * @requires jquery.cookie
 */
function registerSlideBox(id) 
{
	var trigger = $("#trigger_" + id);
	var box = $("#" + id);
	trigger.click( function() {
		if (box.is(":hidden")) {
			box.slideDown("slow");
			trigger.addClass('bHide');
			trigger.removeClass('bShow');
			$.cookie('state_' + id, 'expanded');
		} else {
			box.slideUp("slow");
			trigger.addClass('bShow');
			trigger.removeClass('bHide');
			$.cookie('state_' + id, 'collapsed');
		}
		return false;
	});
	// Set current State
	var state = $.cookie('state_' + id);
	if (state == 'collapsed') {
		box.hide();
		trigger.addClass('bShow');
		trigger.removeClass('bHide');
	}
}
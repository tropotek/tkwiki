
/**
 * Set the waiting Icon
 */
function setWaiting()
{
    $('#content2').html('<img src="img/waiting.gif" style="margin: 100px auto;width: 16px;display: block;"/>');
}
window.setWaiting = setWaiting;
/**
 * @param path The relative path not complete path
 */
function getFileList() 
{
	var path = arguments[0] ? arguments[0] : '';
    // setWaiting();
    $.get('fileList.php', {selectedPath : path}, function(data) {$('#content2').html(data);});
}
window.getFileList = getFileList;

/**
 * @param path The relative path not complete path
 */
function getDeleteFileList(path) 
{
    var str = '';
    var elements = document.forms[0].elements;
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].name.indexOf('fileSelect') > -1 && elements[i].checked)
            str = str + '&fileSelect[]=' + elements[i].value;
    }
    setWaiting();
    var url = 'fileList.php?dl=dl'+str;
    $.get(url, {selectedPath : path}, function(data) {$('#content2').html(data);});
}



function toggle(checkbox) 
{
    clearAll();
    checkbox.checked = true;
    /*
    if (checkbox.checked) {
        checkbox.checked = false;
    } else {
        checkbox.checked = true;
    }
    */
}

function clearAll()
{
    var elements = document.forms[0].elements;
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].name.indexOf('fileSelect') > -1)
            elements[i].checked = false;
    }
}

function selectAll()
{
    var elements = document.forms[0].elements;
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].name.indexOf('fileSelect') > -1)
            elements[i].checked = true;
    }
}

function hasSelection()
{
    var elements = document.forms[0].elements;
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].name.indexOf('fileSelect') > -1 && elements[i].checked) {
            return true;
        }
    }
    return false;
}

/**
 * Submit a form with an event attached so php scripts can fire the event.
 * 
 * @param formElement form
 * @param string action
 * @param string value (optional) If not supplied action is used.
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
function getExt(file)
{
    if (!file) {
        return;
    }
    var pos = file.lastIndexOf('.');
    if (pos > -1) {
        return file.substring(pos+1);
    }
    return '';
}

/**
 * get 
 * 
 * @return string
 */
function basename(path)
{
    var pos = path.lastIndexOf('/');
    if (pos > -1) {
        return path.substring(pos+1);
    }
    return '';
}

function dirname(path)
{
    var pos = path.lastIndexOf('/');
    if (pos > -1) {
        return path.substring(0, pos);
    }
    return '';
}

/**
 * Is the file type viewable in the preview window?
 * 
 */
function isViewable(ext)
{
    switch (ext) {
        // text files
        case 'htm':
        case 'html':
        case 'txt':
        case 'js':
        case 'css':
        // movie files
        case 'swf':
        case 'mov':
        case 'mpg':
        case 'mpeg':
        case 'avi':
        case 'mp2':
        case 'wmv':
        // Audio files
        case 'mp3':
        case 'wav':
        case 'ogg':
        // Image types
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'png':
        case 'ico':
        case 'bmp':
            return true;
    }
    return false;
}

/**
 * Is the file type viewable in the preview window?
 * 
 */
function getIcon(ext)
{
    switch (ext) {
        // text files
        case 'htm':
        case 'html':
            return 'html_lg.png';
        case 'txt':
        case 'js':
        case 'css':
            return 'txt.png';
        // movie files
        case 'swf':
        case 'mov':
        case 'mpg':
        case 'mpeg':
        case 'avi':
        case 'mp2':
        case 'wmv':
        case 'flv':
        case 'mov':
        case 'mp4':
        case 'f4v':
        case '3gp':
        case '3g2':
            return 'video.png';
        // Audio files
        case 'mp3':
        case 'wav':
        case 'ogg':
            return 'sound.png';
        // Image types
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'png':
        case 'ico':
        case 'bmp':
            return 'image.png';
        case 'zip':
        case 'gz':
        case 'rar':
        case 'tar':
        case 'tgz':
            return 'archive.png';
        default: 
            return 'default.png';
    }
}

/**
 * Init the preview window and associated links.
 * 
 */
function initPreview(url)
{
    var ext = getExt(url);
    var filename = basename(url);
    var iframe = document.getElementById('preview');
    
    if (isViewable(ext)) {
        iframe.src = url;
    } else {
        iframe.src = 'img/mime/'+getIcon(ext);
    }
    
    var f = document.forms[0];
    if (getIcon(ext) == 'image.png') {
    	f.createLink.checked = false;
    } else {
    	f.createLink.checked = true;
    }
    
    document.getElementById('insert').onclick = function() { FileManagerDialog.insert(url); };
}


 


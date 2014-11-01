tinyMCEPopup.requireLangPack();

var FileManagerDialog = {
    init : function() {
        // var f = document.forms[0];
        // this.iframeId = tinyMCEPopup.getWindowArg('mce_window_id')+'_ifr';
        // Get the selected contents as text and place it in the input
        // f.someval.value = tinyMCEPopup.editor.selection.getContent({format :
        // 'text'});
        // f.somearg.value = tinyMCEPopup.getWindowArg('some_custom_arg');
    },
    
    insert : function(url) {
        if (!url) {
            alert('Please select a file first.');
            return;
        }
        // Insert the contents from the input into the document
        var f = document.forms[0];
        var html = '';
        var ext = getExt(url).toLowerCase();
        var filename = basename(url);
    
        var align = '';
        if (f.align.value) {
            align = ' align="' + f.align.value + '"';
        }
    
        var link = f.createLink.checked;
    
        if (ext && (ext == 'gif' || ext == 'jpg' || ext == 'jpeg' || ext == 'png' || ext == 'ico' || ext == 'bmp')) {
            if (link) {
                html = '<a href="' + url + '" title="' + filename + '" class="jdkImageUrl"><img src="' + url + '" border="0" alt="' + filename + '"' + align + ' class="jdkImage" /></a>';
            } else {
                html = '<img src="' + url + '" border="0" alt="' + filename + '"' + align + ' class="jdkImage" />';
            }
        } else if (ext && (ext == 'flv' || ext == 'mp4' || ext == 'mov' || ext == 'f4v' || ext == '3gp' || ext == '3g2' || ext == 'mp3' || ext == 'aac')) {
          var player = url.substring(0, url.indexOf('/data/')+6) + 'tinymce/jscripts/tiny_mce/plugins/fileManager/js/jwplayer/player.swf';
          html = '<div class="flvplayer">' +
                   '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="300" height="300" id="jdkplayer" name="jdkplayer">' +
                     '<param name="movie" value="' + player + '">' +
                     '<param name="allowfullscreen" value="true">' +
                     '<param name="allowscriptaccess" value="always">' +
                     '<param name="flashvars" value="file=' + url + '">' +
                     '<embed id="jdkplayer" name="jdkplayer" src="' + player + '" width="300" height="300" allowscriptaccess="always" allowfullscreen="true" flashvars="file=' + url + '" />' +
                   '</object>' +
                 '</div>';
        } else {
            if (link) {
                html = '<a href="' + url + '" title="' + filename + '" class="jdkFileUrl">' + filename + '</a>';
            } else {
                html = '<span class="jdkFile">' + filename + '</span>';
            }
        }
    
        tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
        tinyMCEPopup.close();
    },
    
    reload : function() {
        var win = tinyMCEPopup.getWindowArg('win');
        tinyMCEPopup.close();
        win.setWaiting();
        win.getFileList(win.document.forms[0].selectedPath.value);
    },
    
    mkdir : function(url) {
        url = url + '?selectedPath=' + document.forms[0].selectedPath.value;
        tinyMCE.activeEditor.windowManager.open( {
            url : url,
            width : 320,
            height : 150,
            inline : 1
        }, {
            win : window
        });
    
    },
    
    upload : function(url) {
        url = url + '?selectedPath=' + document.forms[0].selectedPath.value;
        tinyMCE.activeEditor.windowManager.open( {
            url : url,
            width : 320,
            height : 150,
            inline : 1
        }, {
            win : window
        });
    
    }
    
};

tinyMCEPopup.onInit.add(FileManagerDialog.init, FileManagerDialog);

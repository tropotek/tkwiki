tinyMCEPopup.requireLangPack();

var WikiCreatePageDialog = {
	init : function() {
		var f = document.forms[0];
		// Get the selected contents as text and place it in the input
		f.pageName.value = tinyMCEPopup.editor.selection.getContent({format : 'text'}).replace(/[^a-zA-Z0-9_-]/g, '_');
		f.linkText.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
	},
    
	insert : function() {
	    //create a wiki link text
	    
	    var pageName = document.forms[0].pageName.value;
	    var linkText = document.forms[0].linkText.value;
	    
	    var url = 'page://' + pageName;
	    var html = '<a href="' + url + '" class="wikiPage">' + linkText + '</a>';
	    
		  tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
		  tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(WikiCreatePageDialog.init, WikiCreatePageDialog);

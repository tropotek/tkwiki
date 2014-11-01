tinyMCEPopup.requireLangPack();

var WikiCreatePageDialog = {
	init : function() {
		var f = document.forms[0];
		f.pageName.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
	},
    
	insert : function() {
	    
	    var linkText = document.forms[0].pageName.value;
	    var pageName = linkText.replace(/[^a-zA-Z0-9_-]/g, '_');
	    
	    var url = 'page://' + pageName;
	    var html = '<a href="' + url + '" class="dk-newWikiPage" title="Link To Page: '+pageName+'">' + linkText + '</a>';
	    
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(WikiCreatePageDialog.init, WikiCreatePageDialog);
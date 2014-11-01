tinyMCEPopup.requireLangPack();

var WikiFindPageDialog = {
  	init : function() { },
  
  	insert : function(pageName, pageTitle) {
  	  
  		var selText = pageTitle;
  		if (tinyMCEPopup.editor.selection.getContent({format : 'text'})) {
  		    selText = tinyMCEPopup.editor.selection.getContent({format : 'text'});
  		}
  		var html = '<a href="page://' + pageName + '" title="' + pageTitle + '">' + selText  + "</a>";
  		
  		tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
  		tinyMCEPopup.close();
  	}
};

tinyMCEPopup.onInit.add(WikiFindPageDialog.init, WikiFindPageDialog);

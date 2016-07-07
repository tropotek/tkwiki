/**
 * Created by mifsudm on 29/06/16.
 * 
 * 
 * TODO: Still not fiully tested
 * TODO: We will have to check on the pagenator when ready
 * TODO: Get a friggen decent ICON working
 */
tinymce.PluginManager.add('wikilink', function(editor, url) {
  var siteUrl = url.substr(0, url.lastIndexOf('/html'));
  if (config) {
    siteUrl = config.siteUrl;
  }
  //console.log(url);
  //console.log(siteUrl);

  var ajaxUrl = '';
  var win = null;
  var onlyText = false;

      function createNewPageLink()
  {
    if (!$('#wikilink-new').val()) {
      editor.windowManager.alert('Please enter a valid title for the new page');
      return;
    }
    var title = $('#wikilink-new').val();
    
    // insert the new page url at the cursor location or within the selected text
    insert(toUrl(title), title, true);
    
    this.parent().parent().close();
  }
  
  
  function toUrl(title) {
    return title.replace(/[^a-zA-Z0-9_-]/g, '_');
  }

  function isOnlyTextSelected(anchorElm) {
    var html = editor.selection.getContent();

    // Partial html and not a fully selected anchor element
    if (/</.test(html) && (!/^<a [^>]+>[^<]+<\/a>$/.test(html) || html.indexOf('href=') == -1)) {
      return false;
    }

    if (anchorElm) {
      var nodes = anchorElm.childNodes, i;

      if (nodes.length === 0) {
        return false;
      }

      for (i = nodes.length - 1; i >= 0; i--) {
        if (nodes[i].nodeType != 3) {
          return false;
        }
      }
    }

    return true;
  }
  
  function insert(pageUrl) {
    var title = arguments[1] ? arguments[1] : '';
    var isNew = arguments[2] ? arguments[2] : false;
    
    var linkAttrs = {
      href: 'page://' + pageUrl,
      'class': 'wiki-page',
      title: title
    };
    if (isNew) {
      linkAttrs.class = 'wiki-page-new';
      //linkAttrs.href = linkAttrs.href+'?title='+encodeURI(title); 
    }
    if (editor.selection.getContent()) {
      editor.execCommand('mceInsertLink', false, linkAttrs);
    } else {
      editor.insertContent(editor.dom.createHTML('a', linkAttrs, editor.dom.encode(title)));
    }
  }
  
  
  function showPageList(win)
  {
    if (jQuery.fn.jtable) {
      jQuery('.wikilink-pageSelect').jtable({
        properties: ['title', 'modified'],
        dataUrl: ajaxUrl,
        template :
        '<div class="jtable-wrapper"><div class="filter clearfix"><div class="jtable-search">'+
        '<input type="text" class="mce-textbox" placeholder="search" />' +
        '<div class="mce-widget mce-btn mce-primary mce-abs-layout-item mce-btn-has-text"><button class="" type="button">Go!</button></div>' +
        '</div></div><br/>' +
        '<table class="table table-condensed table-hover" style="width: 100%;"></table>'+
        '</div>',
        onSelect: function (object) {
          insert(object.url, object.title);
          win.close();
        }
      });
    }
    
  }
  
  
  function wikilink() {
    onlyText = isOnlyTextSelected();
    if (editor.getParam('wikilink_ajaxUrl')) {
      ajaxUrl = editor.getParam('wikilink_ajaxUrl');
    }
    var title = '';
    if (isOnlyTextSelected()) {
      title = editor.selection.getContent()
    }
    // Open window
    var win = editor.windowManager.open({
      title: 'Add Page Link',
      width: parseInt(editor.getParam("plugin_preview_width", "650"), 10),
      height: parseInt(editor.getParam("plugin_preview_height", "500"), 10),
      html: '<div><style>.key a {color: #369; cursor: pointer;}.mce-primary{min-width: auto;}</style><div class="wikilink-pageSelect" style="padding: 10px;">List Unavailable - install jquery-jtable.js</div></div>',
      buttons: [
        {type: 'textbox', name: 'pagetitle', size: 20, label: 'Page Title', id: 'wikilink-new', placeholder: 'Title', value: title},
        {text: "Create", subtype: 'primary', onclick: createNewPageLink},
        {type: "spacer", flex: 1},
        {text: 'Close', onclick: function() { this.parent().parent().close(); }}
      ],
      onSubmit: function(e) {
        console.log(e.data);
      },
      onPostRender: function () {
        showPageList(this);
      }
    });
    
    if (ajaxUrl == '') {
      editor.windowManager.alert('There is no lookup script for existing pages.');
    }
    
  }
  
  // Add a button that opens a window
  editor.addButton('wikilink', {
    text: '',
    title: 'Add/Create page link',
    icon: 'newdocument',
    //image: siteUrl + '/html/js/tinymce/plugins/wikilink/wikilink.js',
    onclick: wikilink
  });

  // Adds a menu item to the tools menu
  editor.addMenuItem('wikilink', {
    text: 'Add Page Link',
    context: 'tools',
    onclick: wikilink
  });
  
  
});
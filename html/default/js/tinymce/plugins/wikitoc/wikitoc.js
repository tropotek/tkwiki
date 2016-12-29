/**
 * plugin.js
 *
 * Released under LGPL License.
 * Copyright (c) 1999-2015 Ephox Corp. All rights reserved
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/*jshint unused:false */
/*global tinymce:true */

/**
 * wikitoc plugin that adds a toolbar button and menu item.
 */
tinymce.PluginManager.add('wikitoc', function(editor, url) {

    editor.addCommand('wikiInsertToc', wikiTocClick);

    /**
     *
     * @param ed
     */
    function wikiTocClick()
    {
        var dom = editor.dom;
        var body = editor.getBody();
        if ($(body).find('.wiki-toc').length) {
            $(body).find('.wiki-toc').remove();
        } else {
            $(body).prepend('<div class="wiki-toc">&nbsp;</div>');
        }
        editor.nodeChanged();
    }

    // Add a button that opens a window
    editor.addButton('wikitoc', {
        text: '',
        title: 'Table Of Contents',
        icon: 'numlist',
        //image: siteUrl + '/html/js/tinymce/plugins/wikilink/wikilink.js',
        onclick: wikiTocClick,
		stateSelector: '.wiki-toc'
    });

    // Adds a menu item to the tools menu
    editor.addMenuItem('wikitoc', {
        text: 'Contents',
        context: 'tools',
        onclick: wikiTocClick,
		stateSelector: '.wiki-toc'
    });

});
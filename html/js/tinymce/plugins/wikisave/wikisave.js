/**
 * plugin.js
 *
 * Released under LGPL License.
 * Copyright (c) 1999-2015 Ephox Corp. All rights reserved
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/*global tinymce:true */

tinymce.PluginManager.add('wikisave', function(editor) {
  function wikisave() {
    var formObj;

    formObj = tinymce.DOM.getParent(editor.id, 'form');

    if (editor.getParam("wikisave_enablewhendirty", true) && !editor.isDirty()) {
      return;
    }

    tinymce.triggerSave();

    // Use callback instead
    if (editor.getParam("wikisave_onsavecallback")) {
      editor.execCallback('wikisave_onsavecallback', editor);
      editor.nodeChanged();
      return;
    }

    if (formObj) {
      editor.setDirty(false);

      if (!formObj.onsubmit || formObj.onsubmit()) {
        if (typeof formObj.submit == "function") {
          formObj.submit();
        } else {
          displayErrorMessage(editor.translate("Error: Form submit field collision."));
        }
      }

      editor.nodeChanged();
    } else {
      displayErrorMessage(editor.translate("Error: No form element found."));
    }
  }

  function displayErrorMessage(message) {
    editor.notificationManager.open({
      text: message,
      type: 'error'
    });
  }

  function cancel() {
    var h = tinymce.trim(editor.startContent);

    // Use callback instead
    if (editor.getParam("wikisave_oncancelcallback")) {
      editor.execCallback('wikisave_oncancelcallback', editor);
      return;
    }

    editor.setContent(h);
    editor.undoManager.clear();
    editor.nodeChanged();
  }

  function stateToggle() {
    var self = this;

    editor.on('nodeChange dirty', function() {
      self.disabled(editor.getParam("wikisave_enablewhendirty", true) && !editor.isDirty());
    });
  }

  editor.addCommand('mceSave', wikisave);
  editor.addCommand('mceCancel', cancel);

  editor.addButton('wikisave', {
    title: 'Save the page',
    icon: 'save',
    text: '',
    cmd: 'mceSave',
    disabled: true,
    onPostRender: stateToggle
  });

  editor.addButton('cancel', {
    text: 'Cancel',
    icon: false,
    cmd: 'mceCancel',
    disabled: true,
    onPostRender: stateToggle
  });

  editor.addShortcut('Meta+S', '', 'mceSave');
});

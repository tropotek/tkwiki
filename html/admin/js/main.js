/**
 * main.js
 */

jQuery(function ($) {

  project_core.initDualListBox();



  var lockTimeout = 110 * 1000;     // 1 * 1000 = 1 sec
  var url = config.siteUrl + '/ajax/lockPage';
  function saveLock() {
    //$.getJSON(url, {pid: $('#pageEdit #fid_pid').val()}, function(data) {});
    $.getJSON(url, {pid: $('#fid_pid').val()}, function(data) {});
    setTimeout(saveLock, lockTimeout);
  }
  project_core.initTinymce({
    plugins: [
      'wikisave wikilink wikitoc advlist autolink autosave link image lists charmap print preview hr anchor',
      'searchreplace code fullscreen insertdatetime media nonbreaking codesample',
      'table directionality emoticons template textcolor paste textcolor colorpicker textpattern visualchars visualblocks'
    ],
    toolbar1: 'wikisave wikilink wikitoc | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | '+
      'bullist numlist | outdent indent | forecolor backcolor fontselect fontsizeselect',
    valid_elements : "*[*]",
    extended_valid_elements : "*[*]",
    keep_styles: true,
    autosave_interval: '10m',
    wikilink_ajaxUrl : config.siteUrl + '/ajax/getPageList',
    wikisave_enablewhendirty: true,
    content_css: [
      '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
      '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
      config.siteUrl + '/vendor/ttek/tk-base/assets/js/tk-tinymce.css',
      config.siteUrl + '/html/app/css/tinymce.css'
    ],


    wikisave_onsavecallback: function (ed) {
      if (config.pageEdit)
        submitForm($('#'+config.pageEdit.formId).get(0), config.pageEdit.saveEvent);
    },
    body_class: 'mce-content-body wiki-content',
    style_formats_merge: true,
    init_instance_callback : function(editor) { // setup a page lock loop
      setTimeout(saveLock, lockTimeout);

      // FIX empty CDATA issue in javascript
      // jw: this code is heavily borrowed from tinymce.jquery.js:12231 but modified so that it will
      //     just remove the escaping and not add it back.
      editor.serializer.addNodeFilter('script,style', function(nodes, name) {
        var i = nodes.length, node, value, type;

        function trim(value) {
          /*jshint maxlen:255 */
          /*eslint max-len:0 */
          return value.replace(/(<!--\[CDATA\[|\]\]-->)/g, '\n')
            .replace(/^[\r\n]*|[\r\n]*$/g, '')
            .replace(/^\s*((<!--)?(\s*\/\/)?\s*<!\[CDATA\[|(<!--\s*)?\/\*\s*<!\[CDATA\[\s*\*\/|(\/\/)?\s*<!--|\/\*\s*<!--\s*\*\/)\s*[\r\n]*/gi, '')
            .replace(/\s*(\/\*\s*\]\]>\s*\*\/(-->)?|\s*\/\/\s*\]\]>(-->)?|\/\/\s*(-->)?|\]\]>|\/\*\s*-->\s*\*\/|\s*-->\s*)\s*$/g, '');
        }
        while (i--) {
          node = nodes[i];
          value = node.firstChild ? node.firstChild.value : '';
          if (value.length > 0) {
            node.firstChild.value = trim(value);
          }
        }
      });
    },
    setup : function(ed){
      ed.on('NodeChange', function(e){
        // TODO: move this into the HtmlFormatter
        $('script', ed.getDoc()).attr('data-jsl-static', 'data-jsl-static');
      });
    }
  });




  project_core.initCodemirror();
  project_core.initMasqueradeConfirm();
  project_core.initTableDeleteConfirm();
  project_core.initGrowLikeAlerts();


});

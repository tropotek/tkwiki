/**
 * Created by mifsudm on 15/06/16.
 */

/******************************** WIKI Style Script (optional) ********************************/
// NOTE: Edit this as needed for the template
jQuery(function ($) {

  $('[data-toggle="tooltip"]').tooltip();

  $('input[type=file].fileinput').fileinput({dataUrl: config.dataUrl});

  /* -- TOC Menu -- */
  // var menu = $('.wiki-content');
  // if (menu.length && menu.toc) {
  //   menu.toc({scope: '.wiki-content'});
  // }

  var menu = $('.wiki-toc');
  if (menu.length && menu.toc) {
    menu.toc({scope: '.wiki-content'});
    // http://keith-wood.name/sticky.html
    menu.sticky({boundedBy: '.wiki-content'});
  }

  $('#NavSearch').on('submit', function(e) {
    if (!$(this).find('input').val()) {
      $(this).find('input').parent().addClass('has-error').find('button').removeClass('btn-default').addClass('btn-danger');
      $(this).find('input').attr('placeholder', 'Enter some search text.');
      return false;
    }
  });

  /* -- Mega Menu -- */
  var delay = 0;
  $('.dropdown.mega-dropdown').on('click', function(e) {
    if ($(this).hasClass('open')) {
      $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideUp(delay);
      $(this).removeClass('open');
    } else {
      $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideDown(delay);
      $(this).addClass('open');
      return false; // stops link execution.
    }
  }).on('mouseleave', function(e) {
    $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideUp(delay);
    $(this).removeClass('open');
  });

  // elFinder integration docs
  // See: https://github.com/Studio-42/elFinder/wiki/Integration-with-TinyMCE-4.x
  
  if (typeof(tinyMCE) != 'undefined') {
    
    function elFinderPickerCallback (callback, value, meta) {
      tinymce.activeEditor.windowManager.open({
        file: config.templateUrl + '/assets/elfinder/elfinder.html', // use an absolute path!
        title: 'TkWiki File Manager',
        width: 900,
        height: 450,
        resizable: true
      }, {
        oninsert: function (file, elf) {
          var url, reg, info;

          // URL normalization
          url = file.url;
          reg = /\/[^/]+?\/\.\.\//;
          while(url.match(reg)) {
            url = url.replace(reg, '/');
          }
          // Remove double // from path
          url = url.replace('//', '/');

          // Make file info
          info = file.name + ' (' + elf.formatSize(file.size) + ')';

          // Provide file and text for the link dialog
          if (meta.filetype == 'file') {
            callback(url, {text: info, title: info});
          }

          // Provide image and alt text for the image dialog
          if (meta.filetype == 'image') {
            callback(url, {alt: info});
          }

          // Provide alternative source and posted for the media dialog
          if (meta.filetype == 'media') {
            callback(url);
          }

        }
      });
      return false;
    }
    
    var lockTimeout = 110 * 1000;     // 1 * 1000 = 1 sec
    var url = config.siteUrl + '/ajax/lockPage';
    function saveLock() {
      //$.getJSON(url, {pid: $('#pageEdit #fid_pid').val()}, function(data) {});
      $.getJSON(url, {pid: $('#fid_pid').val()}, function(data) {});
      setTimeout(saveLock, lockTimeout);
    }

    var initLarge = {
      selector: '.tinymce',
      init_instance_callback : function(editor) { // setup a page lock loop
        setTimeout(saveLock, lockTimeout);
      },
      setup : function(ed){
        ed.on('NodeChange', function(e){
          // TODO: move this into the HtmlFormatter
          $('script', ed.getDoc()).attr('data-jsl-static', 'data-jsl-static');
        });
      },
      plugins: [
        'wikisave wikilink wikitoc advlist autolink autosave link image lists charmap print preview hr anchor',
        'searchreplace code fullscreen insertdatetime media nonbreaking codesample',
        'table directionality emoticons template textcolor paste textcolor colorpicker textpattern visualchars visualblocks'
      ],

      toolbar1: 'wikisave wikilink wikitoc | undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect | bullist numlist | outdent indent',
      toolbar2: 'cut copy paste searchreplace | link unlink anchor image media | hr subscript superscript | forecolor backcolor blockquote',
      toolbar3: 'table | visualchars visualblocks ltr rtl | nonbreaking insertdatetime | charmap emoticons | print preview | removeformat fullscreen code codesample',

      menubar: false,
      toolbar_items_size: 'small',
      valid_elements : "*[*]",
      extended_valid_elements : "*[*]",
      keep_styles: true,
      convert_urls: false,
      browser_spellcheck: true,
      autosave_interval: '10m',
      wikilink_ajaxUrl : config.siteUrl + '/ajax/getPageList',
      wikisave_enablewhendirty: true,
      wikisave_onsavecallback: function (ed) {
        //console.log(ed.getContent());
        submitForm($('#pageEdit').get(0), 'save');
      },
      file_picker_callback : elFinderPickerCallback,

      content_css: [
        config.templateUrl + '/assets/bootstrap-3.3.6/dist/css/bootstrap.min.css',
        config.templateUrl + '/css/tkwiki.css'
      ],
      body_class: 'mce-content-body wiki-content',
      content_style: 'body {padding: 10px;}',
      style_formats_merge: true
    };


    var initSmall = {
      selector: '.tinymce',
      init_instance_callback : function(editor) {
        setTimeout(saveLock, lockTimeout);
      },
      setup : function(ed){
        ed.on('NodeChange', function(e){
          // TODO: move this into the HtmlFormatter
          $('script', ed.getDoc()).attr('data-jsl-static', 'data-jsl-static');
        });
      },
      plugins: [
        'wikisave wikilink wikitoc advlist autolink autosave link image lists charmap print preview hr anchor',
        'searchreplace visualchars code fullscreen insertdatetime media nonbreaking',
        'table paste visualblocks codesample'
      ],

      toolbar: 'wikilink wikitoc | undo redo | insert | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat fullscreen code',

      menubar: false,
      //toolbar_items_size: 'small',
      valid_elements : "*[*]",
      extended_valid_elements : "*[*]",
      keep_styles: true,
      convert_urls: false,
      browser_spellcheck: true,
      autosave_interval: '10m',
      wikilink_ajaxUrl : config.siteUrl + '/ajax/getPageList',
      wikisave_enablewhendirty: true,
      wikisave_onsavecallback: function (ed) {
        submitForm($('#pageEdit').get(0), 'save');
      },
      file_picker_callback : elFinderPickerCallback,
      content_css: [
        config.templateUrl + '/assets/bootstrap-3.3.6/dist/css/bootstrap.min.css',
        config.templateUrl + '/css/tkwiki.css'
      ],
      body_class: 'mce-content-body wiki-content',
      content_style: 'body {padding: 10px;}',
      style_formats_merge: true
    };


    if (document.documentElement.clientWidth < config.widthBreakpoints[3]) {
      tinymce.init(initSmall);
    } else {
      tinymce.init(initLarge);
    }



    // Prevent Bootstrap dialog from blocking focusing
    $(document).on('focusin', function(e) {
      if ($(e.target).closest('.mce-window').length) {
        e.stopImmediatePropagation();
      }
    });
  }

});






/******************************** WIKI System script (Required) ********************************/
// NOTE: only edit of you know what you are doing 
jQuery(function ($) {
  
  // Save page header trigger
  $('.wiki-save-trigger').on('click', function(e) {
    // TODO: submit the form for the edit page
    submitForm($('#pageEdit').get(0), 'save');
  });

  // Default delete confirmation
  $('.wiki-delete-trigger').on('click', function(e) {
    return confirm('Are you sure you want to Delete this?');
  });

  // Fix disabled menu items
  $('.disabled, .disabled a').on('click', function(e) {
    return false;
  });
  
  $('.wiki-create-url-trigger').on('click', function(e) {
    var title = $('#fid-title').val();
    console.log(title);
    // ajax request a url, checking for duplicates.
    $(this).blur();
    
  });
  
  $('.wiki-revert-trigger').on('click', function(e) {
    return confirm('Are you sure you want to revert to this change?');
  });

  // For static form input-button fields
  $('.input-group .form-control[disabled]').each(function (i, el) {
    $(this).closest('.input-group').find('.input-group-btn a').addClass('disabled');
  });
  
});


/**
 * Submit a form with an event attached so php scripts can fire the event.
 * 
 * @param form
 * @param action
 * @internal value (optional) If not supplied, action is used.
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




/**
 * Created by mifsudm on 15/06/16.
 */

/******************************** WIKI Style Script (optional) ********************************/
// NOTE: Edit this as needed for the template
jQuery(function ($) {
  
  /* -- TOC Menu -- */
  var menu = $('.wiki-content');
  if (menu.length && menu.toc) {
    menu.toc({scope: '.wiki-content'});
  }
  
  /* -- Mega Menu -- */
  $('.dropdown.mega-dropdown').on('click', function(e) {
    if ($(this).hasClass('open')) {
      $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideUp('400');
      $(this).removeClass('open');
    } else {
      $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideDown('400');
      $(this).addClass('open');
    }
    return false;
  });
  
  $('.dropdown.mega-dropdown').on('mouseleave', function(e) {
    $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideUp('400');
    $(this).removeClass('open');
  });

  // elFinder integration docs
  // See: https://github.com/Studio-42/elFinder/wiki/Integration-with-TinyMCE-4.x
  
  if (typeof(tinyMCE) != 'undefined') {
    
    function elFinderBrowser (callback, value, meta) {
      tinymce.activeEditor.windowManager.open({
        file: config.siteUrl + '/html/assets/elfinder/elfinder.html', // use an absolute path!
        title: 'TkWiki File Manager',
        width: 900,
        height: 450,
        resizable: 'yes'
      }, {
        oninsert: function (file, elf) {
          var url, reg, info;

          // URL normalization
          url = file.url;
          reg = /\/[^/]+?\/\.\.\//;
          while(url.match(reg)) {
            url = url.replace(reg, '/');
          }

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
    
    tinymce.init({
      selector: '.tinymce',
      plugins: [
        'wikisave wikilink advlist autolink autosave link image lists charmap print preview hr anchor',
        'searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
        'table contextmenu directionality emoticons template textcolor paste fullpage textcolor colorpicker textpattern visualblocks'
      ],

      toolbar1: 'wikisave wikilink | undo redo | cut copy paste searchreplace | bold italic underline strikethrough | styleselect | bullist numlist | outdent indent blockquote',
      toolbar2: ' link unlink anchor image media | hr subscript superscript | nonbreaking insertdatetime | forecolor backcolor',
      toolbar3: 'table | visualchars visualblocks ltr rtl | charmap emoticons | print preview | removeformat fullscreen code',

      menubar: false,
      toolbar_items_size: 'small',
      browser_spellcheck: true,
      convert_urls: false,
      
      wikilink_ajaxUrl : config.siteUrl + '/ajax/getPageList',
      wikisave_enablewhendirty: true,
      wikisave_onsavecallback: function () { submitForm($('#pageEdit').get(0), 'save'); },
      file_picker_callback : elFinderBrowser,
      
      
      content_css: [
        config.siteUrl + '/html/assets/bootstrap-3.3.6/dist/css/bootstrap.min.css',
        config.siteUrl + '/html/css/tinymce.css'
      ],
      content_style: 'body {padding: 10px;}'
    });

    // Prevent Bootstrap dialog from blocking focusin
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

  // For static form fields
  $('.input-group .form-control[disabled]').each(function (i, el) {
    $(this).closest('.input-group').find('.input-group-btn a').addClass('disabled');
  });
  
});


/**
 * Submit a form with an event attached so php scripts can fire the event.
 * 
 * @param formElement form
 * @param string action
 * @param string value (optional) If not supplied, action is used.
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




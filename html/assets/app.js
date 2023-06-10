/**
 * Init all application specific scripts here
 */

jQuery(function ($) {
  // Init page javascript functions
  tkbase.initSugar();
  tkbase.initDialogConfirm();
  tkbase.initTkInputLock();
  tkbase.initDataToggle();
  tkbase.initPasswordToggle();

  app.initTkFormTabs();
  app.initDatepicker();
  app.initTinymce();
  app.initWikiScripts();
});


let app = function () {
  "use strict";

  /**
   * Init all wiki base level functions
   */
  let initWikiScripts = function () {
    $('a.wk-page-disable').on('click', function(e) {
      e.preventDefault();
      return false;
    });

    // see: https://trvswgnr.github.io/bs5-lightbox/#image-gallery
    $('img.wk-image', '.wk-content').each(function () {
      if ($(this).parents('a').length) return;
      let link = $('<a href="#" data-toggle="lightbox" data-gallery="wk-gallery" data-caption=""></a>');
      link.attr('href', $(this).attr('src'));
      $(this).before(link);
      link.append($(this).detach());
    });
    const options = {
      keyboard: true,
      //size: 'fullscreen',
      size: 'xl',
    };
    document.querySelectorAll('[data-toggle="lightbox"]').forEach((el) => el.addEventListener('click', (e) => {
      e.preventDefault();
      const lightbox = new Lightbox(el, options);
      lightbox.show();
    }));

  };

  /**
   * Creates bootstrap 5 tabs around the \Tk\Form renderer groups (.tk-form-group) output
   */
  let initTkFormTabs = function () {
    if ($.fn.tktabs === undefined) {
      console.warn('jquery.tktabs.js is not installed.');
      return;
    }

    function init() {
      $(this).tktabs({});
    }

    $('form').on(EVENT_INIT, document, init).each(init);
  };

  /**
   * Setup the jquery datepicker UI
   */
  let initDatepicker = function () {
    if ($.fn.datepicker === undefined) {
      console.warn('jquery-ui.js is not installed.');
      return;
    }

    function init() {
      let defaults = { dateFormat: config.dateFormat.jqDatepicker };
      $('input.date').each(function () {
        let settings = $.extend({}, defaults, $(this).data());
        $(this).datepicker(settings);
      });
    }

    $('form').on(EVENT_INIT, document, init).each(init);
  };

  /**
   * Tiny MCE setup
   *   See this article for how to create plugins in custom paths and see if it works
   *   Custom plugins: https://stackoverflow.com/questions/21779730/custom-plugin-in-custom-directory-for-tinymce-jquery-plugin
   */
  let initTinymce = function () {
    if (typeof tinymce === "undefined") {
      console.warn('Plugin not loaded: jquery.tinymce');
      return;
    }

    function getMceElf(data) {
      let path = data.elfinderPath ?? '/media';
      return new tinymceElfinder({
        // connector URL (Use elFinder Demo site's connector for this demo)
        url: config.vendorOrgUrl + '/tk-base/assets/js/elfinder/connector.minimal.php?path='+ path,
        // upload target folder hash for this tinyMCE
        uploadTargetHash: 'l1_lw',
        // elFinder dialog node id
        nodeId: 'elfinder'
      });
    }

    // Default base tinymce options
    let mceDefaults = {
      //entity_encoding : 'raw',
      height: 700,
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'anchor', 'image', 'media', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
      ],
      toolbar1:
        'wikiPage | bold italic strikethrough | blocks | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | codesample link image media | removeformat code fullscreen',
      content_css: [
        '//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        config.baseUrl + '/html/assets/app.css'
      ],
      content_style: 'body {padding: 15px;}',
      urlconverter_callback : function (url, node, on_save) {
        let parts = url.split(config.baseUrl);
        if (parts.length > 1) {
          url = config.baseUrl + parts[1];
        }
        return url;
      },
      //image_prepend_url: config.baseUrl,
      //a11y_advanced_options: true,
      statusbar: false,
      extended_valid_elements: 'i[*],em[*],b[*],a[*]',

      setup: (editor) => {

        // Button to create/insert a page into the wiki
        // See \App\Helper\PageSelect object for more info
        editor.ui.registry.addButton('wikiPage', {
          icon: 'addtag',
          tooltip: 'Add/Insert Wiki Page',
          onAction: function(_) {
            $('#page-select-dialog').modal('show');
          }
        });


      }

    };

    function init () {
      let form = $(this);

      // Tiny MCE with only the default editing no upload
      //   functionality with elfinder
      $('textarea.mce-min', form).tinymce({});

      // Full tinymce with elfinder file manager
      $('textarea.mce', form).each(function () {
        let el = $(this);
        el.tinymce($.extend(mceDefaults, {
          file_picker_callback : getMceElf(el.data()).browser,
        }));
      });
    };

    $('form').on(EVENT_INIT, document, init).each(init);

    // TODO: Tinymce Bug: The page scrolls up/down when the cursur reaches the
    //       bottom of the editor window, we need to find out a way to stop this
    //       can we intercept this event and cancel it?????

    // $(window).off('scroll');
    // $(window).on('scroll', function (e) {
    //   //console.log(arguments);
    //   e.stopPropagation();
    //   return false;
    // });
    // $('body').off('scroll');
    // $('body').on('scroll', function (e) {
    //   //console.log(arguments);
    //   e.stopPropagation();
    //   return false;
    // });

    // mceDefaults = {
    //   plugins: [
    //     'advlist'
    //   ],
    //   toolbar1:
    //     'bold italic strikethrough | blocks | alignleft aligncenter ' ,
    //
    // };
    // $('textarea.mce').tinymce(mceDefaults);
  };  // end initTinymce()


  return {
    initWikiScripts: initWikiScripts,
    initTkFormTabs: initTkFormTabs,
    initDatepicker: initDatepicker,
    initTinymce: initTinymce
  }

}();
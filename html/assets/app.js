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
  tkbase.initDatepicker();
  tkbase.initTkFormTabs();

  app.initWikiScripts();
  app.initWkSecret();
  app.initTinymce();
});

let app = function () {
  "use strict";
  let $ = jQuery;

  /**
   * Init all wiki base level functions
   */
  let initWikiScripts = function () {

    // Disable links
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

    // Scroll-to-top button
    let mybutton = document.getElementById("btn-back-to-top");
    if (mybutton) {
      // When the user scrolls down 20px from the top of the document, show the button
      window.onscroll = function () {
        scrollFunction();
      };

      function scrollFunction() {
        if (
          document.body.scrollTop > 20 ||
          document.documentElement.scrollTop > 20
        ) {
          mybutton.style.display = "block";
        } else {
          mybutton.style.display = "none";
        }
      }

      // When the user clicks on the button, scroll to the top of the document
      mybutton.addEventListener("click", backToTop);

      function backToTop() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
      }
    }

  };

  /**
   * Init all wk-secret module functions
   */
  let initWkSecret = function () {

    function loadPass(el, callback) {
      let id = el.data('id');
      if (id) {
        el.removeAttr('data-id');
        $.get(tkConfig.baseUrl + '/api/secret/pass', {id}, function (data) {
          el.data('text', data.p);
          callback.apply(el, [data]);
        }, 'json');
      }
    }

    $('.cp-usr, .cp-pas', '.wk-secret').on('click', function () {
      let pass = $(this).parent().find($(this).data('target'));
      if (!pass) return;
      let val = pass.data('text');
      copyToClipboard(val);
      if ($(this).is('.cp-pas')) {
        loadPass(pass, function (data) {
          copyToClipboard(pass.data('text'));
        });
      }
    });

    $('.wk-secret .cp-otp').on('click', function (e) {
      let btn = $(this);
      var params = {'o': btn.parent().data('id')};
      $.post(document.location, params, function (data) {
        btn.next().text(data.otp);
        copyToClipboard(data.otp);
      });
      return false;
    });

    // show/hide secret pw field
    // $('.wk-secret .pw-show').on('click', function () {
    //   let ico = $(this);
    //   if (ico.is('.fa-eye')) {
    //     ico.prev().text(ico.prev().data('text'));
    //     loadPass(ico.prev(), function (data) {
    //       ico.removeClass('fa-eye');
    //       ico.addClass('fa-eye-slash')
    //       ico.prev().text(ico.prev().data('text'));
    //     });
    //   } else {
    //     ico.removeClass('fa-eye-slash');
    //     ico.addClass('fa-eye')
    //     ico.prev().text(''.padEnd(ico.prev().data('text').length, '*'));
    //   }
    // });

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
        url: tkConfig.vendorOrgUrl + '/tk-base/assets/js/elfinder/connector.minimal.php?path='+ path,
        // upload target folder hash for this tinyMCE
        uploadTargetHash: 'l1_lw',
        // elFinder dialog node id
        nodeId: 'elfinder'
      });
    }

    // Default base tinymce options
    let mceDefaults = {
      height: 700,
      plugins: [
        'advlist', 'save', 'autolink', 'lists', 'link', 'anchor', 'image', 'media', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
      ],
      toolbar1:
        'save wikiPage wikiSecret | bold italic strikethrough | blocks | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | codesample link image media | removeformat code fullscreen',
      content_css: [
        '//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        tkConfig.baseUrl + '/html/assets/app.css'
      ],
      content_style: 'body {padding: 15px;}',
      contextmenu: 'link image template inserttable | cell row column deletetable',
      image_advtab: true,
      statusbar: false,
      extended_valid_elements: 'span[*],i[*],em[*],b[*],a[*],div[*],img[*],input[*],textarea[*],select[*]',

      save_onsavecallback: () => {
        $('#page-save', tinymce.activeEditor.formElement).trigger('click');
      },
      urlconverter_callback : function (url, node, on_save) {
        let parts = url.split(tkConfig.baseUrl);
        if (tkConfig.baseUrl && parts.length > 1) {
          url = tkConfig.baseUrl + parts[1];
        }
        return url;
      },
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

        // Button to create/insert a secret record
        // See \App\Helper\SecretSelect object for more info
        if (tkConfig.enableSecretMod) {
          editor.ui.registry.addButton('wikiSecret', {
            icon: 'lock',
            tooltip: 'Add/Insert Secret Content',
            onAction: function (_) {
              $('#secret-select-dialog').modal('show');
            }
          });

          // Edit secret on double click, in new page...
          // TODO: We should modify the dialog to handle edit and add secrets
          editor.on('init', function () {
            $(editor.getDoc()).on('dblclick', 'img.wk-secret', function () {
              window.open(tkConfig.baseUrl + '/secretEdit?secretId=' + $(this).attr('wk-secret'), '_blank');
            });
            editor.getBody().setAttribute('spellcheck', true);
          });
        }

      },

    };

    function init () {
      // Tiny MCE with only the default editing no upload
      //   functionality with elfinder
      $('textarea.mce-min', this).tinymce();

      // Full tinymce with elfinder file manager
      $('textarea.mce', this).each(function () {
        let el = $(this);
        el.tinymce($.extend(mceDefaults, {
          file_picker_callback : getMceElf(el.data()).browser,
        }));
      });
    }

    formEvents.push(init);

    // TODO: Tinymce Bug: The page scrolls up/down when the cursor reaches the
    //       bottom of the editor window, we need to find out a way to stop this
    //       can we intercept this event and cancel it?????

  };  // end initTinymce()


  return {
    initWikiScripts: initWikiScripts,
    initWkSecret: initWkSecret,
    initTinymce: initTinymce
  }

}();
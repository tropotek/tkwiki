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

    function loadData(el, params, callback) {
      let hash = el.data('secretHash');
      if (!hash) return;
      $.post(tkConfig.baseUrl + '/api/secret/pass', params, function (data) {
        callback.apply(el, [data]);
      }, 'json').fail(function () {
        console.error(arguments);
      });
    }

    $('.wk-secret .pw-show').on('click', function () {
      let secret = $(this).closest('.wk-secret');
      if (!secret.length) return;
      if (secret.data('pw')) {
        $('.pas', secret).text(secret.data('pw'))
        return;
      }
      loadData(secret, {p: secret.data('secretHash')}, function (data) {
        secret.data('pw', data.pw);
        $('.pas', secret).text(data.pw);
      });
    });

    $('.wk-secret .cp-pas').on('click', function () {
      let secret = $(this).closest('.wk-secret');
      if (!secret.length) return;
      if (secret.data('pw')) {
        copyToClipboard(secret.data('pw'));
        return;
      }
      loadData(secret, {p: secret.data('secretHash')}, function (data) {
        secret.data('pw', data.pw);
        copyToClipboard(data.pw);
      });
    });

    $('.wk-secret .cp-usr').on('click', function () {
      let val = $('.usr', $(this).parent()).text();
      copyToClipboard(val);
    });

    $('.wk-secret .cp-otp').on('click', function (e) {
      let secret = $(this).closest('.wk-secret');
      if (!secret.length) return;
      loadData(secret, {p: secret.data('secretHash')}, function (data) {
        $('.otp-code', secret).text(data.otp);
        copyToClipboard(data.otp);
      });
      return false;
    });

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
      //contextmenu: 'link image template inserttable | cell row column deletetable',
      contextmenu: false,
      image_advtab: true,
      statusbar: false,
      extended_valid_elements: 'span[*],i[*],em[*],b[*],a[*],div[*],img[*],input[*],textarea[*],select[*]',
      //content_security_policy: "default-src 'self'",

      save_onsavecallback: () => {
        $('#page-save', tinymce.activeEditor.formElement).trigger('click');
        $(tinymce.activeEditor.targetElm).trigger('save.mce');
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
          editor.on('init', function () {
            $(editor.getDoc()).on('dblclick', 'img.wk-secret', function () {
              window.open(tkConfig.baseUrl + '/secretEdit?h=' + $(this).data('secretHash'), '_blank');
            });
            editor.getBody().setAttribute('spellcheck', true);
          });
        }

      },

    };

    tkRegisterInit(function () {
      // Tiny MCE with only the default editing no upload
      //   functionality with elfinder
      $('textarea.mce-min', this).tinymce({});

      // Full tinymce with elfinder file manager
      $('textarea.mce', this).each(function () {
        let el = $(this);
        el.tinymce($.extend(mceDefaults, {
          file_picker_callback : getMceElf(el.data()).browser,
        }));
      });
    });

  };  // end initTinymce()


  return {
    initWikiScripts: initWikiScripts,
    initWkSecret: initWkSecret,
    initTinymce: initTinymce
  }

}();
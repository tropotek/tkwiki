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

  app.initWikiScripts();
  app.initWkSecret();
  app.initTkFormTabs();
  app.initCodemirror();
  app.initTinymce();
});

let app = function () {
  "use strict";

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

  };

  /**
   * Init all wk-secret module functions
   */
  let initWkSecret = function () {

    $('.cp-usr, .cp-pas', '.wk-secret').on('click', function () {
      let val = $(this).parent().find($(this).data('target')).data('text');
      copyToClipboard(val);
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
    $('.wk-secret .pw-show').on('click', function () {
      let ico = $(this);
      if (ico.is('.fa-eye')) {
        ico.prev().text(ico.prev().data('text'));
        if (ico.prev().data('id')) {
          let id = ico.prev().data('id');
          ico.prev().removeAttr('data-id');
          $.get(config.baseUrl + '/api/secret/pass', {id}, function (data) {
            ico.removeClass('fa-eye');
            ico.addClass('fa-eye-slash')
            ico.prev().data('text', data.p);
            ico.prev().text(ico.prev().data('text'));
          }, 'json');
        }
      } else {
        ico.removeClass('fa-eye-slash');
        ico.addClass('fa-eye')
        ico.prev().text(''.padEnd(ico.prev().data('text').length, '*'));
      }
    });

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
      $('form.tk-form').tktabs({});
    }
    init();
    $('body').on(EVENT_INIT_FORM, init);
  };


  /**
   * Code Mirror setup
   * @todo Implement this into our javascript and css textarea editors
   */
  let initCodemirror = function () {
    if (typeof CodeMirror === 'undefined') {
      console.warn('Plugin not loaded: CodeMirror');
      return;
    }

    function init() {
      let el = this;
      this.cm = CodeMirror.fromTextArea(this, $.extend({}, {
        lineNumbers: true,
        mode: 'javascript',
        smartIndent: true,
        indentUnit: 2,
        tabSize: 2,
        autoRefresh: true,
        indentWithTabs: false,
        dragDrop: false
      }, $(this).data()));

      $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
        this.refresh();
      }.bind(el.cm));
    };

    init();
    $('body').on(EVENT_INIT_FORM, init);
  };  // end initCodemirror()


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
        'advlist', 'save', 'autolink', 'lists', 'link', 'anchor', 'image', 'media', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample', 'template'
      ],
      toolbar1:
        'save wikiPage wikiSecret | bold italic strikethrough | blocks | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | codesample link image media | removeformat code fullscreen',
      content_css: [
        '//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        config.baseUrl + '/html/assets/app.css'
      ],
      content_style: 'body {padding: 15px;}',
      //image_prepend_url: config.baseUrl,
      //a11y_advanced_options: true,
      image_advtab: true,
      statusbar: false,
      extended_valid_elements: 'span[*],i[*],em[*],b[*],a[*],div[*],img[*]',

      save_onsavecallback: () => {
        $('#page-save', tinymce.activeEditor.formElement).trigger('click');
      },
      urlconverter_callback : function (url, node, on_save) {
        let parts = url.split(config.baseUrl);
        if (config.baseUrl && parts.length > 1) {
          url = config.baseUrl + parts[1];
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
        if (config.enableSecretMod) {
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
              window.open(config.baseUrl + '/secretEdit?id=' + $(this).attr('wk-secret'), '_blank');
            });
          });
        }

      },

      templates : [
        {
          title: 'Card Content',
          description: 'Add an optional header and/or footer within a card.',
          content: `<div class="card">
  <h5 class="card-header">Featured</h5>
  <div class="card-body">
    <h5 class="card-title">Special title treatment</h5>
    <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>`
        },
        {
          title: 'Row/Col x3',
          description: 'Add an optional header and/or footer within a card.',
          content: `<div class="row">
  <div class="col-md-4">&nbsp;</div>
  <div class="col-md-4">&nbsp;</div>
  <div class="col-md-4">&nbsp;</div>
</div>`
        },
        {
          title: 'Row/Col x4',
          description: 'Add an optional header and/or footer within a card.',
          content: `<div class="row">
  <div class="col-md-3">&nbsp;</div>
  <div class="col-md-3">&nbsp;</div>
  <div class="col-md-3">&nbsp;</div>
  <div class="col-md-3">&nbsp;</div>
</div>`
        },
        {
          title: 'Placeholder Content',
          description: 'In the example below, we take a typical card component and recreate it with placeholders applied to create a “loading card”. ',
          content: `<div class="card" aria-hidden="true">
  <div class="card-body">
    <h5 class="card-title placeholder-glow">
      <span class="placeholder col-6">&nbsp;</span>
    </h5>
    <p class="card-text placeholder-glow">
      <span class="placeholder col-7">&nbsp;</span>
      <span class="placeholder col-4">&nbsp;</span>
      <span class="placeholder col-4">&nbsp;</span>
      <span class="placeholder col-6">&nbsp;</span>
      <span class="placeholder col-8">&nbsp;</span>
    </p>
    <a class="btn btn-primary disabled placeholder col-6">&nbsp;</a>
  </div>
</div>
`
        },
        {
          title: 'Accordion',
          description: 'Click the accordions below to expand/collapse the accordion content.',
          content: `<div class="accordion" id="accordionExample">
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        Accordion Item #1
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
        Accordion Item #2
      </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
        Accordion Item #3
      </button>
    </h2>
    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
      </div>
    </div>
  </div>
</div>`
        },
        {
          title: 'Description list',
          description: 'Adds a boostrap description list template.',
          content: `<dl class="row">
    <dt class="col-sm-3">{Label}</dt>
    <dd class="col-sm-9">{Description}</dd>
    <dt class="col-sm-3">{Label}</dt>
    <dd class="col-sm-9">{Description}</dd>
</dl>`
        },
        {
          title: 'Naming a source',
          description: 'When providing attribution',
          content: `<figure>
  <blockquote class="blockquote">
    <p>A well-known quote, contained in a blockquote element.</p>
  </blockquote>
  <figcaption class="blockquote-footer">
    Someone famous in <cite title="Source Title">Source Title</cite>
  </figcaption>
</figure>`
        },
      ]

    };

    function init () {
      let form = 'form.tk-form';

      // Tiny MCE with only the default editing no upload
      //   functionality with elfinder
      $('textarea.mce-min', form).tinymce();

      // Full tinymce with elfinder file manager
      $('textarea.mce', form).each(function () {
        let el = $(this);
        el.tinymce($.extend(mceDefaults, {
          file_picker_callback : getMceElf(el.data()).browser,
        }));
      });
    };

    init();
    $('body').on(EVENT_INIT_FORM, init);

    // TODO: Tinymce Bug: The page scrolls up/down when the cursor reaches the
    //       bottom of the editor window, we need to find out a way to stop this
    //       can we intercept this event and cancel it?????

  };  // end initTinymce()


  return {
    initWikiScripts: initWikiScripts,
    initWkSecret: initWkSecret,
    initTkFormTabs: initTkFormTabs,
    initCodemirror: initCodemirror,
    initTinymce: initTinymce
  }

}();
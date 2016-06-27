/**
 * This plugin template is from http://stefangabos.ro/jquery/jquery-plugin-boilerplate-revisited/
 *
 * jQuery Plugin Boilerplate
 * A boilerplate for jumpstarting jQuery plugins development
 * version 1.1, May 14th, 2011
 * by Stefan Gabos
 *
 * <code>
 *   $(document).ready(function() {
 *     // attach the plugin to an element
 *     $('#element').pageList({'foo': 'bar'});
 *
 *     // call a public method
 *     $('#element').data('pageList').foo_public_method();
 *
 *     // get the value of a property
 *     $('#element').data('pageList').settings.foo;
 *   
 *   });
 * </code>
 */

// remember to change every instance of "pageList" to the name of your plugin!
(function($) {

  // here we go!
  var pageList = function(element, options) {

    // plugin's default options
    // this is private property and is  accessible only from inside the plugin
    var defaults = {
      ajaxUrl: '',
      template : 
        '<div class="pageListWrapper"><div class="filter clearfix"><div class="input-group input-group-sm col-md-4 pageList-search pull-right">'+
        '<input type="text" class="form-control" placeholder="search" />' +
        '<span class="input-group-btn">' +
        '<button class="btn btn-default" type="button">Go!</button>' +
        '</span></div></div><br/>' + 
        '<table class="table table-condensed table-hover"><tr><th>Title</th><th>Modified</th></tr></table>'+
        '</div>',
      tool : {orderBy: 'title', offset: 0, limit: 20, total: 0, keywords: ''},
      pageIdx: 0,    // page that is showing now 0 = 1
      onPageSelect : function(page) {}
    };

    // to avoid confusions, use "plugin" to reference the 
    // current instance of the object
    var plugin = this;

    // this will hold the merged default, and user-provided options
    // plugin's properties will be available through this object like:
    // plugin.settings.propertyName from inside the plugin or
    // element.data('pageList').settings.propertyName from outside the plugin, 
    // where "element" is the element the plugin is attached to;
    plugin.settings = {};

    var $element = $(element);  // reference to the jQuery version of DOM element

    // the "constructor" method that gets called when the object is created
    plugin.init = function() {
      // the plugin's final properties are the merged default and 
      // user-provided options (if any)
      plugin.settings = $.extend({}, defaults, options);

      // code goes here
      getPageList();    // Show the table...
      
    };

    // private methods
    // these methods can be called only from inside the plugin like:
    // methodName(arg1, arg2, ... argn)

    // a private method. for demonstration purposes only - remove it!
    var show = function(data) {
      // code goes here
      var table = $(plugin.settings.template);
      $element.empty();
      $element.append(table);
      
      var list = data.list;
      plugin.settings.tool = data.tool;
      for(var i = 0; i < list.length; i++) {
        var page = list[i];
        var item = $('<tr class="pageData"><td><a href="#">'+page.title+'</a></td><td>'+page.modified+'</td></tr>');
        item.data('page', page);
        table.find('table').append(item);
      }
      table.find('tr.pageData td a').on('click', function(e) {
        var page = $(this).parents('tr.pageData').data('page');
        plugin.settings.onPageSelect.apply(this, [page]);
      });
      
      table.find('.pageList-search input').val(plugin.settings.tool.keywords);
      table.find('.pageList-search button').on('click', function(e) {
        var input = table.find('.pageList-search input');
        plugin.settings.tool.offset = 0;
        plugin.settings.tool.keywords = input.val();
        getPageList();
      });
      
      // Pager
      var pager = showPager(plugin.settings.tool);
      if (pager)
        table.append(pager);
      
      
    };
    
    
    var showPager = function(tool) {
      var pager = $('<nav class="text-center"><ul class="pagination pagination-center pagination-sm"></ul></nav>');
      var prev = $('<li class="prev"><a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>');
      var next = $('<li class="next"><a href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>');
      
      var pageTotal = Math.ceil(tool.total/tool.limit);
      if (pageTotal > 20) pageTotal = 20; // limit the max number of pages to show
      
      
      if (pageTotal <= 1) return;
      plugin.settings.pageIdx = 0;
      if (tool.offset > 0)
        plugin.settings.pageIdx = Math.ceil(tool.offset/tool.limit);
      
      for(var i = 0; i < pageTotal; i++) {
        var classStr = '';
        if (i == plugin.settings.pageIdx) classStr = 'active';
        var item = $('<li class="page '+classStr+'"><a href="#" data-offset="'+(i*tool.limit)+'">'+(i+1)+'</a></li>');
        pager.find('ul').append(item);
      }
      
      if (plugin.settings.pageIdx <= 0) {
        prev.addClass('disabled');
      }
      if (plugin.settings.pageIdx >= pageTotal-1 ) {
        next.addClass('disabled');
      }
      
      pager.find('ul').prepend(prev);
      pager.find('ul').append(next);
      
      
      pager.find('.page a').on('click', function(e) {
        plugin.settings.tool.offset = $(this).data('offset');
        getPageList();
      });
      pager.find('.prev a').on('click', function(e) {
        if ($(this).parent().hasClass('disabled')) return false;
        plugin.settings.tool.offset = pager.find('.active').prev().find('a').data('offset');
        getPageList();
      });
      pager.find('.next a').on('click', function(e) {
        if ($(this).parent().hasClass('disabled')) return false;
        plugin.settings.tool.offset = pager.find('.active').next().find('a').data('offset');
        getPageList();
      });
      
      return pager;
    }
    
    
    var getPageList = function() {
      // TODO Cache the response data values to save looking it up each click
      
      processing(true);
      $.getJSON(plugin.settings.ajaxUrl, plugin.settings.tool, function(data) {
        show(data);
      }).always(function (data) {
        processing(false);
      })
    };
    
    var processing = function(show) {
      if (show) {
        // Show processing icon
        console.log('Show Waiting');
      } else {
        // Hide processing icon
        console.log('Hide Waiting');
      }
    };
    

    // public methods
    // these methods can be called like:
    // plugin.methodName(arg1, arg2, ... argn) from inside the plugin or
    // element.data('pageList').publicMethod(arg1, arg2, ... argn) from outside 
    // the plugin, where "element" is the element the plugin is attached to;

    // a public method. for demonstration purposes only - remove it!
    plugin.foo_public_method = function() {
      // code goes here
    };

    // fire up the plugin!
    // call the "constructor" method
    plugin.init();

  };

  // add the plugin to the jQuery.fn object
  $.fn.pageList = function(options) {
    // iterate through the DOM elements we are attaching the plugin to
    return this.each(function() {
      // if plugin has not already been attached to the element
      if (undefined == $(this).data('pageList')) {

        // create a new instance of the plugin
        // pass the DOM element and the user-provided options as arguments
        var plugin = new pageList(this, options);

        // in the jQuery version of the element
        // store a reference to the plugin object
        // you can later access the plugin and its methods and properties like
        // element.data('pageList').publicMethod(arg1, arg2, ... argn) or
        // element.data('pageList').settings.propertyName
        $(this).data('pageList', plugin);
      }
    });

  }

})(jQuery);
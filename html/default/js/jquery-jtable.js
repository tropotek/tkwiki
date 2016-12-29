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
 *     $('#element').jtable({'foo': 'bar'});
 *
 *     // call a public method
 *     $('#element').data('jtable').foo_public_method();
 *
 *     // get the value of a property
 *     $('#element').data('jtable').settings.foo;
 *   
 *   });
 * </code>
 */
// remember to change every instance of "jtable" to the name of your plugin!
(function($) {

  // here we go!
  var jtable = function(element, options) {

    // plugin's default options
    // this is private property and is  accessible only from inside the plugin
    var defaults = {
      properties: null,
      labels: null,
      key: '', 
      dataUrl: '',
      template : 
        '<div class="jtable-wrapper"><div class="filter clearfix"><div class="input-group input-group-sm col-md-4 jtable-search pull-right">'+
        '<input type="text" class="form-control" placeholder="search" />' +
        '<span class="input-group-btn">' +
        '<button class="btn btn-default" type="button">Go!</button>' +
        '</span></div></div><br/>' + 
        '<table class="table table-condensed table-hover"></table>'+
        '</div>',
      tool : {orderBy: 'title', offset: 0, limit: 15, total: 0, keywords: ''},
      pageIdx: 0,    // page that is showing now 0 = 1
      onSelect : function(object) {}
    };

    // to avoid confusions, use "plugin" to reference the 
    // current instance of the object
    var plugin = this;

    // this will hold the merged default, and user-provided options
    // plugin's properties will be available through this object like:
    // plugin.settings.propertyName from inside the plugin or
    // element.data('jtable').settings.propertyName from outside the plugin, 
    // where "element" is the element the plugin is attached to;
    plugin.settings = {};

    var $element = $(element);  // reference to the jQuery version of DOM element

    // the "constructor" method that gets called when the object is created
    plugin.init = function() {
      // the plugin's final properties are the merged default and 
      // user-provided options (if any)
      plugin.settings = $.extend({}, defaults, options);
      
      if (plugin.settings.properties == null || plugin.settings.properties.length == 0) {
        alert('There are no properties available for this table.');
        return;
      }
      if (plugin.settings.labels == null || plugin.settings.labels.length == 0) {
        plugin.settings.labels = [];
        for(var i=0; i < plugin.settings.properties.length; i++) {
          plugin.settings.labels[plugin.settings.labels.length] = plugin.settings.properties[i].jtableLabel();
        }
      }
      if (plugin.settings.key == '') {
        plugin.settings.key = plugin.settings.properties[0];
      }
      
      // code goes here
      getPageList();    // Show the table...
      
    };

    // private methods
    // these methods can be called only from inside the plugin like:
    // methodName(arg1, arg2, ... argn)

    // a private method. for demonstration purposes only - remove it!
    var show = function(data) {
      // code goes here
      var i = 0;
      //var table = $(plugin.settings.template);
      $element.empty();
      $element.append(plugin.settings.template);
      
      // Show headers
      var header = $('<tr></tr>');
      for (i=0; i < plugin.settings.labels.length; i++) {
        header.append('<th>'+plugin.settings.labels[i]+'</th>');
      }
      $element.find('table').append(header);
      
      // show data
      var list = data.list;
      plugin.settings.tool = data.tool;
      for(i = 0; i < list.length; i++) {
        var object = list[i];
        var row = $('<tr></tr>');
        row.data('object', object);
        for (var j=0; j < plugin.settings.labels.length; j++) {
          var css = '';
          var prop = plugin.settings.properties[j];
          var text = object[prop];
          if (plugin.settings.key == prop) {
            css = plugin.settings.properties[j] + ' key';
            text = '<a href="javascript:;">'+text+'</a>';
          }
          row.append('<td class="'+css+'">'+text+'</td>');
        }
        $element.find('table').append(row);
      }
      
      
      // Setup Table Events
      $element.find('td.key a').on('click', function(e) {
        plugin.settings.onSelect.apply(this, [$(this).parents('tr').data('object')]);
      });

      $('.jtable-search input').keypress(function (e) {
        var key = e.which;
        if(key == 13)  { // the enter key code
          $('.jtable-search button').click();
          return false;
        }
      });
      $element.find('.jtable-search button').on('click', function(e) {
        var input = $element.find('.jtable-search input');
        plugin.settings.tool.offset = 0;
        plugin.settings.tool.keywords = input.val();
        getPageList();
      });
      
      // Set the keywords input value if available
      $element.find('.jtable-search input').val(plugin.settings.tool.keywords);
      
      // Pager
      var pager = showPager(plugin.settings.tool);
      if (pager)
        $element.find('table').after(pager);
      
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
    };
    
    
    var getPageList = function() {
      // TODO: Cache the response data values to save looking it up each click
      processing(true);
      $.getJSON(plugin.settings.dataUrl, plugin.settings.tool, function(data) {
        show(data);
      }).always(function (data) {
        processing(false);
      });
    };
    
    var processing = function(show) {
      if (show) {
        $element.addClass('disabled');
        // Show processing icon
        //console.log('Show Waiting');
      } else {
        //$element.removeClass('disabled');
        // Hide processing icon
        //console.log('Hide Waiting');
      }
    };
    

    // public methods
    // these methods can be called like:
    // plugin.methodName(arg1, arg2, ... argn) from inside the plugin or
    // element.data('jtable').publicMethod(arg1, arg2, ... argn) from outside 
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
  $.fn.jtable = function(options) {
    // iterate through the DOM elements we are attaching the plugin to
    return this.each(function() {
      // if plugin has not already been attached to the element
      if (undefined == $(this).data('jtable')) {

        // create a new instance of the plugin
        // pass the DOM element and the user-provided options as arguments
        var plugin = new jtable(this, options);

        // in the jQuery version of the element
        // store a reference to the plugin object
        // you can later access the plugin and its methods and properties like
        // element.data('jtable').publicMethod(arg1, arg2, ... argn) or
        // element.data('jtable').settings.propertyName
        $(this).data('jtable', plugin);
      }
    });

  }

})(jQuery);


// Not sure if we should do this or leave the global namespace and 
// add a method to the plugin instead... see how it goes.
String.prototype.jtableLabel = function(str) {
  return this
      // insert a space before all caps
      .replace(/([A-Z])/g, ' $1')
      // uppercase the first character
      .replace(/^./, function(str){ return str.toUpperCase(); })
};
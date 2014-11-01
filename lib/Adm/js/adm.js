/*
 * Admin Common JS functions.
 * 
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @copyright Tropotek 2008
 * @requires jQuery 1.3+
 */

/* 
 * Add any jquery startup scripts here
 */
$(function()
{
  $('.icon .disable, .i16-icon.disable, .i32-icon.disable').click(function () {return false;}).attr('title', 'DISABLED: ' + $('.icon .disable, .i16-icon.disable, .i32-icon.disable').attr('title'));
  
  registerSlideBox('box1');
  registerSlideBox('box2');
  registerSlideBox('box3');
  registerSlideBox('box4');
  
//  $('.cBox .head').click(function () {
//    $(this).parent().find('.boxContent').toggle();
//    $(this).parent().find('.foot').toggle();
//  });
  
  
  
  
});


/**
 * This function registers a help message for selected elements
 * 
 * @param String select The element(s) that jQuery should apply this message to.
 * @param String msg
 */
function setStatusText(select, msg) 
{
	$(select).mouseover( 
    function() { $('#helpStatusBar').html(msg); }
  ).mouseout( 
    function() { $('#helpStatusBar').html(''); }
  );
}





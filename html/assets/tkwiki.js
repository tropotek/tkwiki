/**
 * Created by mifsudm on 15/06/16.
 */


jQuery(function ($) {
  
  
  /* -- TOC Menu -- */
  var menu = $('.toc-menu');
  if (menu.length && menu.toc) {
    menu.toc({scope: '.wiki-content'});
    $('.toc-close').click(function(e) {
      $(this).closest($(this).data('dismiss')).hide();
    });
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
  
  
  
  
  
  
  
});
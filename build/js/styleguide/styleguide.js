'use strict';

$('.top-bar ul.left .has-dropdown > a').on('click', function(e){
  e.preventDefault();
});

$('.top-bar ul.left :not(.has-dropdown) a').on('click', function(e){

  if ($(this).hasClass('device')) {
    e.preventDefault();
    $('.top-bar ul.left li.icon').removeClass('active');
    $('#sg__container').attr('class', $(this).data('device'));
    $(this).parent().addClass('active');
  } else {
    $('.top-bar ul.left li:not(.icon)').removeClass('active').removeClass('selected');
    $(this).parent().addClass('active').addClass('selected');
    $(this).parents('.has-dropdown').addClass('active');
    window.location.hash = $(this).html();
  }

});

$('#view_outside').on('click', function(e){
  e.preventDefault();
  var url = $('.top-bar li.selected a').attr('href');
  window.open(url, '_blank');
});

if(window.location.hash){
  $('#iframe-styleguide').attr('src', $('a' + window.location.hash).attr('href'));
  $('a' + window.location.hash).parent().addClass('active');
  $('a' + window.location.hash).parents('.has-dropdown').addClass('active');
}

$('#sg_pattern_search').bind('input', function (e) {
  var val = $(this).val();
  var endVal = $('#patterns').find('option[value="' + val + '"]');
  if (endVal.length) {
    $('#iframe-styleguide').attr('src', $(endVal[0]).data('value'));
    window.location.hash = val;
  }
});

$('#sg_search').on('click', function (e) {
  e.preventDefault();
  $('#sg_search_box').toggle();
});

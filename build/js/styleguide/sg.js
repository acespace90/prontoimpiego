'use strict';

$(document).ready(function() {
  $('.sg__block__cta').on('click', function(){
    $(this).parent().next().toggle();
  });
  $('.sg__main__toggle a').on('click', function(e){
    e.preventDefault();
    $(this).children('i').toggleClass('fa-chevron-circle-down').toggleClass('fa-chevron-circle-up');
    $(this).parent().next('.sg__main__block').toggle();
  });
  $('pre code').each(function(i, block) {
    hljs.highlightBlock(block);
  });
});

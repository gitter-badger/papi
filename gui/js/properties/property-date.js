(function ($) {
  
  if ($('input[data-ptb-date]').length) {
    $('input[data-ptb-date]').pikaday({
      format: 'YYYY-MM-DD'
    });
  }
  
}(window.jQuery));
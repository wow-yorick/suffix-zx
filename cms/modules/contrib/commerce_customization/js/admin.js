(function($) {

  'use strict';
  
  Drupal.behaviors.commerceCustomizationAdmin = {
    attach: function(context, settings) {

      // Hide all buttons and trigger them on select change.
      $('[commerce-customization-trigger]', context).each(function() {
        var name = $(this).attr('commerce-customization-trigger');
        $('[name="' + name + '"').hide();
        $(this).change(function() {
          $('[name="' + name + '"').mousedown();
        });
      });
    }
  }

})(jQuery);

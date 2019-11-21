(function ($) {

  'use strict';

  /**
   * Attaches the behavior to zurb carousel view.
   */
  Drupal.behaviors.views_zurb_carousel = {
    attach: function (context, settings) {
      $('.carousel-inner').each(function() {
        if ($(this).children('div').length === 1) {
          $(this).siblings('.carousel-control, .carousel-indicators').hide();
        }
      });
    }
  }



}(jQuery));

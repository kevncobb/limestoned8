(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initColorbox = {
    attach: function (context, settings) {
      if (!$.isFunction($.colorbox) || typeof settings.colorbox === 'undefined') {
        return;
      }

      if (settings.colorbox.mobiledetect && window.matchMedia) {
        // Disable Colorbox for small screens.
        var mq = window.matchMedia('(max-device-width: ' + settings.colorbox.mobiledevicewidth + ')');
        if (mq.matches) {
          return;
        }
      }

      settings.colorbox.rel = function () {
        return $(this).data('colorbox-gallery');
      };

      settings.colorbox.html = function () {
        var $modalContent = $(this).find('> .modal-content');
        return $modalContent.length ? $(this).find('> .modal-content').children().clone() : false;
      };

      $('.colorbox', context)
        .once('init-colorbox')
        .colorbox(settings.colorbox);
    }
  };

})(jQuery, Drupal);

"use strict";

/**
 * @file media_library.form-element.js
 */
(function ($, Drupal) {
  "use strict";
  /**
   * Allow users to edit media library items inside a modal.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behavior to allow editing of a media library item.
   */

  Drupal.behaviors.MediaLibraryFormElementEditItem = {
    attach: function attach(context) {
      $('.media-library-form-element .js-media-library-item a[href]', context).once('media-library-edit').each(function () {
        var elementSettings = {
          progress: {
            type: 'throbber'
          },
          dialogType: 'modal',
          dialog: {
            "width": "80%"
          },
          dialogRenderer: null,
          base: $(this).attr('id'),
          element: this,
          url: $(this).attr('href'),
          event: 'click'
        };
        Drupal.ajax(elementSettings);
      });
    }
  };
  /**
   * Disable the open button when the user is not allowed to add more items.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behavior to disable the media library open button.
   */

  Drupal.behaviors.MediaLibraryFormElementDisableButton = {
    attach: function attach(context) {
      // When the user returns from the modal to the widget, we want to shift
      // the focus back to the open button. If the user is not allowed to add
      // more items, the button needs to be disabled. Since we can't shift the
      // focus to disabled elements, the focus is set back to the open button
      // via JavaScript by adding the 'data-disabled-focus' attribute.
      $('.js-media-library-open-button[data-disabled-focus="true"]', context).once('media-library-disable').each(function () {
        var _this = this;

        $(this).focus(); // There is a small delay between the focus set by the browser and the
        // focus of screen readers. We need to give screen readers time to
        // shift the focus as well before the button is disabled.

        setTimeout(function () {
          $(_this).attr('disabled', 'disabled');
        }, 50);
      });
    }
  };
})(jQuery, Drupal);

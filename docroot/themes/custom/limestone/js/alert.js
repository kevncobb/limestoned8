/**
 * @file
 * Contains javascript to refresh alert div contents.
 */

(function ($, Drupal) {

  Drupal.behaviors.alert = {
    attach: function (context, settings) {
      var alert_section = $(".alert-section");
      var closed_for_the_day = Cookies.get('alert');
      if (closed_for_the_day != null) {
        alert_section.addClass("d-none");
      }

      $(context).find('.alert-section a.close').bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        alert_section.addClass("d-none");
        var inHalfADay = 0.5;
        Cookies.set('alert', 'true', {
          expires: inHalfADay
        });
        return false;
      });
    }
  };

})(jQuery, Drupal, drupalSettings);

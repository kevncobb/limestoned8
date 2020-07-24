/**
 * @file
 * Contains javascript to refresh alert div contents.
 */

(function ($, Drupal) {

  Drupal.behaviors.alert = {
    attach: function (context, settings) {
      var alert_section = $(".alert-section");
      var alert_icon = $(".alert-section .alert--icon > span").html();
      var closed_for_the_day = Cookies.get('alert');
      var top_menu_first = $("#block-topmenu li:first-child");
      var top_menu_alert_icon = $("#block-topmenu li.top-menu-alert-icon a");
      if (closed_for_the_day != null) {
        alert_section.addClass("d-none");
      }

      $(context).find('.alert-section a.close').bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $( "<li class='top-menu-alert-icon'><a href='#'>" + alert_icon + "</a></li>" ).insertBefore( top_menu_first );
        alert_section.addClass("d-none");
        var inHalfADay = 0.5;
        Cookies.set('alert', 'true', {
          expires: inHalfADay
        });
        return false;
      });

      $(context).find(top_menu_alert_icon).bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        alert_section.removeClass("d-none");
        $("#block-topmenu li.top-menu-alert-icon a").remove();
        Cookies.remove('alert');
        return false;
      });
    }
  };

})(jQuery, Drupal, drupalSettings);

/**
 * @file
 * Contains javascript to refresh alert div contents.
 */

(function ($, Drupal) {

  Drupal.behaviors.alert = {
    attach: function (context, settings) {
      var alert_section = $(".alert-section");
      var alert_icon = $(".alert-section #alert--icon").html();
      var closed_for_the_day = Cookies.get('alert');
      var top_menu_first = $("#block-topmenu > ul.menu.dropdown > li:first-child");
      if (closed_for_the_day != null) {
        alert_section.slideUp();
        $( "<li class='top-menu-alert-icon'><a href='#' title='Show Alert'>" + alert_icon + "</a></li>" ).detach().insertBefore( top_menu_first );
      }

      $(context).find('.alert-section a.close').bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $( "<li class='top-menu-alert-icon'><a href='#' title='Show Alert'>" + alert_icon + "</a></li>" ).detach().insertBefore( top_menu_first );
        alert_section.slideUp();
        var inHalfADay = 0.5;
        Cookies.set('alert', 'true', {
          expires: inHalfADay
        });
        $('#block-topmenu ul li.top-menu-alert-icon a').bind('touchstart click', function (event) {
          event.stopPropagation();
          event.preventDefault();
          //console.log('in 2nd alert function');
          alert_section.slideDown();
          $("#block-topmenu ul li.top-menu-alert-icon").remove();
          Cookies.remove('alert');
          return false;
        });
        return false;
      });
      if (closed_for_the_day == 'true') {
        $('#block-topmenu ul li.top-menu-alert-icon a').bind('touchstart click', function (event) {
          event.stopPropagation();
          event.preventDefault();
          //console.log('in 3rd alert function');
          alert_section.slideUp();
          $("#block-topmenu ul li.top-menu-alert-icon a").remove();
          Cookies.remove('alert');
          return false;
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);

(function (Drupal, $, window) {
  Drupal.behaviors.lc_slider = {
    attach: function (context, settings) {
      // Execute code once the DOM is ready. $(document).ready() not required
      // within Drupal.behaviors.

      $(window).on("load resize", function () {
        var ww = window.innerWidth;
        if (ww >= 768) {
          $(".lc-hero-image .desktop-image img").each(function () {
            $(this).parent().parent('div.lc-hero-image').css("background-image", "url(" + $(this).attr('src') + ") ");
          });
        }

        if (ww < 768) {
          $(".lc-hero-image .mobile-image img").each(function () {
            $(this).parent().parent('div.lc-hero-image').css("background-image", "url(" + $(this).attr('src') + ") ");
          });
        }
      });

      $(window).on('resize', function () {

        // Execute code when the window is resized.
        var $containerWidth = $(window).width();
        if ($containerWidth <= 767) {

        }
      });

      // Execute code once the window is fully loaded.
      //$.map( $('.lc-hero-next-slide ul > li'), function (element) { return
      // $(element).text() });

      var titles = $('.lc-hero-next-slide ul > li').map(function(i, el) {
        return $(el).text();
      }).get();

      $( ".slideshow-controls a.next-headline" ).each(function(i) {
        if (titles[i+1] == null) {
          $(this).append(titles[0]);
        } else {
          $(this).append(titles[i+1]);
        }
      });

    }
  }
}(Drupal, jQuery, this));

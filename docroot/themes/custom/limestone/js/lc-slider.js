/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.lc_slider = {
    attach: function (context, settings) {

      $('.lc-slider').once('lc_slider').each(function() {
        var titles = $('.lc-slide-next ul > li').map(function(i, el) {
          return $(el).text();
        }).get();

        $( ".slideshow-controls a.next-headline" ).each(function(i) {
          if (i == (titles.length - 1) ) {
            $(this).append(titles[0]);
          } else {
            $(this).append(titles[i+1]);
          }
        });
      });

      $("ul.tabs li:nth-child(3) a").click();

    }
  };
})(jQuery, Drupal);
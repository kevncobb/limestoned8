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
        var length = titles.length;
        $( ".slideshow-controls a.next-headline" ).each(function(i) {
            $(this).append(titles[i+1]);
            i++;
        });

      });
    }
  };
})(jQuery, Drupal);



//gl - pr
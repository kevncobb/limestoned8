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
        console.log(titles.length);
        $( ".slideshow-controls a.next-headline" ).each(function(i) {
          console.log(titles.length + 'before if statement');
          if (titles[i] == titles.length ) {
            $(this).append(titles[0]);
            console.log(titles.length + 'before if statement');
          } else {
            console.log(titles.length + 'before if statement');
            $(this).append(titles[i+1]);
          }
        });

      });
    }
  };
})(jQuery, Drupal);
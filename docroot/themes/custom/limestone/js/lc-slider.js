/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.lc_slider = {
    attach: function (context, settings) {
      $(window).bind("load", function() {
        var titles = $('.lc-slide-next ul > li').map(function(i, el) {
          return $(el).text();
        }).get();
        console.log(titles);
        var length = titles.length;
        $( ".slideshow-controls a.next-headline" ).each(function(i) {
          if (titles[i+1] == (length + 1) ) {
            $(this).append(titles[0]);
          } else {
            $(this).append(titles[i+1]);
          }
        });
      });
    }
  };
})(jQuery, Drupal);




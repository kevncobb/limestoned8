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

        var a = [];
        for ( var i = 0; i < divs.length; i++ ) {
          a.push( lis[ i ].innerHTML );
        }
        console.log(a);
        $( ".slideshow-controls a.next-headline" ).each(function(i) {
          if (a[i+1] == null) {
            $(this).append(a[0]);
          } else {
            $(this).append(a[i+1]);
          }
        });



      });
    }
  };
})(jQuery, Drupal);




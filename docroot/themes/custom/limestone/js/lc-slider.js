(function (Drupal, $, window) {
  Drupal.behaviors.lc_slider = {
    attach: function (context, settings) {
      // Execute code once the DOM is ready. $(document).ready() not required
      // within Drupal.behaviors.

      $(context).find('div.lc-slider');

      //$.map( $('.lc-hero-next-slide ul > li'), function (element) { return
      // $(element).text() });

      var titles = $('.lc-slide-next ul > li').map(function(i, el) {
        return $(el).text();
      }).get();

      $( ".slideshow-controls a.next-headline" ).each(function(i) {
        if (titles[i+1] == NULL) {
          $(this).append(titles[0]);
        } else {
          $(this).append(titles[i+1]);
        }
      });

    }
  }
}(Drupal, jQuery, this));
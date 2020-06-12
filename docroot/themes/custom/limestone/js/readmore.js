/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  function someElementWatcher(context) {
    // $.once acts like $.each, and loops through the found elements.
    // The code inside $.once() will act on each element in the jQuery object
    // a single time.
    $(context).find(".readmore").once("readmore").each(function () {
      // Configure/customize these variables.
      var showChar = 250;  // How many characters are shown by default
      var ellipsestext = "...";
      var moretext = "Show more >";
      var lesstext = "Show less";


      $('.readmore').one(function() {
        var content = $(this).html();

        if(content.length > showChar) {

          var c = content.substr(0, showChar);
          var h = content.substr(showChar, content.length - showChar);

          var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

          $(this).html(html);
        }

      });

      $(".morelink").click(function(){
        if($(this).hasClass("less")) {
          $(this).removeClass("less");
          $(this).html(moretext);
        } else {
          $(this).addClass("less");
          $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
      });
    });
      // $(this) refers to the current instance of $(".some_element")

  }

  Drupal.behaviors.readmore = {
    attach:function (context) {
      someElementWatcher();
    }
  };
}(jQuery, Drupal));

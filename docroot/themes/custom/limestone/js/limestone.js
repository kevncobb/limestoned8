/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.trunk = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('div.trunk').trunk8({
        fill: '&hellip;',
        lines: 4
      });
    }
  };
  Drupal.behaviors.callout = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('.lc-callout').prepend( "<span class='watermark'>&nbsp;</span>" );
      $(context).find('.lc-callout right').prepend( "<span class='watermark right'>&nbsp;</span>" );
    }
  };
  Drupal.behaviors.trim_next_headline = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('#views_slideshow_cycle_main_hero_slider-block_1 .slideshow-controls .next-headline').prepend( "<span class='watermark'>&nbsp;</span>" );
    }
  };
  Drupal.behaviors.customCKEditorConfig = {
    attach: function (context, settings) {
      if (typeof CKEDITOR !== "undefined") {
        CKEDITOR.dtd.$removeEmpty['i'] = false;
        CKEDITOR.dtd.$removeEmpty['span'] = false;
        //console.log(CKEDITOR.dtd);
      }
    }
  };
  $(window).bind("load resize", function() {
    $('.lc-mobile-menu-toggle').click(function(){
      $(this).toggleClass("expander-hidden");
    });
  });
  Drupal.behaviors.to_top = {
    attach: function (context, settings) {
      // Execute code once the DOM is ready. $(document).ready() not required within Drupal.behaviors.

      (function (jQuery) {
        jQuery.mark = {
          jump: function (options) {
            var defaults = {
              selector: 'a.anchor-link'
            };
            if (typeof options == 'string') {
              defaults.selector = options;
            }

            options = jQuery.extend(defaults, options);
            return jQuery(options.selector).click(function (e) {
              var jumpobj = jQuery(this);
              var target = jumpobj.attr('href');
              var thespeed = 1000;
              var offset = jQuery(target).offset().top;
              jQuery('html,body').animate({
                scrollTop: offset
              }, thespeed, 'swing');
              e.preventDefault();
            });
          }
        };
      })(jQuery);


      // To Top button appear on scroll
      $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
          $('#to-top:hidden').stop(true, true).fadeIn();
        } else {
          $('#to-top').stop(true, true).fadeOut();
        }
        if ($(this).scrollTop() > 400) {
          $('.scroll-fade-1').stop(true, true).css("display", "none");
          $('.scroll-fade-in-1:hidden').stop(true, true).fadeIn();
        } else {
          $('.scroll-fade-1:hidden').stop(true, true).fadeIn();
          $('.scroll-fade-in-1').stop(true, true).css("display", "none");
        }
      });

      jQuery(function(){
        jQuery.mark.jump();
      });


      $(window).load(function () {
        // Execute code once the window is fully loaded.

      });

      $(window).resize(function () {
        // Execute code when the window is resized.
      });

      $(window).scroll(function () {
        // Execute code when the window scrolls.
      });

    }
  };
})(jQuery, Drupal);

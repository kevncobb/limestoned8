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
        lines: 6
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
  Drupal.behaviors.flyout = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('.flyout').click(function() {
        $(this).toggleClass('open');
      });
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
  Drupal.behaviors.lc_menu = {
    attach: function (context, settings) {
      $(context).find('.lc-mobile-menu-toggle').bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).toggleClass("expander-hidden");
        if (!Foundation.MediaQuery.atLeast("medium")){
          // workaround for https://github.com/zurb/foundation-sites/issues/10478
          $(".is-dropdown-submenu-parent").removeClass("is-dropdown-submenu-parent");
        }
      });
    }
  };

  Drupal.behaviors.to_top = {
    attach: function (context, settings) {
      // Execute code once the DOM is ready. $(document).ready() not required within Drupal.behaviors.

      // To Top button appear on scroll
      $(window).bind("scroll", function() {
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
    }
  };
  Drupal.behaviors.open_gallery = {
    attach: function (context, settings) {
      $(context).find('a.open-side-column-gallery').bind('touchstart click', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var holdingCell = $(this).parents('.cell');
        var galleryElement = $(holdingCell).siblings('.cell').find('.cover-image > .field-items > .field-item:first-child a.colorbox');
        var galleryLink = $(galleryElement).colorbox();

        galleryLink.eq(0).click();
        return false;
      });
    }
  };

  Drupal.behaviors.masonry_grid = {
    attach: function (context, settings) {
      if ($(".masonry-grid").length > 0 ) {
        $(context).find('.masonry-grid').masonry({
          // set grid-itemSelector so .grid-sizer is not used in layout
          itemSelector: '.grid-item',
          // use element for option
          columnWidth: '.grid-sizer',
          percentPosition: true
        });
      }
    }
  };
  Drupal.behaviors.accordion_focus_tab = {
    attach: function (context, settings) {
      $(context).find('ul.accordion li.accordion-item a').bind('touchstart click', function (event) {
        $(this).next('div.accordion-content').focus;
      });
    }
  };
})(jQuery, Drupal);



(function ($, Drupal) {

  Drupal.behaviors.faqs = {
    attach: function (context, settings) {
      console.log('faq.js fired');
      var FAQfeature = {
        currentColor: "",
        currentID: false,
        visible: false,
        currentNode: null,

        init: function() {
          $(".faqs .show_form").click(this.toggleForm);
          $("#more-photos").bind("click", FAQfeature.advance);
          $("menu.feature-more").on('keyup', function(event){
            event.stopPropagation();
            event.preventDefault();
            if ( event.keyCode == 13 ) {
              var slider = $(".faqs .view-content");
              var lastColumn = slider.find(".faq-column:last");
              FAQfeature.featureWidth = lastColumn.position().left + lastColumn.width();
              var new_left = slider.position().left;
              new_left -= 385;
              if (new_left + FAQfeature.featureWidth < $(document).width()) {
                new_left = $(document).width() - FAQfeature.featureWidth;
              }
              slider.stop().animate({
                left: new_left + "px"
              }, 800, "easeOutQuad");
            }
          });
          $("#less-photos").bind("click", this.reverse);
          $("menu.feature-previous").on('keyup', function(event){
            event.stopPropagation();
            event.preventDefault();
            if ( event.keyCode == 13 ) {
              var slider = $(".faqs .view-content");
              var lastColumn = slider.find(".faq-column:last");
              FAQfeature.featureWidth = lastColumn.position().left + lastColumn.width();
              var new_left = slider.position().left;
              new_left = 0;
              if (new_left + FAQfeature.featureWidth < $(document).width()) {
                new_left = $(document).width() - FAQfeature.featureWidth;
              }
              slider.stop().animate({
                left: new_left + "px"
              }, 800, "easeOutQuad");
            }
          });
          $(".faqs .cancel").click(this.hideForm);
          $(".faqs .faq-cell").click(this.showCellDetails);
          $(".faqs .close").click(this.hideCellDetails);
          $(".faqs .faq-details .wrap").on('keyup', function(event){
            event.stopPropagation();
            event.preventDefault();
            if (event.keyCode == 27) {
              $(".faqs .faq-details").fadeOut(300);
              $(FAQfeature.currentNode).focus();
              return false;
            }
          });

          $(".faqs form").submit(this.submitQuestion);
          $(".faqs .send").click(function() {
            $(".faqs form").submit();
            return false;
          });
          $(".faqs .vote").click(this.submitVote);
          $(".feature_label .reset_feature").click(this.reset);

          $(".faqs .faq-cell").on('keyup', function(event){
            event.stopPropagation();
            event.preventDefault();
            var hasFocus = $('.faqs .faq-cell').is(':focus');
            var cell = $('.faqs .faq-cell');
            if ( (hasFocus == true) && (event.keyCode == 13) ) {
              cellClasses = $(this).attr("class");
              color = $.trim(cellClasses).replace("cell-quarter", "").replace("cell-half", "").replace("cell-full", "").replace("cell", "");
              question = $(this).find("blockquote").html();
              answer = $(this).find("div.cell-answer").html();
              FAQfeature.currentNode = $(document.activeElement);
              details = $(this).parents('.faqs').find('div.faq-details');
              var focusedAnswer = $(this).parents(".faqs").find('.faq-details .answer');

              //console.log('showCellDetails fired');
              $(".faqs .faq-details h2").html(question);
              $(".faqs .faq-details .answer").html(answer);
              if (FAQfeature.currentColor) {
                $(".faqs .faq-details").removeClass(FAQfeature.currentColor);
              }
              details.addClass(color).fadeIn(300);
              FAQfeature.currentColor = color;
              $(focusedAnswer).focus();
              return false;

              if (event.handled !== true) {
                return false;
              }
            } // end of if keycode
            hasFocus = false;
          });

        },

        toggleForm: function() {
          if (FAQfeature.visible) {
            FAQfeature.hideForm();
          } else {
            FAQfeature.showForm();
          }
        },
        showForm: function() {
          $(".faqs form").fadeIn(300);
          FAQfeature.visible = true;
          return false;
        },
        hideForm: function() {
          $(".faqs form").fadeOut(300);
          FAQfeature.visible = false;
          return false;
        },
        advance: function() {
          var slider = $(".faqs .view-content");
          var lastColumn = slider.find(".faq-column:last");
          FAQfeature.featureWidth = lastColumn.position().left + lastColumn.width();
          var new_left = slider.position().left;
          new_left -= 385;
          if (new_left + FAQfeature.featureWidth < $(document).width()) {
            new_left = $(document).width() - FAQfeature.featureWidth;
          }
          slider.stop().animate({
            left: new_left + "px"
          }, 800, "easeOutQuad");
        },
        reverse: function() {
          var slider = $(".faqs .view-content");
          var lastColumn = slider.find(".faq-column:last");
          FAQfeature.featureWidth = lastColumn.position().left + lastColumn.width();
          var new_left = slider.position().left;
          new_left = 0;
          if (new_left + FAQfeature.featureWidth < $(document).width()) {
            new_left = $(document).width() - FAQfeature.featureWidth;
          }
          slider.stop().animate({
            left: new_left + "px"
          }, 800, "easeOutQuad");
        },
        reset: function() {
          var slider = $(".faqs .view-content");
          slider.stop().animate({
            left: "0px"
          }, 400, "easeOutQuad");
        },
        showCellDetails: function() {
          cellClasses = $(this).attr("class");
          color = $.trim(cellClasses).replace("cell-quarter", "").replace("cell-half", "").replace("cell-full", "").replace("faq-cell", "");
          question = $(this).find("blockquote.faq-question").html();
          answer = $(this).find("div.cell-answer").html();
          FAQfeature.currentNode = $(document.activeElement);
          details = $(this).parents('.faqs').find('div.faq-details');
          var focusableAnswer = $(this).parents(".faqs").children('.faq-details .answer');
          console.log('showCellDetails fired');
          $(".faqs .faq-details h2").html(question);
          $(".faqs .faq-details .answer").html(answer);
          if (FAQfeature.currentColor) {
            $(".faqs .faq-details").removeClass(FAQfeature.currentColor);
          }
          $(".faqs .thanks").html("").css({
            opacity: 0,
            marginTop: "50px"
          });
          $(".faqs .voting").show();
          details.addClass(color).fadeIn(300);
          FAQfeature.currentColor = color;
          var focusedAnswer = $(this).parents(".faqs").find('.faq-details .answer');
          $(focusedAnswer).focus();
          return false;
        },
        hideCellDetails: function() {
          $(".faqs .faq-details").fadeOut(300);
          $(FAQfeature.currentNode).focus();
          return false;
        }
      };

      /*
      $(document).ready(function($) {
        // WRAP NEEDED FAQ CELLS IN COLUMNS
        var $cell = $(".faqs article.cell-quarter, .faqs article.cell-half"),
          $full = $(".faqs article.cell-full");
        for (var i = 0; i < $cell.length; i += 3) {
          $cell.slice(i, i + 3).wrapAll('<div class="faq-column"></div>');
        }
        for (var i = 0; i < $full.length; i += 1) {
          $full.slice(i, i + 1).wrapAll('<div class="faq-column"></div>');
        }
        var $columns = $(".faq-column");

        if ($columns.parent().is("div")) {
          $columns.unwrap();
        }

        $('.faqs div:not([class])').remove();

        //INIT Functions
        FAQfeature.init();
      });
      */
    }
  };
})(jQuery, Drupal);


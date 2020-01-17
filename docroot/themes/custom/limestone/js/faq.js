(function ($, Drupal) {

  Drupal.behaviors.faqs = {
    attach: function (context, settings) {

      (function($, Drupal, window, document, undefined) {
        var FAQfeature = {
          currentColor: "",
          currentID: false,
          visible: false,
          currentNode: null,

          init: function() {
            $(".view-faq-view .show_form").click(this.toggleForm);
            $("#more-photos").bind("click", FAQfeature.advance);
            $("menu.feature-more").on('keyup', function(event){
              event.stopPropagation();
              event.preventDefault();
              if ( event.keyCode == 13 ) {
                var slider = $(".view-faq-view .view-content");
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
                var slider = $(".view-faq-view .view-content");
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
            $(".view-faq-view .cancel").click(this.hideForm);
            $(".view-faq-view .faq-cell").click(this.showCellDetails);
            $(".view-faq-view .close").click(this.hideCellDetails);
            $(".view-faq-view .details .wrap").on('keyup', function(event){
              event.stopPropagation();
              event.preventDefault();
              if (event.keyCode == 27) {
                $(".view-faq-view .details").fadeOut(300);
                $(FAQfeature.currentNode).focus();
                return false;
              }
            });

            $(".view-faq-view form").submit(this.submitQuestion);
            $(".view-faq-view .send").click(function() {
              $(".view-faq-view form").submit();
              return false;
            });
            $(".view-faq-view .vote").click(this.submitVote);
            $(".feature_label .reset_feature").click(this.reset);

            $(".view-faq-view .cell").on('keyup', function(event){
              event.stopPropagation();
              event.preventDefault();
              var hasFocus = $('.view-faq-view .faq-cell').is(':focus');
              var cell = $('.view-faq-view .cell');
              if ( (hasFocus == true) && (event.keyCode == 13) ) {
                cellClasses = $(this).attr("class");
                color = $.trim(cellClasses).replace("cell-quarter", "").replace("cell-half", "").replace("cell-full", "").replace("cell", "");
                question = $(this).find("blockquote").html();
                answer = $(this).find("div.cell-answer").html();
                FAQfeature.currentNode = $(document.activeElement);
                details = $(this).parents('.view-faq-view').find('div.details');
                var focusedAnswer = $(this).parents(".view-faq-view").find('.details .answer');

                //console.log('showCellDetails fired');
                $(".view-faq-view .details h2").html(question);
                $(".view-faq-view .details .answer").html(answer);
                if (FAQfeature.currentColor) {
                  $(".view-faq-view .details").removeClass(FAQfeature.currentColor);
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
            $(".view-faq-view form").fadeIn(300);
            FAQfeature.visible = true;
            return false;
          },
          hideForm: function() {
            $(".view-faq-view form").fadeOut(300);
            FAQfeature.visible = false;
            return false;
          },
          advance: function() {
            var slider = $(".view-faq-view .view-content");
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
            var slider = $(".view-faq-view .view-content");
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
            var slider = $(".view-faq-view .view-content");
            slider.stop().animate({
              left: "0px"
            }, 400, "easeOutQuad");
          },
          showCellDetails: function() {
            cellClasses = $(this).attr("class");
            color = $.trim(cellClasses).replace("cell-quarter", "").replace("cell-half", "").replace("cell-full", "").replace("faq-cell", "");
            question = $(this).find("blockquote").html();
            answer = $(this).find("div.cell-answer").html();
            FAQfeature.currentNode = $(document.activeElement);
            details = $(this).parents('.view-faq-view').find('div.details');
            var focusableAnswer = $(this).parents(".view-faq-view").children('.details .answer');
            console.log('showCellDetails fired');
            $(".view-faq-view .details h2").html(question);
            $(".view-faq-view .details .answer").html(answer);
            if (FAQfeature.currentColor) {
              $(".view-faq-view .details").removeClass(FAQfeature.currentColor);
            }
            $(".view-faq-view .thanks").html("").css({
              opacity: 0,
              marginTop: "50px"
            });
            $(".view-faq-view .voting").show();
            details.addClass(color).fadeIn(300);
            FAQfeature.currentColor = color;
            var focusedAnswer = $(this).parents(".view-faq-view").find('.details .answer');
            $(focusedAnswer).focus();
            return false;
          },
          hideCellDetails: function() {
            $(".view-faq-view .details").fadeOut(300);
            $(FAQfeature.currentNode).focus();
            return false;
          }
        };

        $(document).ready(function($) {
          // WRAP NEEDED FAQ CELLS IN COLUMNS
          var $cell = $(".view-faq-view article.cell-quarter, .view-faq-view article.cell-half"),
            $full = $(".view-faq-view article.cell-full");
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

          $('.view-faq-view div:not([class])').remove();

          //INIT Functions
          FAQfeature.init();
        });
      })(jQuery, Drupal, this, this.document);;
    }
  };
})(jQuery, Drupal);


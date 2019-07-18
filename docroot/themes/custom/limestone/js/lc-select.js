/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.lc_select = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('div.lc-select').each(function() {
        $(this).children('select').css('display', 'none');

        var $current = $(this);

        $(this).find('option').each(function(i) {
          if (i == 0) {
            $current.prepend($('<div>', {
              class: $current.attr('class').replace(/ViewsJumpMenu/g, 'ViewsJumpMenu__box')
            }));

            var placeholder = $(this).text();
            $current.prepend($('<span>', {
              class: $current.attr('class').replace(/ViewsJumpMenu/g, 'ViewsJumpMenu__placeholder'),
              text: placeholder,
              'data-placeholder': placeholder
            }));

            return;
          }

          $current.children('div').append($('<span>', {
            class: $current.attr('class').replace(/ViewsJumpMenu/g, 'ViewsJumpMenu__box__options'),
            text: $(this).text()
          }));
        });
      });

      // Toggling the `.active` state on the `.sel`.
      $('.ViewsJumpMenu').click(function() {
        $(this).toggleClass('active');
      });

      // Toggling the `.selected` state on the options.
      $('.ViewsJumpMenu__box__options').click(function() {
        var txt = $(this).text();
        var index = $(this).index();

        $(this).siblings('.ViewsJumpMenu__box__options').removeClass('selected');
        $(this).addClass('selected');

        var $currentSel = $(this).closest('.ViewsJumpMenu');
        $currentSel.children('.ViewsJumpMenu__placeholder').text(txt);
        $currentSel.children('select').prop('selectedIndex', index + 1);
      });
    }
  };
})(jQuery, Drupal);



/**
 * @file
 * Provides Javascript for the Layout Builder UX module.
 */

(($, Drupal) => {
  /**
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.LbUX = {
    attach() {
      $('.layout-builder__actions .layout-builder__link')
        .once()
        .on('click', event => {
          $(event.currentTarget)
            .parent()
            .toggleClass('layout-builder__actions--display');
        });

      $(window).on('dialog:aftercreate', (event, dialog, $element) => {
        const id = $element
          .find('[data-layout-builder-target-highlight-id]')
          .attr('data-layout-builder-target-highlight-id');
        if (id) {
          $(`[data-layout-builder-highlight-id="${id}"]`).addClass(
            'layout-builder__block--selected',
          );
        }
      });

      $(window).on('dialog:afterclose', (event, dialog, $element) => {
        $('.layout-builder__block--selected').removeClass(
          'layout-builder__block--selected',
        );
        if (Drupal.offCanvas.isOffCanvas($element)) {
          // Remove the highlight from all elements.
          $('.layout-builder__actions--display').removeClass(
            'layout-builder__actions--display',
          );
        }
      });

      $('.layout-builder__region > .layout-builder-block')
        .once()
        .on('click', event => {
          const currentBlock = $(event.currentTarget);

          /* If block is clicked on while the block is selected, deselect it. */
          if (currentBlock.hasClass('layout-builder__block--selected')) {
            currentBlock
              .find('.layout-builder__actions__block')
              .removeClass('layout-builder__actions--display');

            currentBlock.removeClass('layout-builder__block--selected');

            /* Otherwise, deselect all and select only the current block. */
          } else {
            $('.layout-builder__region > .layout-builder-block').removeClass(
              'layout-builder__block--selected',
            );
            $('.layout-builder__actions--display').removeClass(
              'layout-builder__actions--display',
            );

            currentBlock
              .find('.layout-builder__actions__block')
              .addClass('layout-builder__actions--display');

            currentBlock.addClass('layout-builder__block--selected');
          }
        });
    },
  };
})(jQuery, Drupal);
